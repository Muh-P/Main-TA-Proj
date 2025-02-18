<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

include '../koneksi.php';


$id = $_GET['id'];

// Delete the user from the database
$query = "DELETE FROM users WHERE id=$id";
if ($conn->query($query) === TRUE) {
    echo "User deleted successfully";
    header("Location: ../dashboard_admin.php");
    exit;
} else {
    echo "Error: " . $query . "<br>" . $conn->error;
}

$conn->close();
?>
