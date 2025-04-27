<?php
session_start();

// Prevent browser cache (so back button won’t load a cached page)
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Cek apakah user sudah login dan memiliki peran sebagai admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// REG ID
session_regenerate_id(true);

// Database connection
include 'koneksi.php';

// Memastikan User Ada Di Session
if (!isset($_SESSION['user_id'])) {
    die("Error: Session user_id tidak ditemukan. Silakan login kembali.");
}

$user_id = $_SESSION['user_id'];

// Ambil informasi admin berdasarkan user_id
$query_admin = "SELECT id, full_name FROM users WHERE id = ? AND role = 'admin'";
$stmt_admin = $conn->prepare($query_admin);
$stmt_admin->bind_param("i", $user_id);
$stmt_admin->execute();
$result_admin = $stmt_admin->get_result();
$admin = $result_admin->fetch_assoc();

if (!$admin) {
    die("Error: Tidak menemukan data admin untuk user_id " . $user_id);
}

$admin_id = $admin['id'];
$admin_name = $admin['full_name'];
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Eskul Management</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/stylepage.css">
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <h2>Admin Dashboard</h2>
        <button id="menu-btn" class="menu-btn">☰</button>
    </div>

    <!-- Wrapper to allow sibling selectors to work -->
    <div class="wrapper">

        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar">
            <button id="close-btn" class="close-btn">&times;</button>
            <ul>
                <li><a href="#">Dashboard</a></li>
                <li><a href="#">Manage Users</a></li>
                <li><a href="profil.php">Profil</a></li>
                <li><a href="#" onclick="confirmLogout(event)">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <div class="container">
            <h3>Manage Users</h3>
            <a href="crud-admin/add_user.php" class="button">Add New User</a>

            <div class="table-responsive">
                <!-- Table with Users -->
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include 'koneksi.php';
                        include 'php/include-pagnation.php';

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                                echo "<td>" . ucfirst(htmlspecialchars($row['role'])) . "</td>";
                                echo "<td>
                                        <a href='crud-admin/edit_user.php?id=" . $row['id'] . "' class='button'>Edit</a>
                                        <a href='crud-admin/delete_user.php?id=" . $row['id'] . "' class='button delete-btn'>Delete</a>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>No users found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="pagination">
                    <a href="?page=<?= ($page - 1) > 0 ? $page - 1 : 1 ?>" class="prev">&laquo; Previous</a>

                    <?php
                    $pageRange = 3;
                    $startPage = max(1, $page - floor($pageRange / 2));
                    $endPage = min($totalPages, $startPage + $pageRange - 1);

                    if ($endPage - $startPage + 1 < $pageRange) {
                        $startPage = max(1, $endPage - $pageRange + 1);
                    }

                    for ($i = $startPage; $i <= $endPage; $i++) {
                        echo "<a href='?page=$i' class='" . ($i == $page ? 'active' : '') . "'>$i</a>";
                    }
                    ?>

                    <a href="?page=<?= ($page + 1) <= $totalPages ? $page + 1 : $totalPages ?>" class="next">Next &raquo;</a>
                </div>
            </div>
        </div>

    </div> <!-- End of .wrapper -->

    
    <!-- Logout Modal -->
    <div class="logout-overlay" id="logoutOverlay">
        <div class="logout-modal">
            <h3>Are you sure you want to logout?</h3>
            <div class="modal-buttons">
                <a href="logout.php" class="button">Confirm</a>
                <button class="button" onclick="closeLogoutModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script type="module" src="js/main.js"></script>


</body>
</html>
