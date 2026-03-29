<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$title = $_POST['title'];
$amount = $_POST['amount'];
$date = $_POST['date'];
$category = $_POST['category'];

$sql = "INSERT INTO expenses (user_id, title, amount, date, category) 
        VALUES ('$user_id', '$title', '$amount', '$date', '$category')";
$result = mysqli_query($conn, $sql);

if ($result) {
    header("Location: index.php");
    exit();
} else {
    echo "حدث خطأ: " . mysqli_error($conn);
}
?>