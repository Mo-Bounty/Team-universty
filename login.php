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
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>تسجيل الدخول</title>
<link rel="stylesheet" href="loginandregister.css">
</head>
<body class="login-page">
<div class="container">
<h2>تسجيل الدخول</h2>

<?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>

<form method="POST">
<input type="email" name="email" placeholder="البريد الإلكتروني" required>
<input type="password" name="password" placeholder="كلمة المرور" required>
<button type="submit" name="login">دخول</button>
</form>

<a href="register.php">إنشاء حساب جديد</a>
</div>
</body>
</html>