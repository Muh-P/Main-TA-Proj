<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$database = "db_eskul_test";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = $_POST['username'];
    $new_full_name = $_POST['full_name'];
    $new_role = $_POST['role'];

    // Update user details in database
    $query = "UPDATE users SET username='$new_username', full_name='$new_full_name', role='$new_role' WHERE id=$id";
    if ($conn->query($query) === TRUE) {
        echo "User updated successfully";
        header("Location: ../dashboard_admin.php");
        exit;
    } else {
        echo "Error: " . $query . "<br>" . $conn->error;
    }
}

$query = "SELECT * FROM users WHERE id=$id";
$result = $conn->query($query);
$user = $result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Eskul Management</title>
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

        .container input, .container select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
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
    </style>
</head>
<body>

<div class="container">
    <h2>Edit User</h2>
    <form method="POST" action="">
        <label for="username">Username</label>
        <input type="text" name="username" value="<?php echo $user['username']; ?>" required>

        <label for="full_name">Full Name</label>
        <input type="text" name="full_name" value="<?php echo $user['full_name']; ?>" required>

        <label for="role">Role</label>
        <select name="role" required>
            <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
            <option value="teacher" <?php if ($user['role'] == 'teacher') echo 'selected'; ?>>Teacher</option>
            <option value="student" <?php if ($user['role'] == 'student') echo 'selected'; ?>>Student</option>
        </select>

        <button type="submit">Update User</button>
    </form>
</div>

</body>
</html>
