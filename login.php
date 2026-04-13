<?php
session_start();
include "db.php";

if (isset($_POST['login'])) {

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];

        header("Location: index.php");
        exit();

    } else {
        $error = "بيانات الدخول غير صحيحة";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>تسجيل الدخول</title>

<link href="https://fonts.googleapis.com/css2?family=Cairo&display=swap" rel="stylesheet">
<link rel="stylesheet" href="loginandregister.css">

</head>
<body class="login-page">

<div class="container">
<h2>تسجيل الدخول</h2>

<?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>

<form method="POST">

    <div class="input-group">
        <span class="icon">📧</span>
        <input type="email" name="email" placeholder="البريد الإلكتروني" required>
    </div>

    <div class="input-group">
        <span class="toggle-password" onclick="togglePassword(this)">🔒</span>
        <input type="password" id="password" name="password" placeholder="كلمة المرور" required>
    </div>

    <button type="submit" name="login">دخول</button>

</form>

<a href="register.php">إنشاء حساب جديد</a>

</div>

<script>
function togglePassword(icon) {
    let password = document.getElementById("password");
    if (password.type === "password") {
        password.type = "text";
        icon.textContent = "🔓";
    } else {
        password.type = "password";
        icon.textContent = "🔒";
    }
}
</script>

</body>
</html>