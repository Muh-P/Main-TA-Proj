<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "⚠️ Maaf, kolom username dan password tidak bisa kosong";
    } else {
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $validPassword = true;
            } elseif ($password === $user['password']) {
                $validPassword = true;
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                $update_query = "UPDATE users SET password = ? WHERE username = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("ss", $hashed_password, $username);
                $update_stmt->execute();
            } else {
                $validPassword = false;
            }

            if ($validPassword) {
                session_regenerate_id(true);
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                switch ($user['role']) {
                    case 'admin':
                        header("Location: dashboard_admin.php");
                        exit;
                    case 'teacher':
                        header("Location: dashboard_guru.php");
                        exit;
                    case 'student':
                        header("Location: dashboard_siswa.php");
                        exit;
                    default:
                        header("Location: login.php");
                        exit;
                }
            } else {
                $error = "⚠️ Username atau password salah";
            }
        } else {
            $error = "⚠️ Username atau password salah";
        }
    }
}
?>
