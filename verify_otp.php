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
        mysqli_query($conn, "UPDATE users SET password='".password_hash($new_password, PASSWORD_DEFAULT)."', otp=NULL WHERE email='$email'");
        unset($_SESSION['reset_email']);
        $message = "تم تحديث كلمة المرور بنجاح! <a href='login.php'>تسجيل دخول</a>";
    } else {
        $message = "رمز التحقق غير صحيح!";
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>تغيير كلمة المرور</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container" style="max-width: 500px;">
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
</body>
</html>