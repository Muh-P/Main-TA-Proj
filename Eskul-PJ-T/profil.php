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

// Default foto profil
$profile_picture = $user['profile_picture'] ?: 'fotos/profile.default.png';

// Cek email dan phone untuk guru/murid
$email = "email Belum diisi";
$phone = "no telp Belum diisi";

if ($role == 'teacher') {
    $query_teacher = "SELECT email, phone FROM teachers WHERE user_id = ?";
    $stmt = $conn->prepare($query_teacher);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $teacher = $stmt->get_result()->fetch_assoc();
    if ($teacher) {
        $email = $teacher['email'] ?: 'Belum diisi';
        $phone = $teacher['phone'] ?: 'Belum diisi';
    }
} elseif ($role == 'student') {
    $query_student = "SELECT email, phone FROM students WHERE user_id = ?";
    $stmt = $conn->prepare($query_student);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    if ($student) {
        $email = $student['email'] ?: 'Belum diisi';
        $phone = $student['phone'] ?: 'Belum diisi';
    }
}

// Handle form update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);

    if ($role == 'teacher' || $role == 'student') {
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
    }

    // Upload foto baru
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowed_types = ['jpg', 'jpeg', 'png'];
        $file_info = pathinfo($_FILES['profile_image']['name']);
        $ext = strtolower($file_info['extension']);

        if (in_array($ext, $allowed_types) && $_FILES['profile_image']['size'] <= 2 * 1024 * 1024) {
            // Hapus foto lama jika bukan default
            if ($profile_picture !== 'fotos/profile.default.png' && file_exists($profile_picture)) {
                unlink($profile_picture);
            }

            $new_filename = 'fotos/profile/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['profile_image']['tmp_name'], $new_filename);
            $profile_picture = $new_filename;
        }
    }

    // Update tabel users
    $update_user = "UPDATE users SET full_name = ?, profile_picture = ? WHERE id = ?";
    $stmt = $conn->prepare($update_user);
    $stmt->bind_param("ssi", $full_name, $profile_picture, $user_id);
    $stmt->execute();

    // Update tabel guru/murid kalau bukan admin
    if ($role == 'teacher') {
        $update_teacher = "UPDATE teachers SET email = ?, phone = ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_teacher);
        $stmt->bind_param("ssi", $email, $phone, $user_id);
        $stmt->execute();
    } elseif ($role == 'student') {
        $update_student = "UPDATE students SET email = ?, phone = ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_student);
        $stmt->bind_param("ssi", $email, $phone, $user_id);
        $stmt->execute();
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
    <link rel="stylesheet" href="css/style-profile.css">
</head>
<body>
<div class="container">
    <div class="profile-container">
        <div class="profile-picture-container">
            <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Foto Profil">
        </div>

        <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>

        <?php if (isset($_GET['success'])): ?>
            <p class="success-message">✅ Profil berhasil diupdate!</p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" placeholder="Nama Lengkap" required>

            <?php if ($role == 'student' || $role == 'teacher'): ?>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Email">

                <label for="phone">No HP:</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" placeholder="No HP">
            <?php endif; ?>

            <label for="profile_image" style="text-align: left;">Ganti Foto Profil:</label>
            <input type="file" id="profile_image" name="profile_image" accept="image/*">

            <button type="submit">Simpan Profil</button>
        </form>

        <a href="<?php echo ($role === 'student') ? 'dashboard_siswa.php' : (($role === 'teacher') ? 'dashboard_guru.php' : 'dashboard_admin.php'); ?>" class="back-link">
            ⬅ Kembali ke Dashboard
        </a>
    </div>
</div>
</body>
</html>
