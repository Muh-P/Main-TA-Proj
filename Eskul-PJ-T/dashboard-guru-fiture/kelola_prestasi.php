<?php
session_start();
include '../koneksi.php';
include '../helpers/semester_helper.php';

$eskul_id = isset($_GET['eskul_id']) ? $_GET['eskul_id'] : null;
$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;

if ($eskul_id === null || $student_id === null) {
    die("Invalid parameters. Eskul ID or Student ID is missing.");
}

// Get student info
$student_stmt = $conn->prepare("SELECT name FROM students WHERE student_id = ?");
$student_stmt->bind_param("i", $student_id); 
$student_stmt->execute();
$student_result = $student_stmt->get_result();
$student_name = $student_result->fetch_assoc()['name'] ?? "Unknown Student";

// Get active semester
$semester_query = "SELECT id_semester FROM semester WHERE semester = ? AND tahun_ajaran = ?";
$stmt = $conn->prepare($semester_query);
$current_semester = 'Ganjil'; // or 'Genap'
$current_year = '2025/2026'; // adjust as needed
$stmt->bind_param("ss", $current_semester, $current_year);
$stmt->execute();
$semester_result = $stmt->get_result();
$semester = $semester_result->fetch_assoc();

// Handle prestasi submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_prestasi'])) {
    try {
        $judul = $_POST['judul_prestasi'];
        $tingkat = $_POST['tingkat'];
        $tanggal = $_POST['tanggal'];
        
        // Debug
        error_log("Adding prestasi: " . $judul . " - " . $tingkat . " - " . $tanggal);
        
        $insert_query = "INSERT INTO prestasi (student_id, eskul_id, judul_prestasi, tingkat, tanggal, id_semester) 
                        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("iisssi", $student_id, $eskul_id, $judul, $tingkat, $tanggal, $semester['id_semester']);
        
        if ($stmt->execute()) {
            $success = "Prestasi berhasil ditambahkan!";
            header("Location: " . $_SERVER['PHP_SELF'] . "?eskul_id=" . $eskul_id . "&student_id=" . $student_id . "&success=1");
            exit;
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
        error_log("Prestasi Error: " . $error);
    }
}

// Handle delete prestasi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_prestasi'])) {
    $prestasi_id = $_POST['prestasi_id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM prestasi WHERE prestasi_id = ? AND student_id = ? AND eskul_id = ?");
        $stmt->bind_param("iii", $prestasi_id, $student_id, $eskul_id);
        
        if ($stmt->execute()) {
            $success = "Prestasi berhasil dihapus!";
            header("Location: " . $_SERVER['PHP_SELF'] . "?eskul_id=" . $eskul_id . "&student_id=" . $student_id . "&success=1");
            exit;
        } else {
            throw new Exception("Gagal menghapus prestasi");
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Show success message after redirect
if (isset($_GET['success'])) {
    $success = "Prestasi berhasil ditambahkan!";
}

// Get prestasi list
$prestasi_query = "
    SELECT p.*, s.semester, s.tahun_ajaran,
    DATE_FORMAT(p.tanggal, '%d-%m-%Y') as tanggal_formatted 
    FROM prestasi p
    JOIN semester s ON p.id_semester = s.id_semester
    WHERE p.student_id = ? AND p.eskul_id = ?
    GROUP BY p.prestasi_id  /* Prevent duplicates */
    ORDER BY p.tanggal DESC";

$stmt = $conn->prepare($prestasi_query);
$stmt->bind_param("ii", $student_id, $eskul_id);
$stmt->execute();
$prestasi_result = $stmt->get_result();

// Debug prestasi list
error_log("Found " . $prestasi_result->num_rows . " prestasi records");

$page_title = "Kelola Prestasi";

include 'layouts/header.php';
include 'layouts/sidebar.php';
?>

<div class="wrapper">
    <main class="container">
        <h2>Kelola Prestasi: <?php echo htmlspecialchars($student_name); ?></h2>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Add Prestasi Form -->
        <form method="POST" class="form-group">
            <h3>Tambah Prestasi Baru</h3>
            <div class="form-control">
                <label>Judul Prestasi:</label>
                <input type="text" name="judul_prestasi" required>
            </div>
            <div class="form-control">
                <label>Tingkat:</label>
                <select name="tingkat" required>
                    <option value="Sekolah">Sekolah</option>
                    <option value="Kota">Kota</option>
                    <option value="Provinsi">Provinsi</option>
                    <option value="Nasional">Nasional</option>
                    <option value="Internasional">Internasional</option>
                </select>
            </div>
            <div class="form-control">
                <label>Tanggal:</label>
                <input type="date" name="tanggal" required>
            </div>
            <button type="submit" name="add_prestasi" class="button">Tambah Prestasi</button>
        </form>

        <!-- Prestasi List -->
        <h3>Daftar Prestasi</h3>
        <div class="table-responsive">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Tingkat</th>
                        <th>Tanggal</th>
                        <th>Poin</th>
                        <th>Semester</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($prestasi_result->num_rows > 0): ?>
                        <?php while ($prestasi = $prestasi_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($prestasi['judul_prestasi']); ?></td>
                            <td><?php echo htmlspecialchars($prestasi['tingkat']); ?></td>
                            <td><?php echo htmlspecialchars($prestasi['tanggal_formatted']); ?></td>
                            <td><?php echo htmlspecialchars($prestasi['poin']); ?></td>
                            <td><?php echo htmlspecialchars($prestasi['semester'] . ' ' . $prestasi['tahun_ajaran']); ?></td>
                            <td>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus prestasi ini?');">
                                    <input type="hidden" name="prestasi_id" value="<?php echo $prestasi['prestasi_id']; ?>">
                                    <button type="submit" name="delete_prestasi" class="button delete-btn">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">Belum ada prestasi</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <a href="../dashboard_guru.php" class="button">Kembali ke Dashboard</a>
    </main>
</div>

<script src="../js/main.js"></script>
</body>
</html>

<?php
$student_stmt->close();
$stmt->close();
$conn->close();
?>
