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
$profile_picture = !empty($user['profile_picture']) && file_exists($user['profile_picture']) ? $user['profile_picture'] : 'fotos/profile/default.png';

// Ambil data email & phone kalau guru atau murid
$email = "Belum diisi";
$phone = "Belum diisi";

if ($role == 'teacher') {
    $query_teacher = "SELECT email, phone FROM teachers WHERE user_id = ?";
    $stmt = $conn->prepare($query_teacher);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $teacher = $stmt->get_result()->fetch_assoc();
    if ($teacher) {
        $email = $teacher['email'] ?? 'Belum diisi';
        $phone = $teacher['phone'] ?? 'Belum diisi';
    }
} elseif ($role == 'student') {
    $query_student = "SELECT email, phone FROM students WHERE user_id = ?";
    $stmt = $conn->prepare($query_student);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    if ($student) {
        $email = $student['email'] ?? 'Belum diisi';
        $phone = $student['phone'] ?? 'Belum diisi';
    }
}

// Handle update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['full_name'])) {
    $full_name = trim($_POST['full_name']);
    $new_email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $new_phone = isset($_POST['phone']) ? trim($_POST['phone']) : null;

    // Handle upload foto baru
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowed_types = ['jpg', 'jpeg', 'png'];
        $file_info = pathinfo($_FILES['profile_image']['name']);
        $ext = strtolower($file_info['extension']);

        if (in_array($ext, $allowed_types) && $_FILES['profile_image']['size'] <= 2 * 1024 * 1024) {
            if ($profile_picture !== 'fotos/profile/default.png' && file_exists($profile_picture)) {
                unlink($profile_picture);
            }
            $new_filename = 'fotos/profile/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['profile_image']['tmp_name'], $new_filename);
            $profile_picture = $new_filename;
        }
    }

    // Update users
    $update_user = "UPDATE users SET full_name = ?, profile_picture = ? WHERE id = ?";
    $stmt = $conn->prepare($update_user);
    $stmt->bind_param("ssi", $full_name, $profile_picture, $user_id);
    $stmt->execute();

    // Update email dan phone
    if ($role == 'teacher') {
        $update_teacher = "UPDATE teachers SET email = ?, phone = ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_teacher);
        $stmt->bind_param("ssi", $new_email, $new_phone, $user_id);
        $stmt->execute();
    } elseif ($role == 'student') {
        $update_student = "UPDATE students SET email = ?, phone = ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_student);
        $stmt->bind_param("ssi", $new_email, $new_phone, $user_id);
        $stmt->execute();
    }

    header("Location: profil.php?success=1");
    exit;
}

// Handle ganti password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['current_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $query_pass = "SELECT password FROM users WHERE id = ?";
    $stmt = $conn->prepare($query_pass);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result_pass = $stmt->get_result();
    $user_pass = $result_pass->fetch_assoc();

    if (password_verify($current, $user_pass['password'])) {
        if ($new === $confirm) {
            $new_hashed = password_hash($new, PASSWORD_BCRYPT);
            $update_pass = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($update_pass);
            $stmt->bind_param("si", $new_hashed, $user_id);
            $stmt->execute();
            header("Location: profil.php?success=1");
            exit;
        } else {
            echo "<script>alert('⚠️ Konfirmasi password tidak cocok');</script>";
        }
    } else {
        echo "<script>alert('⚠️ Password lama salah');</script>";
    }
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

<div class="profile-container">
    <div class="profile-picture-container">
        <img id="preview" src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Foto Profil">
    </div>

    <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>

    <?php if (isset($_GET['success'])): ?>
        <p class="success-message">✅ Profil berhasil diupdate!</p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="updateProfileForm">
        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" placeholder="Nama Lengkap" required>

        <?php if ($role == 'student' || $role == 'teacher'): ?>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Email">
            <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" placeholder="No HP">
        <?php endif; ?>

        <label style="text-align: left;">Ganti Foto Profil:</label>
        <input type="file" name="profile_image" id="profile_image" accept="image/*">

        <button type="submit">Simpan Profil</button>
    </form>

    <br><br>

    <h3>Ganti Password</h3>

<form method="POST" id="changePasswordForm">
    <div class="password-field">
        <input type="password" name="current_password" id="current_password" placeholder="Password Lama" required>
        <span class="toggle-password" onclick="togglePassword('current_password')">👁️</span>
    </div>
    <div class="password-field">
        <input type="password" name="new_password" id="new_password" placeholder="Password Baru" required>
        <span class="toggle-password" onclick="togglePassword('new_password')">👁️</span>
    </div>
    <div class="password-field">
        <input type="password" name="confirm_password" id="confirm_password" placeholder="Konfirmasi Password Baru" required>
        <span class="toggle-password" onclick="togglePassword('confirm_password')">👁️</span>
    </div>

    <button type="submit" id="submitPasswordBtn" disabled>Ganti Password</button>
</form>


    <a href="<?php echo ($role === 'student') ? 'dashboard_siswa.php' : (($role === 'teacher') ? 'dashboard_guru.php' : 'dashboard_admin.php'); ?>" class="back-link">
        ⬅ Kembali ke Dashboard
    </a>
</div>

<script>
// Preview foto sebelum upload
const profileImageInput = document.getElementById('profile_image');
const previewImage = document.getElementById('preview');

profileImageInput.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.setAttribute('src', e.target.result);
        }
        reader.readAsDataURL(file);
    }
});

// Loading saat submit
const updateForm = document.getElementById('updateProfileForm');
const changePassForm = document.getElementById('changePasswordForm');

updateForm.addEventListener('submit', function() {
    const button = updateForm.querySelector('button');
    button.innerHTML = '⏳ Menyimpan...';
    button.disabled = true;
});

changePassForm.addEventListener('submit', function() {
    const button = changePassForm.querySelector('button');
    button.innerHTML = '⏳ Mengganti...';
    button.disabled = true;
});

// Toggle lihat password
function togglePassword(fieldId) {
    const input = document.getElementById(fieldId);
    if (input.type === "password") {
        input.type = "text";
    } else {
        input.type = "password";
    }
}

// Enable tombol setelah password match
const newPassword = document.getElementById('new_password');
const confirmPassword = document.getElementById('confirm_password');
const submitPasswordBtn = document.getElementById('submitPasswordBtn');

function checkPasswordMatch() {
    if (newPassword.value && confirmPassword.value && newPassword.value === confirmPassword.value) {
        submitPasswordBtn.disabled = false;
    } else {
        submitPasswordBtn.disabled = true;
    }
}

newPassword.addEventListener('input', checkPasswordMatch);
confirmPassword.addEventListener('input', checkPasswordMatch);
</script>

</body>
</html>
