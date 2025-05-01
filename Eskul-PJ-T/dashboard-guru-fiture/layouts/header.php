<?php
if (!isset($teacher_name)) {
    $query_teacher = "SELECT t.name, u.profile_picture 
                     FROM teachers t 
                     JOIN users u ON t.user_id = u.id 
                     WHERE t.user_id = ?";
    $stmt = $conn->prepare($query_teacher);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $teacher_data = $stmt->get_result()->fetch_assoc();
    $teacher_name = $teacher_data['name'];
    $teacher_photo = !empty($teacher_data['profile_picture']) ? '../' . $teacher_data['profile_picture'] : '../fotos/profile/default.png';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $page_title ?? 'Dashboard Guru'; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <script type="module" src="../js/main.js"></script>
</head>
<body>
    <?php if (isset($error)) echo "<div class='alert alert-error'>$error</div>"; ?>
    <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
    <nav class="navbar">
        <div class="navbar-left">
            <h2><?php echo $page_title ?? 'Dashboard Guru'; ?></h2>
        </div>
        <div class="navbar-right">
            <div class="profile">
                <img src="<?php echo $teacher_photo; ?>" alt="Profile" class="profile-logo">
                <span class="welcome-message">
                    Hai! <?php echo htmlspecialchars($teacher_name); ?>
                </span>
            </div>
            <button id="menu-btn" class="menu-btn">☰</button>
        </div>
    </nav>
</body>
</html>
