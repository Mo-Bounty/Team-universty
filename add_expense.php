<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إضافة مصروف</title>
    <style>
        body { font-family: Arial, sans-serif; direction: rtl; text-align: right; }
        form { max-width: 400px; margin: 50px auto; }
        label { display: block; margin-top: 15px; }
        input, select, button { width: 100%; padding: 8px; margin-top: 5px; }
        button { margin-top: 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #45a049; }
    </style>
</head>
<body>

<h2>إضافة مصروف جديد</h2>

<form action="insert_expense.php" method="POST">
    <label for="title">اسم المصروف:</label>
    <input type="text" name="title" id="title" required>

    <label for="amount">المبلغ:</label>
    <input type="number" name="amount" id="amount" step="0.01" required>

    <label for="date">التاريخ:</label>
    <input type="date" name="date" id="date" required>

    <label for="category">اختر الفئة:</label>
    <select name="category" id="category" required>
        <option value="">-- اختر فئة --</option>
        <option value="food">طعام</option>
        <option value="transport">مواصلات</option>
        <option value="shopping">تسوق</option>
        <option value="entertainment">ترفيه</option>
        <option value="others">أخرى</option>
    </select>

    <button type="submit">إضافة المصروف</button>
</form>

</body>
</html>