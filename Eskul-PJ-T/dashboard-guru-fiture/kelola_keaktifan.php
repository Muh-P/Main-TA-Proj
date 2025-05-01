<?php
session_start();
include '../koneksi.php';
include '../helpers/semester_helper.php';

$eskul_id = isset($_GET['eskul_id']) ? $_GET['eskul_id'] : null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mass_keaktifan'])) {
    $today = date('Y-m-d');
    $selected_students = $_POST['student_ids'] ?? [];
    
    $conn->begin_transaction();
    try {
        // Get current semester
        $semester = getCurrentSemester();
        if (!$semester) {
            throw new Exception("Tidak dalam periode semester aktif");
        }

        // Get or create penilaian_eskul entries
        foreach ($selected_students as $student_id) {
            // Check if penilaian exists
            $stmt = $conn->prepare("SELECT penilaian_id FROM penilaian_eskul 
                                  WHERE student_id = ? AND eskul_id = ? AND id_semester = ?");
            $stmt->bind_param("iii", $student_id, $eskul_id, $semester['id_semester']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                // Create new penilaian
                $stmt = $conn->prepare("INSERT INTO penilaian_eskul (student_id, eskul_id, id_semester) 
                                      VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $student_id, $eskul_id, $semester['id_semester']);
                $stmt->execute();
                $penilaian_id = $conn->insert_id;
            } else {
                $penilaian_id = $result->fetch_assoc()['penilaian_id'];
            }

            // Calculate attendance percentage
            $stmt = $conn->prepare("SELECT 
                                    COUNT(CASE WHEN status = 'Hadir' THEN 1 END) as hadir,
                                    COUNT(*) as total
                                  FROM attendance a
                                  JOIN eskul_schedule es ON a.schedule_id = es.schedule_id
                                  WHERE a.student_id = ? AND es.eskul_id = ? AND es.id_semester = ?");
            $stmt->bind_param("iii", $student_id, $eskul_id, $semester['id_semester']);
            $stmt->execute();
            $attendance = $stmt->get_result()->fetch_assoc();
            
            $attendance_percentage = ($attendance['total'] > 0) 
                ? ($attendance['hadir'] / $attendance['total']) * 100 
                : 0;

            // Set keaktifan nilai based on attendance
            $nilai = ($attendance_percentage == 100) ? 100 : 75;

            // Insert or update keaktifan
            $stmt = $conn->prepare("INSERT INTO penilaian_keaktifan (penilaian_id, nilai, deskripsi) 
                                  VALUES (?, ?, ?) 
                                  ON DUPLICATE KEY UPDATE nilai = ?, deskripsi = ?");
            $deskripsi = "Keaktifan tanggal " . date('Y-m-d');
            $stmt->bind_param("iisss", $penilaian_id, $nilai, $deskripsi, $nilai, $deskripsi);
            $stmt->execute();
        }
        
        $conn->commit();
        $success = "Keaktifan berhasil disimpan!";
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}

// Get students list
$stmt = $conn->prepare("SELECT s.student_id, s.name, k.nama_kelas 
                       FROM eskul_students es
                       JOIN students s ON es.student_id = s.student_id
                       JOIN kelas k ON s.kelas_id = k.kelas_id
                       WHERE es.eskul_id = ?
                       ORDER BY k.tingkat, k.nama_kelas, s.name");
$stmt->bind_param("i", $eskul_id);
$stmt->execute();
$students = $stmt->get_result();

$page_title = "Kelola Keaktifan";

include 'layouts/header.php';
include 'layouts/sidebar.php';
?>

<div class="wrapper">
    <main class="container">
        <h2>Kelola Keaktifan Siswa</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="mass-attendance">
            <h3>Keaktifan Hari Ini (<?php echo date('d M Y'); ?>)</h3>
            <form method="POST" action="">
                <div class="checkbox-group">
                    <input type="checkbox" id="selectAll" onclick="toggleAllCheckboxes()">
                    <label for="selectAll">Pilih Semua Aktif</label>
                </div>
                
                <div class="student-list">
                    <?php while ($student = $students->fetch_assoc()): ?>
                    <div class="student-card">
                        <input type="checkbox" name="student_ids[]" 
                               value="<?php echo $student['student_id']; ?>" 
                               class="student-checkbox"
                               id="student_<?php echo $student['student_id']; ?>">
                        <label for="student_<?php echo $student['student_id']; ?>">
                            <?php echo htmlspecialchars($student['name']); ?>
                            <br>
                            <small><?php echo htmlspecialchars($student['nama_kelas']); ?></small>
                        </label>
                    </div>
                    <?php endwhile; ?>
                </div>
                
                <button type="submit" name="mass_keaktifan" class="button">
                    Simpan Keaktifan
                </button>
            </form>
        </div>

        <a href="../dashboard_guru.php" class="button">Kembali ke Dashboard</a>
    </main>
</div>

<script src="../js/main.js"></script>
</body>
</html>

<script>
function toggleAllCheckboxes() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.getElementsByClassName('student-checkbox');
    for (let checkbox of checkboxes) {
        checkbox.checked = selectAll.checked;
    }
}
</script>

<?php
$stmt->close();
$conn->close();
?>
