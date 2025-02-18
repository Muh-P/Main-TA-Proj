<?php
session_start();

// Database connection
include 'koneksi.php'; 


if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil informasi murid
$query_murid = "SELECT student_id, name, class FROM students WHERE user_id = ?";
$stmt = $conn->prepare($query_murid);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_murid = $stmt->get_result();
$murid = $result_murid->fetch_assoc();

if (!$murid) {
    die("Error: Data murid tidak ditemukan");
}

$student_id = $murid['student_id'];
$student_name = $murid['name'];
$class = $murid['class'];

// Ambil eskul yang diikuti murid
$query_eskul = "SELECT e.name FROM eskul_students es
                JOIN eskul e ON es.eskul_id = e.eskul_id
                WHERE es.student_id = ?";
$stmt = $conn->prepare($query_eskul);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$eskul_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa</title>
    <link rel="stylesheet" href="asset1/style.css">
</head>
<body>
    <div class="navbar">
        <h2>Dashboard Siswa</h2>
        <div class="menu">
            <span>Selamat datang, <?php echo htmlspecialchars($student_name); ?>!</span>
            <a href="#" onclick="confirmLogout(event)">Logout</a>
        </div>
    </div>

    <div class="container">
        <h3>Informasi Siswa</h3>
        <p><strong>Nama:</strong> <?php echo htmlspecialchars($student_name); ?></p>
        <p><strong>Kelas:</strong> <?php echo htmlspecialchars($class); ?></p>

        <h3>Ekstrakurikuler yang Diikuti</h3>
        <ul>
            <?php 
            if ($eskul_result->num_rows > 0) {
                while ($row = $eskul_result->fetch_assoc()) {
                    echo "<li>🏅 " . htmlspecialchars($row['name']) . "</li>";
                }
            } else {
                echo "<p style='color: red;'>⚠ Tidak terdaftar di ekstrakurikuler mana pun.</p>";
            }
            ?>
        </ul>
    </div>

    <div class="logout-overlay" id="logoutOverlay">
        <div class="logout-modal">
            <h3>Apakah Anda yakin ingin logout?</h3>
            <div class="modal-buttons">
                <a href="logout.php" class="button">Lanjutkan</a>
                <button class="button" onclick="closeLogoutModal()">Batal</button>
            </div>
        </div>
    </div>

    <script src="asset1/main.js"></script>
    <script>
        function confirmLogout(event) {
            event.preventDefault();
            document.getElementById("logoutOverlay").classList.add("show");
        }
        
        function closeLogoutModal() {
            document.getElementById("logoutOverlay").classList.remove("show");
        }
    </script>
</body>
</html>
