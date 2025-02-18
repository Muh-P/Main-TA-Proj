<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Database connection
    include '../koneksi.php';


    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure password hashing
    $full_name = $_POST['full_name'];
    $role = $_POST['role'];

    // Insert new user into database
    $query = "INSERT INTO users (username, password, full_name, role) VALUES ('$username', '$password', '$full_name', '$role')";
    if ($conn->query($query) === TRUE) {
        echo "New user created successfully";
        header("Location: ../dashboard_admin.php");
        exit;
    } else {
        echo "Error: " . $query . "<br>" . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - Eskul Management</title>
    <style>
        body {
            font-family: 'Comic Sans MS', cursive, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .container h2 {
            margin-bottom: 20px;
        }

        .container label {
            display: block;
            margin-bottom: 5px;
        }

        .container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .container button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
        }

        .container button:hover {
            background-color: #45a049;
        }

        .password-wrapper {
            position: relative;
            width: 100%;
        }

        .password-wrapper input[type="password"] {
            padding-right: 30px; /* Space for the checkbox */
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .toggle-password input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Add New User</h2>
    <form method="POST" action="">
        <label for="username">Username</label>
        <input type="text" name="username" required>

        <label for="password">Password</label>
        <div class="password-wrapper">
            <input type="password" name="password" id="password" required>
            <label class="toggle-password">
                <input type="checkbox" id="show-password">
                Show Password
            </label>
        </div>

        <label for="full_name">Full Name</label>
        <input type="text" name="full_name" required>

        <label for="role">Role</label>
        <select name="role" required>
            <option value="admin">Admin</option>
            <option value="teacher">Teacher</option>
            <option value="student">Student</option>
        </select>

        <button type="submit">Add User</button>
    </form>
</div>

<script>
    const showPasswordCheckbox = document.getElementById('show-password');
    const passwordInput = document.getElementById('password');

    showPasswordCheckbox.addEventListener('change', function () {
        if (this.checked) {
            passwordInput.type = 'text'; // Show password
        } else {
            passwordInput.type = 'password'; // Hide password
        }
    });
</script>

</body>
</html>
