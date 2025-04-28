<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include '../koneksi.php';
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $_POST['full_name'];
    $role = $_POST['role'];

    $query = "INSERT INTO users (username, password, full_name, role) VALUES ('$username', '$password', '$full_name', '$role')";
    if ($conn->query($query) === TRUE) {
        header("Location: ../dashboard_admin.php");
        exit;
    } else {
        echo "Error: " . $query . "<br>" . $conn->error;
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah User - Admin Eskul</title>
    <link rel="stylesheet" href="../css/style.css">
    <script type="module" src="../js/main.js"></script>

</head>
<?php include 'partials/navbar_admin.php'; ?>
<?php include 'partials/sidebar_admin.php'; ?>

<body>

<div class="container">
    <h2>Tambah User Baru</h2>
    <form method="POST" action="">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Full Name</label>
        <input type="text" name="full_name" required>

        <label>Role</label>
        <select name="role" required>
            <option value="admin">Admin</option>
            <option value="teacher">Teacher</option>
            <option value="student">Student</option>
        </select>

        <button type="submit" class="button">Tambah User</button>
    </form>
</div>

</body>
</html>
