<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}


header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

session_regenerate_id(true);

include 'koneksi.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Eskul Management</title>
    <link rel="stylesheet" href="asset1/style.css">
</head>
<body>
    <div class="navbar">
        <h2>Admin Dashboard</h2>
        <div class="menu">
            <a href="#" onclick="confirmLogout(event)">Logout</a>
        </div>
    </div>
    
    <!-- Sidebar -->
    <aside id="sidebar">
        <div class="sidebar_content sidebar_head">
            <h1>Admin Menu</h1>
        </div>
        <div class="sidebar_content sidebar_body">
            <nav class="side_navlinks">
                <ul>
                    <li><a href="#">Dashboard</a></li>
                    <li><a href="#">Manage Users</a></li>
                    <li><a href="#" onclick="confirmLogout(event)">Logout</a></li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Sidebar-Toggler -->
    <div class="sidebar_toggler">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <div class="container">
        <h3>Manage Users</h3>
        <a href="crud-admin/add_user.php" class="button">Add New User</a>
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
                $query = "SELECT * FROM users";
                $result = $conn->query($query);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                        echo "<td>" . ucfirst(htmlspecialchars($row['role'])) . "</td>";
                        echo "<td>
                                <a href='crud-admin/edit_user.php?id=" . $row['id'] . "' class='button'>Edit</a>
                                <a href='crud-admin/delete_user.php?id=" . $row['id'] . "' class='button' style='background-color: #f44336;'>Delete</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No users found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    

    <div class="logout-overlay" id="logoutOverlay">
        <div class="logout-modal">
            <h3>Apakah Anda yakin ingin logout?</h3>
            <div class="modal-buttons">
                <a href="logout.php" class="button">Lanjutkan</a>
                <button class="button" onclick="closeLogoutModal()">Batal</button>
            </div>
        </div>
    </div>
    
    <script  src="asset1/main.js"></script>
    <script>
        function confirmLogout(event) {
            event.preventDefault();
            document.getElementById("logoutOverlay").classList.add("show");
        }
        
        function closeLogoutModal() {
            document.getElementById("logoutOverlay").classList.remove("show");
        }
    </script>
</body>
</html>
