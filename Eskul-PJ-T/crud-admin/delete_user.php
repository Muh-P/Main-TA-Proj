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
