<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}

$eskul_id = isset($_GET['eskul_id']) ? intval($_GET['eskul_id']) : 0;
$page_title = "Kelola Siswa";

// Get eskul details
$stmt = $conn->prepare("SELECT name FROM eskul WHERE eskul_id = ?");
$stmt->bind_param("i", $eskul_id);
$stmt->execute();
$eskul = $stmt->get_result()->fetch_assoc();

// Handle add student
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_students'])) {
        $selected_students = $_POST['selected_students'] ?? [];
        
        $stmt = $conn->prepare("INSERT INTO eskul_students (eskul_id, student_id) VALUES (?, ?)");
        foreach ($selected_students as $student_id) {
            $stmt->bind_param("ii", $eskul_id, $student_id);
            $stmt->execute();
        }
        $success = "Siswa berhasil ditambahkan ke eskul!";
    }
    
    if (isset($_POST['remove_student'])) {
        $student_id = $_POST['student_id'];
        $stmt = $conn->prepare("DELETE FROM eskul_students WHERE eskul_id = ? AND student_id = ?");
        $stmt->bind_param("ii", $eskul_id, $student_id);
        if ($stmt->execute()) {
            $success = "Siswa berhasil dikeluarkan dari eskul!";
        }
    }
}

// Get all available students grouped by tingkat
$available_students = [];
$sql = "SELECT s.student_id, s.name, k.nama_kelas, k.tingkat 
        FROM students s
        JOIN kelas k ON s.kelas_id = k.kelas_id
        WHERE k.tingkat IN ('X', 'XI')
        AND s.student_id NOT IN (
            SELECT student_id FROM eskul_students WHERE eskul_id = ?
        )
        ORDER BY k.tingkat, k.nama_kelas, s.name";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $eskul_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $available_students[$row['tingkat']][] = $row;
}

// Get current students in eskul
$current_students = [];
$sql = "SELECT s.student_id, s.name, k.nama_kelas, k.tingkat 
        FROM eskul_students es
        JOIN students s ON es.student_id = s.student_id
        JOIN kelas k ON s.kelas_id = k.kelas_id
        WHERE es.eskul_id = ?
        ORDER BY k.tingkat, k.nama_kelas, s.name";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $eskul_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $current_students[$row['tingkat']][] = $row;
}

include 'layouts/header.php';
include 'layouts/sidebar.php';
?>

<div class="wrapper">
    <main class="container">
        <h2>Kelola Siswa: <?php echo htmlspecialchars($eskul['name']); ?></h2>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Add Students Form -->
        <form method="POST" class="form-group">
            <h3>Tambah Siswa Baru</h3>
            
            <?php foreach (['X', 'XI'] as $tingkat): ?>
                <?php if (!empty($available_students[$tingkat])): ?>
                    <div class="grade-section">
                        <h4 class="grade-title">Kelas <?php echo $tingkat; ?></h4>
                        <div class="student-list">
                            <?php foreach ($available_students[$tingkat] as $student): ?>
                                <div class="student-card">
                                    <input type="checkbox" name="selected_students[]" 
                                           value="<?php echo $student['student_id']; ?>" 
                                           id="student_<?php echo $student['student_id']; ?>">
                                    <label for="student_<?php echo $student['student_id']; ?>">
                                        <?php echo htmlspecialchars($student['name']); ?>
                                        <br>
                                        <small><?php echo htmlspecialchars($student['nama_kelas']); ?></small>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
            
            <button type="submit" name="add_students" class="button">Tambah Siswa Terpilih</button>
        </form>

        <!-- Current Students List -->
        <h3>Daftar Siswa Terdaftar</h3>
        <?php foreach (['X', 'XI'] as $tingkat): ?>
            <?php if (!empty($current_students[$tingkat])): ?>
                <div class="grade-section">
                    <h4 class="grade-title">Kelas <?php echo $tingkat; ?></h4>
                    <div class="table-responsive">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Kelas</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($current_students[$tingkat] as $student): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['nama_kelas']); ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="student_id" 
                                                       value="<?php echo $student['student_id']; ?>">
                                                <button type="submit" name="remove_student" 
                                                        class="button delete-btn" 
                                                        onclick="return confirm('Yakin ingin mengeluarkan siswa ini?')">
                                                    Keluarkan
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <a href="../dashboard_guru.php" class="button">Kembali ke Dashboard</a>
    </main>
</div>

<script src="../js/main.js"></script>
</body>
</html>
