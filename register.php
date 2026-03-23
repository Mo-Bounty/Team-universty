<?php
session_start();
include "db.php";

if (isset($_POST['register'])) {

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if (mysqli_num_rows($check) > 0) {
        $error = "الإيميل مستخدم بالفعل";
    } else {

        mysqli_query($conn, "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')");

        $user_id = mysqli_insert_id($conn);

        $_SESSION['user_id'] = $user_id;
        $_SESSION['name'] = $name;

        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>إنشاء حساب</title>
<link rel="stylesheet" href="style.css">
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