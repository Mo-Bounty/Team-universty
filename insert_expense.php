<?php
session_start();
include "db.php";

// تأكيد تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// رفع الإيصال
$receipt_name = null;
if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] == 0) {
    $receipt_name = time() . "_" . $_FILES['receipt']['name']; // اسم فريد
    move_uploaded_file($_FILES['receipt']['tmp_name'], "uploads/" . $receipt_name);
}

// بيانات الفورم
$user_id = $_SESSION['user_id'];
$title = $_POST['title'];
$amount = $_POST['amount'];
$date = $_POST['date'];
$category = $_POST['category'];

// إدخال البيانات في الداتابيز مع الإيصال
$sql = "INSERT INTO expenses (user_id, title, amount, date, category, receipt) 
        VALUES ('$user_id', '$title', '$amount', '$date', '$category', '$receipt_name')";

$result = mysqli_query($conn, $sql);

if ($result) {
    header("Location: index.php");
    exit();
} else {
    echo "حدث خطأ: " . mysqli_error($conn);
}
?>