<?php
session_start();
include '../koneksi.php';
include '../helpers/semester_helper.php';

$eskul_id = isset($_GET['eskul_id']) ? $_GET['eskul_id'] : null;
$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;

if ($eskul_id === null || $student_id === null) {
    die("Invalid parameters. Eskul ID or Student ID is missing.");
}

// Check if date is within semester period
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['mass_attendance'])) {
    $date = $_POST['tanggal'];
    $status = $_POST['status'];
    
    if (!isValidSemesterDate($date)) {
        $error = "Tanggal tidak dalam periode semester yang valid!";
    } else {
        // Check if selected date matches schedule
        $dayOfWeek = date('l', strtotime($date));
        $stmt = $conn->prepare("SELECT COUNT(*) FROM eskul_schedule WHERE eskul_id = ? AND hari = ?");
        $stmt->bind_param("is", $eskul_id, $dayOfWeek);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        if ($count == 0) {
            $error = "Tidak ada jadwal eskul pada hari " . $dayOfWeek;
        } else {
            // Insert attendance
            $stmt = $conn->prepare("INSERT INTO attendance (student_id, schedule_id, tanggal, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $student_id, $schedule_id, $date, $status);
            if ($stmt->execute()) {
                $success = "Absensi berhasil dicatat!";
            }
        }
    }
}

// Get schedule days for this eskul
$stmt = $conn->prepare("SELECT hari FROM eskul_schedule WHERE eskul_id = ?");
$stmt->bind_param("i", $eskul_id);
$stmt->execute();
$result = $stmt->get_result();
$schedule_days = [];
while ($row = $result->fetch_assoc()) {
    $schedule_days[] = $row['hari'];
}

// Get all students for this eskul
$stmt = $conn->prepare("SELECT s.student_id, s.name 
                       FROM eskul_students es
                       JOIN students s ON es.student_id = s.student_id
                       WHERE es.eskul_id = ?");
$stmt->bind_param("i", $eskul_id);
$stmt->execute();
$students = $stmt->get_result();

// Handle mass attendance
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mass_attendance'])) {
    $today = date('Y-m-d');
    $dayOfWeek = date('l');
    
    // Check if today is a scheduled day
    $stmt = $conn->prepare("SELECT schedule_id FROM eskul_schedule 
                           WHERE eskul_id = ? AND hari = ?");
    $stmt->bind_param("is", $eskul_id, $dayOfWeek);
    $stmt->execute();
    $schedule_result = $stmt->get_result();
    
    if ($schedule_result->num_rows > 0) {
        $schedule = $schedule_result->fetch_assoc();
        $schedule_id = $schedule['schedule_id'];
        
        // Begin transaction
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO attendance 
                                  (student_id, schedule_id, tanggal, status) 
                                  VALUES (?, ?, ?, 'Hadir')");
            
            foreach ($_POST['student_ids'] as $student_id) {
                $stmt->bind_param("iis", $student_id, $schedule_id, $today);
                $stmt->execute();
            }
            
            $conn->commit();
            $success = "Absensi massal berhasil disimpan!";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Hari ini bukan jadwal eskul!";
    }
}

// Fix the attendance query
$stmt = $conn->prepare("
    SELECT a.*, es.eskul_id 
    FROM attendance a
    JOIN eskul_schedule es ON a.schedule_id = es.schedule_id
    WHERE es.eskul_id = ? AND a.student_id = ?
    ORDER BY a.tanggal DESC
");
$stmt->bind_param("ii", $eskul_id, $student_id);
$stmt->execute();
$attendance_result = $stmt->get_result();

$page_title = "Kelola Absensi";

include 'layouts/header.php';
include 'layouts/sidebar.php';
?>

<div class="wrapper">
    <main class="container">
        <h1>Kelola Absensi: <?php echo htmlspecialchars($student_id); ?></h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Mass Attendance Section -->
        <div class="mass-attendance">
            <h3>Absensi Hari Ini (<?php echo date('d M Y'); ?>)</h3>
            <form method="POST" action="">
                <div class="checkbox-group">
                    <input type="checkbox" id="selectAll" onclick="toggleAllCheckboxes()">
                    <label for="selectAll">Pilih Semua Hadir</label>
                </div>
                
                <?php while ($student = $students->fetch_assoc()): ?>
                <div class="attendance-card">
                    <input type="checkbox" name="student_ids[]" 
                           value="<?php echo $student['student_id']; ?>" 
                           class="student-checkbox">
                    <label><?php echo htmlspecialchars($student['name']); ?></label>
                </div>
                <?php endwhile; ?>
                
                <button type="submit" name="mass_attendance" class="button">
                    Simpan Absensi Massal
                </button>
            </form>
        </div>

        <h2>Input Absensi</h2>
        
        <form method="POST">
            <div class="form-group">
                <label>Tanggal:</label>
                <input type="date" name="tanggal" required>
                <small>Jadwal eskul: <?php echo implode(", ", $schedule_days); ?></small>
            </div>
            
            <div class="form-group">
                <label>Status:</label>
                <select name="status" required>
                    <option value="Hadir">Hadir</option>
                    <option value="Izin">Izin</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Alpa">Alpa</option>
                </select>
            </div>
            
            <button type="submit" class="button">Simpan Absensi</button>
            <a href="../dashboard_guru.php" class="button">Kembali</a>
        </form>
        
        <h2>Riwayat Absensi</h2>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($attendance_result->num_rows > 0) {
                    while ($attendance = $attendance_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($attendance['tanggal']) . "</td>";
                        echo "<td>" . htmlspecialchars($attendance['status']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='2' style='color: red;'>⚠ Tidak ada absensi ditemukan.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>
</div>

<script src="../js/main.js"></script>
</body>
</html>

<?php

$stmt->close();
$conn->close();
?>
