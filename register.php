<?php
session_start();
include "db.php";

if (isset($_POST['register'])) {

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // نتأكد إن الإيميل مش متكرر
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if (mysqli_num_rows($check) > 0) {
        $error = "الإيميل مستخدم بالفعل";
    } else {

        mysqli_query($conn, "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')");

        // الحصول على id المستخدم الجديد
        $user_id = mysqli_insert_id($conn);

        // حفظ البيانات في السيشن
        $_SESSION['user_id'] = $user_id;
        $_SESSION['name'] = $name;

        // تحويله مباشرة للصفحة الرئيسية
        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>إنشاء حساب</title>
<link rel="stylesheet" href="style.css">
<style>
body {
    font-family: 'Cairo', sans-serif;
    background-color: #f4f6f9;
}
.container {
    max-width: 400px;
    margin: 50px auto;
    padding: 30px;
    background: #fff;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    border-radius: 8px;
}
input, button {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border-radius: 5px;
    border: 1px solid #ccc;
}
button {
    background-color: #0d6efd;
    color: #fff;
    border: none;
}
.error {
    background: #f8d7da;
    color: #842029;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 10px;
}
a {
    display: block;
    text-align: center;
    margin-top: 10px;
    color: #0d6efd;
    text-decoration: none;
}
</style>
</head>
<body>
<div class="container">
<h2>إنشاء حساب جديد</h2>

<?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>

<form method="POST">
    <input type="text" name="name" placeholder="الاسم بالكامل" required>
    <input type="email" name="email" placeholder="البريد الإلكتروني" required>
    <input type="password" name="password" placeholder="كلمة المرور" required>
    <button type="submit" name="register">تسجيل</button>
</form>

<a href="login.php">عندي حساب بالفعل</a>
</div>
</body>
</html>