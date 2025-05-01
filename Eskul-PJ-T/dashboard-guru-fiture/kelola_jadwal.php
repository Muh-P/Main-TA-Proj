<?php
session_start();
include '../koneksi.php';
include '../helpers/semester_helper.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}

$eskul_id = isset($_GET['eskul_id']) ? intval($_GET['eskul_id']) : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selected_days = isset($_POST['days']) ? $_POST['days'] : [];
    
    if (count($selected_days) > 3) {
        $error = "Maksimal hanya 3 hari yang dapat dipilih!";
    } else {
        // Delete existing schedule
        $stmt = $conn->prepare("DELETE FROM eskul_schedule WHERE eskul_id = ?");
        $stmt->bind_param("i", $eskul_id);
        $stmt->execute();
        
        // Insert new schedule
        $stmt = $conn->prepare("INSERT INTO eskul_schedule (eskul_id, hari, id_semester) VALUES (?, ?, ?)");
        
        $currentSemester = getCurrentSemester();
        if ($currentSemester) {
            foreach ($selected_days as $day) {
                $stmt->bind_param("isi", $eskul_id, $day, $currentSemester['id_semester']);
                $stmt->execute();
            }
            $success = "Jadwal berhasil diperbarui!";
        } else {
            $error = "Tidak dalam periode semester aktif!";
        }
    }
}

// Get current schedule
$stmt = $conn->prepare("SELECT hari FROM eskul_schedule WHERE eskul_id = ?");
$stmt->bind_param("i", $eskul_id);
$stmt->execute();
$result = $stmt->get_result();
$scheduled_days = [];
while ($row = $result->fetch_assoc()) {
    $scheduled_days[] = $row['hari'];
}

$page_title = "Kelola Jadwal";
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
        <h2>Kelola Jadwal Eskul</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Pilih Hari (Maksimal 3):</label><br>
                <input type="checkbox" name="days[]" value="Senin" <?php echo in_array('Senin', $scheduled_days) ? 'checked' : ''; ?>> Senin<br>
                <input type="checkbox" name="days[]" value="Selasa" <?php echo in_array('Selasa', $scheduled_days) ? 'checked' : ''; ?>> Selasa<br>
                <input type="checkbox" name="days[]" value="Rabu" <?php echo in_array('Rabu', $scheduled_days) ? 'checked' : ''; ?>> Rabu<br>
                <input type="checkbox" name="days[]" value="Kamis" <?php echo in_array('Kamis', $scheduled_days) ? 'checked' : ''; ?>> Kamis<br>
                <input type="checkbox" name="days[]" value="Jumat" <?php echo in_array('Jumat', $scheduled_days) ? 'checked' : ''; ?>> Jumat<br>
            </div>
            <button type="submit" class="button">Simpan Jadwal</button>
            <a href="../dashboard_guru.php" class="button">Kembali</a>
        </form>
    </main>
</div>

<script src="../js/main.js"></script>
</body>
</html>
