<?php

session_start();
include 'koneksi.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Ambil data user
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Periksa profile_picture, default-kan jika kosong
$profile_picture = (!empty($user['profile_picture']) && file_exists($user['profile_picture'])) 
    ? $user['profile_picture'] 
    : 'fotos/profile/default.png';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    
    // Kalau role student/teacher ada email dan phone
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : null;

    // Handle upload foto
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowed_types = ['jpg', 'jpeg', 'png'];
        $file_info = pathinfo($_FILES['profile_image']['name']);
        $ext = strtolower($file_info['extension']);

        if (in_array($ext, $allowed_types) && $_FILES['profile_image']['size'] <= 2 * 1024 * 1024) {
            $new_filename = 'fotos/profile/' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $new_filename)) {
                $profile_picture = $new_filename;
            }
        }
    }

    // Update users table
    $update = "UPDATE users SET full_name = ?, profile_picture = ? WHERE id = ?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("ssi", $full_name, $profile_picture, $user_id);
    $stmt->execute();

    // Update ke students/teachers kalau perlu
    if ($role === 'student') {
        $update_student = "UPDATE students SET name = ?, email = ?, phone = ? WHERE user_id = ?";
        $stmt2 = $conn->prepare($update_student);
        $stmt2->bind_param("sssi", $full_name, $email, $phone, $user_id);
        $stmt2->execute();
    } elseif ($role === 'teacher') {
        $update_teacher = "UPDATE teachers SET name = ?, email = ?, phone = ? WHERE user_id = ?";
        $stmt2 = $conn->prepare($update_teacher);
        $stmt2->bind_param("sssi", $full_name, $email, $phone, $user_id);
        $stmt2->execute();
    }

    header("Location: profil.php?success=1");
    exit;
}
?>



<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Profil Saya</h2>
        <?php if (isset($_GET['success'])): ?>
            <p style="color: green;">✅ Profil berhasil diupdate!</p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
    <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Foto Profil" width="150" style="border-radius:50%;">

    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" placeholder="Nama Lengkap" required>

    <?php if ($role !== 'admin'): ?>
        <?php
            // Ambil email dan phone dari students atau teachers
            $query_profile = ($role === 'student') ? "SELECT email, phone FROM students WHERE user_id = ?" : "SELECT email, phone FROM teachers WHERE user_id = ?";
            $stmt_profile = $conn->prepare($query_profile);
            $stmt_profile->bind_param("i", $user_id);
            $stmt_profile->execute();
            $result_profile = $stmt_profile->get_result();
            $profile_data = $result_profile->fetch_assoc();
            $email = $profile_data['email'] ?? '';
            $phone = $profile_data['phone'] ?? '';
        ?>

        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="<?php echo empty($email) ? 'Belum diisi' : ''; ?>" required>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" placeholder="<?php echo empty($phone) ? 'Belum diisi' : ''; ?>" required>
    <?php endif; ?>

    <label>Ganti Foto Profil:</label>
    <input type="file" name="profile_image" accept="image/*">

    <button type="submit">Simpan Profil</button>
        </form>


        <br>
        <a href="<?php echo ($role === 'student' ? 'dashboard_siswa.php' : ($role === 'teacher' ? 'dashboard_guru.php' : 'dashboard_admin.php')); ?>">⬅ Kembali ke Dashboard</a>
    </div>
</body>
</html>
