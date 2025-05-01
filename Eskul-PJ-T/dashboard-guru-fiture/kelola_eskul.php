<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get teacher ID
$stmt = $conn->prepare("SELECT teacher_id FROM teachers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();
$teacher_id = $teacher['teacher_id'];

// Handle form submission for new eskul
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'create') {
            $name = $_POST['name'];
            $description = $_POST['description'];
            
            // Check if eskul with same name exists
            $check_stmt = $conn->prepare("SELECT e.*, t.name as teacher_name 
                                        FROM eskul e 
                                        JOIN eskul_teachers et ON e.eskul_id = et.eskul_id
                                        JOIN teachers t ON et.teacher_id = t.teacher_id
                                        WHERE e.name = ?");
            $check_stmt->bind_param("s", $name);
            $check_stmt->execute();
            $existing_eskul = $check_stmt->get_result()->fetch_assoc();
            
            if ($existing_eskul) {
                $error = "Eskul dengan nama '$name' sudah dikelola oleh " . $existing_eskul['teacher_name'];
            } else {
                $stmt = $conn->prepare("INSERT INTO eskul (name, description) VALUES (?, ?)");
                $stmt->bind_param("ss", $name, $description);
                
                if ($stmt->execute()) {
                    $eskul_id = $stmt->insert_id;
                    // Associate teacher with eskul
                    $stmt = $conn->prepare("INSERT INTO eskul_teachers (eskul_id, teacher_id) VALUES (?, ?)");
                    $stmt->bind_param("ii", $eskul_id, $teacher_id);
                    $stmt->execute();
                    $success = "Eskul berhasil dibuat!";
                }
            }
        } elseif ($_POST['action'] == 'update') {
            $eskul_id = $_POST['eskul_id'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            
            // Verify teacher owns this eskul
            $stmt = $conn->prepare("SELECT 1 FROM eskul_teachers WHERE eskul_id = ? AND teacher_id = ?");
            $stmt->bind_param("ii", $eskul_id, $teacher_id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $stmt = $conn->prepare("UPDATE eskul SET name = ?, description = ? WHERE eskul_id = ?");
                $stmt->bind_param("ssi", $name, $description, $eskul_id);
                if ($stmt->execute()) {
                    $success = "Eskul berhasil diperbarui!";
                }
            } else {
                $error = "Anda tidak memiliki akses untuk mengedit eskul ini!";
            }
        }
    }
}

// Get eskul list for this teacher
$query = "SELECT e.* FROM eskul e 
          JOIN eskul_teachers et ON e.eskul_id = et.eskul_id 
          WHERE et.teacher_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

$page_title = "Kelola Eskul";
include 'layouts/header.php';
include 'layouts/sidebar.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Jadwal Eskul</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="wrapper">
    <main class="container">
        <h2>Kelola Eskul</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Form untuk membuat eskul baru -->
        <form method="POST" class="form-group">
            <h3>Buat Eskul Baru</h3>
            <input type="hidden" name="action" value="create">
            <div class="form-control">
                <label>Nama Eskul:</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-control">
                <label>Deskripsi:</label>
                <textarea name="description" required></textarea>
            </div>
            <button type="submit" class="button">Buat Eskul</button>
        </form>

        <h3>Daftar Eskul Yang Dikelola</h3>
        <div class="table-responsive">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Nama Eskul</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($eskul = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($eskul['name']); ?></td>
                        <td><?php echo htmlspecialchars($eskul['description']); ?></td>
                        <td>
                            <a href="kelola_jadwal.php?eskul_id=<?php echo $eskul['eskul_id']; ?>" 
                               class="button">Kelola Jadwal</a>
                            <button onclick="showEditForm(<?php echo htmlspecialchars(json_encode($eskul)); ?>)" 
                                    class="button">Edit Eskul</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <a href="../dashboard_guru.php" class="button">Kembali ke Dashboard</a>
    </main>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Edit Eskul</h3>
        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="eskul_id" id="edit_eskul_id">
            <div class="form-control">
                <label>Nama Eskul:</label>
                <input type="text" name="name" id="edit_name" required>
            </div>
            <div class="form-control">
                <label>Deskripsi:</label>
                <textarea name="description" id="edit_description" required></textarea>
            </div>
            <button type="submit" class="button">Simpan Perubahan</button>
            <button type="button" class="button" onclick="hideEditForm()">Batal</button>
        </form>
    </div>
</div>

<script>
    function showEditForm(eskul) {
        document.getElementById('editModal').style.display = 'block';
        document.getElementById('edit_eskul_id').value = eskul.eskul_id;
        document.getElementById('edit_name').value = eskul.name;
        document.getElementById('edit_description').value = eskul.description;
    }

    function hideEditForm() {
        document.getElementById('editModal').style.display = 'none';
    }
</script>
<script src="../js/main.js"></script>
</body>
</html>
