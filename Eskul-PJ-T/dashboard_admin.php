<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

session_regenerate_id(true);

include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    die("Error: Session user_id tidak ditemukan. Silakan login kembali.");
}

$user_id = $_SESSION['user_id'];

// Ambil info admin
$query_admin = "SELECT id, full_name, profile_picture FROM users WHERE id = ? AND role = 'admin'";
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
$admin_photo = !empty($admin['profile_picture']) ? $admin['profile_picture'] : 'fotos/profile/default.png';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Eskul Management</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/stylepage.css">
  <script type="module" src="js/main.js" defer></script>
</head>

<body>

<!-- Navbar -->
<nav class="navbar">
  <div class="navbar-left">
    <h2>Admin Dashboard</h2>
  </div>
  <div class="navbar-right">
    <div class="profile">
      <a href="profil.php">
        <img src="<?php echo htmlspecialchars($admin_photo); ?>" alt="Profile" class="profile-logo">
      </a>
      <span class="welcome-message">
        Hai! <?php echo htmlspecialchars($admin_name); ?>, Selamat Datang
      </span>
    </div>
    <button id="menu-btn" class="menu-btn">☰</button>
  </div>
</nav>

<!-- Wrapper -->
<div class="wrapper">

  <!-- Sidebar -->
  <aside id="sidebar" class="sidebar">
    <button id="close-btn" class="close-btn">&times;</button>
    <ul>
  <li><a href="dashboard_admin.php">Dashboard</a></li>
  <li><a href="crud-admin/view_grades.php">View Nilai</a></li>
  <li><a href="profil.php">Profil</a></li>
  <li><a href="#" onclick="confirmLogout(event)">Logout</a></li>
    </ul>
  </aside>

  <!-- Main Content -->
  <main class="container">
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
  </main>

</div> <!-- End of .wrapper -->

<!-- Logout Modal -->
<div id="logoutOverlay" class="logout-overlay">
  <div class="logout-modal">
    <h3>Apakah Anda yakin ingin logout?</h3>
    <div class="modal-buttons">
      <a href="logout.php" class="button">Confirm</a>
      <button class="button" onclick="closeLogoutModal()">Cancel</button>
    </div>
  </div>
</div>

</body>
</html>
