<?php
session_start();
include "db.php";

if (isset($_POST['register'])) {

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // التحقق من تطابق كلمة المرور
    if ($password !== $confirm_password) {
        $error = "كلمة المرور غير متطابقة";
    } else {

        // التحقق من أن البريد غير مسجل مسبقًا
        $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = "البريد الإلكتروني مسجل مسبقًا";
        } else {
            // تشفير كلمة المرور
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // إدخال المستخدم الجديد في قاعدة البيانات
            mysqli_query($conn, "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed_password')");

            $_SESSION['user_id'] = mysqli_insert_id($conn);
            $_SESSION['name'] = $name;

            header("Location: index.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>إنشاء حساب جديد</title>

<link href="https://fonts.googleapis.com/css2?family=Cairo&display=swap" rel="stylesheet">
<link rel="stylesheet" href="loginandregister.css">

</head>
<body class="login-page">

<div class="container">
<h2>إنشاء حساب جديد</h2>

<?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>

<form method="POST">

    <div class="input-group">
        <span class="icon">🧑/👩</span>
        <input type="text" name="name" placeholder="الاسم الكامل" required>
    </div>

    <div class="input-group">
        <span class="icon">📧</span>
        <input type="email" name="email" placeholder="البريد الإلكتروني" required>
    </div>

    <div class="input-group">
        <span class="toggle-password" onclick="togglePassword(this)">🔒</span>
        <input type="password" id="password" name="password" placeholder="كلمة المرور" required>
    </div>

    <div class="input-group">
        <span class="toggle-password" onclick="toggleConfirmPassword(this)">🔒</span>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="تأكيد كلمة المرور" required>
    </div>

    <button type="submit" name="register">إنشاء الحساب</button>

</form>

<a href="login.php">تسجيل الدخول</a>

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

function toggleConfirmPassword(icon) {
    let password = document.getElementById("confirm_password");
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