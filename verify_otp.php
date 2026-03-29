<?php
session_start();
include "db.php";

$message = "";

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$email = $_SESSION['reset_email'];

if (isset($_POST['verify'])) {
    $otp_input = $_POST['otp'];
    $new_password = $_POST['new_password'];

    $userQuery = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND otp='$otp_input'");
    if (mysqli_num_rows($userQuery) > 0) {
        // تحديث كلمة المرور وحذف OTP
        mysqli_query($conn, "UPDATE users SET password='".password_hash($new_password, PASSWORD_DEFAULT)."', otp=NULL WHERE email='$email'");
        unset($_SESSION['reset_email']);
        $message = "تم تحديث كلمة المرور بنجاح! <a href='login.php'>تسجيل دخول</a>";
    } else {
        $message = "رمز التحقق غير صحيح!";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>تغيير كلمة المرور</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cairo&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Cairo', sans-serif;
    background: #f0f2f5;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 100vh;
    margin: 0;
    padding: 20px;
}
.container {
    background: #fff;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    width: 400px;
}
h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #2c3e50;
}
form input, form button {
    width: 100%;
    padding: 12px;
    margin: 8px 0;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 16px;
}
form button {
    background: #27ae60;
    color: #fff;
    border: none;
    cursor: pointer;
    transition: 0.3s;
}
form button:hover {
    background: #219150;
}
.alert {
    text-align: center;
}
</style>
</head>
<body>
<div class="container">
    <h2>تحقق من OTP</h2>
    <?php if($message) echo "<div class='alert alert-info'>$message</div>"; ?>
    <form method="POST">
        <div class="mb-3">
            <label>رمز التحقق</label>
            <input type="text" name="otp" class="form-control" placeholder="أدخل رمز التحقق" required>
        </div>
        <div class="mb-3">
            <label>كلمة المرور الجديدة</label>
            <input type="password" name="new_password" class="form-control" placeholder="كلمة المرور الجديدة" required>
        </div>
        <button type="submit" name="verify" class="btn btn-success">تحديث كلمة المرور</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>