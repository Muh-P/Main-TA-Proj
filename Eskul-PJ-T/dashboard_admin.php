<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Eskul Management</title>
    <link rel="stylesheet" href="asset1/style.css">
</head>
<body>

   <!-- Navbar -->
<div class="navbar">
    <h2>Admin Dashboard</h2>
    <button id="menu-btn" class="menu-btn">☰</button>
</div>

<!-- Sidebar -->
<aside id="sidebar" class="sidebar">
    <button id="close-btn" class="close-btn">&times;</button>
    <ul>
        <li><a href="#">Dashboard</a></li>
        <li><a href="#">Manage Users</a></li>
        <li><a href="#" onclick="confirmLogout(event)">Logout</a></li>
    </ul>
</aside>


    <!-- Main Content -->
    <div class="container">
        <h3>Manage Users</h3>
        <a href="crud-admin/add_user.php" class="button">Add New User</a>
        <div class="table-responsive">
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
        </div>
    </div>

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

    <script src="asset1/main.js"></script>
    <script>

        function confirmLogout(event) {
            event.preventDefault();
            document.getElementById("logoutOverlay").classList.add("show");
        }
        
        function closeLogoutModal() {
            document.getElementById("logoutOverlay").classList.remove("show");
        }
        document.addEventListener("DOMContentLoaded", function() {
        const menuBtn = document.getElementById("menu-btn");
        const closeBtn = document.getElementById("close-btn");
        const sidebar = document.getElementById("sidebar");

        menuBtn.addEventListener("click", function() {
            sidebar.classList.toggle("show"); // Toggle sidebar
        });

        closeBtn.addEventListener("click", function() {
            sidebar.classList.remove("show"); // Close sidebar when clicking "X"
        });

        document.addEventListener("click", function(event) {
            if (!sidebar.contains(event.target) && !menuBtn.contains(event.target)) {
                sidebar.classList.remove("show"); // Close sidebar when clicking outside
            }
        });
    });
    </script>
</body>
</html>
