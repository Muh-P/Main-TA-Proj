<?php include 'php/login-code.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Eskul Management</title>
    <link rel="stylesheet" href="css/style2.css">
</head>
<body>
    <div class="login-container">
        <h2>Welcome to Eskul Management</h2>

        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>

            <div class="password-wrapper">
                <input type="password" name="password" id="password" placeholder="Password" required>
                <label class="toggle-password">
                    <input type="checkbox" id="show-password"> Show Password
                </label>
            </div>

            <button type="submit" name="login">Login</button>
        </form>

        <!-- Registration button -->
        <form action="php/register.php" method="POST" style="margin-top: 10px;">
            <button type="submit">Registrasi</button>
        </form>

        <p class="decorative">Manage your extracurricular activities with ease!</p>
    </div>

    <script>
        const showPasswordCheckbox = document.getElementById('show-password');
        const passwordInput = document.getElementById('password');

        showPasswordCheckbox.addEventListener('change', function () {
            passwordInput.type = this.checked ? 'text' : 'password';
        });
    </script>
</body>
</html>
