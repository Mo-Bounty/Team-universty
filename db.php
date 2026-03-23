<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "expense_tracker";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("فشل الاتصال بقاعدة البيانات");
}
?>