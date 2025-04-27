<?php
session_start();
include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
    $role = isset($_POST['role']) ? trim($_POST['role']) : '';

    if (empty($full_name) || empty($username) || empty($password) || empty($confirm_password) || empty($role)) {
        $error = "⚠️ Semua kolom harus diisi.";
    } elseif ($password !== $confirm_password) {
        $error = "⚠️ Password dan Konfirmasi Password tidak cocok.";
    } elseif (!in_array($role, ['student', 'teacher'])) {
        $error = "⚠️ Role tidak valid.";
    } else {
        // Cek apakah username sudah ada
        $query = "SELECT id FROM users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "⚠️ Username sudah digunakan.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Masukkan user baru
            $insert = "INSERT INTO users (full_name, username, password, role) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert);
            $insert_stmt->bind_param("ssss", $full_name, $username, $hashed_password, $role);

            if ($insert_stmt->execute()) {
                // Registrasi sukses
                $_SESSION['success_message'] = "Registrasi berhasil! Silahkan login.";
                header("Location: ../login.php");
                exit;
            } else {
                $error = "⚠️ Gagal mendaftarkan akun, coba lagi.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Eskul Management</title>
    <link rel="stylesheet" href="css/style2.css">
</head>
<body>
    <div class="login-container">
        <h2>Registrasi Akun Baru</h2>

        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="text" name="full_name" placeholder="Nama Lengkap" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Konfirmasi Password" required>

            <select name="role" required>
                <option value="">-- Pilih Role --</option>
                <option value="student">Siswa</option>
                <option value="teacher">Guru</option>
            </select>

            <button type="submit">Register</button>
        </form>

        <p class="decorative">Sudah punya akun? <a href="../login.php">Login di sini</a>.</p>
    </div>
</body>
</html>
