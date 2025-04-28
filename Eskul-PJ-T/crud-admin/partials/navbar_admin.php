<?php
// Pastikan session aktif
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include '../koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ambil data admin
$user_id = $_SESSION['user_id'];
$query_admin = "SELECT id, full_name, profile_picture FROM users WHERE id = ? AND role = 'admin'";
$stmt = $conn->prepare($query_admin);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

$admin_name = $admin['full_name'] ?? 'Admin';
$admin_photo = !empty($admin['profile_picture']) ? '../' . $admin['profile_picture'] : '../fotos/profile/default.png';

?>

<!-- Navbar HTML -->
<nav class="navbar">
    <div class="navbar-left">
        <h2>Admin Dashboard</h2>
    </div>
    <div class="navbar-right">
        <div class="profile">
            <a href="../profil.php">
                <img src="<?= htmlspecialchars($admin_photo) ?>" alt="Profile" class="profile-logo">
            </a>
            <span class="welcome-message">
                Hai! <?= htmlspecialchars($admin_name) ?>, Selamat Datang
            </span>
        </div>
        <button id="menu-btn" class="menu-btn">☰</button>
    </div>
</nav>
