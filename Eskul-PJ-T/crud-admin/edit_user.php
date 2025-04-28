<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../koneksi.php';
$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = $_POST['username'];
    $new_full_name = $_POST['full_name'];
    $new_role = $_POST['role'];

    $query = "UPDATE users SET username='$new_username', full_name='$new_full_name', role='$new_role' WHERE id=$id";
    if ($conn->query($query) === TRUE) {
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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit User - Admin Eskul</title>
    <link rel="stylesheet" href="../css/style.css">
    <script type="module" src="../js/main.js"></script>

</head>
<?php include 'partials/navbar_admin.php'; ?>
<?php include 'partials/sidebar_admin.php'; ?>
<body>

<div class="container">
    <h2>Edit User</h2>
    <form method="POST" action="">
        <label>Username</label>
        <input type="text" name="username" value="<?php echo $user['username']; ?>" required>

        <label>Full Name</label>
        <input type="text" name="full_name" value="<?php echo $user['full_name']; ?>" required>

        <label>Role</label>
        <select name="role" required>
            <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
            <option value="teacher" <?php if ($user['role'] == 'teacher') echo 'selected'; ?>>Teacher</option>
            <option value="student" <?php if ($user['role'] == 'student') echo 'selected'; ?>>Student</option>
        </select>

        <button type="submit" class="button">Update User</button>
    </form>
</div>

</body>
</html>
