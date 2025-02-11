<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "db_eskul_test";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "⚠️ Maaf, kolom username dan password tidak bisa kosong";
    } else {
        // Query untuk mendapatkan data user
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Cek apakah password masih dalam bentuk teks atau sudah di-hash
            if (password_verify($password, $user['password'])) {
                $validPassword = true;
            } elseif ($password === $user['password']) { // Jika masih dalam bentuk teks
                $validPassword = true;
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Update password di database agar menjadi hashed
                $update_query = "UPDATE users SET password = ? WHERE username = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("ss", $hashed_password, $username);
                $update_stmt->execute();
            } else {
                $validPassword = false;
            }

            if ($validPassword) {
                // Simpan session setelah login berhasil
                session_regenerate_id(true);
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect berdasarkan peran user
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
                        header("Location: index.php");
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





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Eskul Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: url('fotos/School.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #333;
        }
        .login-container {
            width: 350px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        .login-container h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #4CAF50;
        }
        .login-container input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 2px solid #4CAF50;
            border-radius: 10px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .login-container button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease-in-out;
        }
        .login-container button:hover {
            background-color: #45a049;
            transform: scale(1.05);
        }
        .error {
            color: red;
            font-size: 14px;
        }
        .decorative {
            font-size: 14px;
            color: #666;
            margin-top: 10px;
        }
        .password-wrapper {
            position: relative;
            width: 100%;
            display: flex;
            align-items: center;
        }
        .password-wrapper input[type="password"] {
            flex-grow: 1;
            padding-right: 40px;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            cursor: pointer;
            user-select: none;
            font-size: 14px;
            color: #333;
        }
        .toggle-password input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Welcome to Eskul Management</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            
            <label for="password"></label>
            <div class="password-wrapper">
                <input type="password" name="password" id="password" required>
                <label class="toggle-password">
                    <input type="checkbox" id="show-password"> Show Password
                </label>
            </div>
            
            <button type="submit">Login</button>
        </form>
        <p class="decorative">Manage your extracurricular activities with ease!</p>
    </div>
    
    <script>
        const showPasswordCheckbox = document.getElementById('show-password');
        const passwordInput = document.getElementById('password');
    
        showPasswordCheckbox.addEventListener('change', function () {
            passwordInput.type = this.checked ? 'text' : 'password';
        });
    </script>
</body>
</html>
