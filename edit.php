<?php
session_start();
include "db.php";

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// جلب بيانات المصروف الحالي
$result = mysqli_query($conn, "SELECT * FROM expenses WHERE id=$id AND user_id=$user_id");
if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}
$expense = mysqli_fetch_assoc($result);

$message = "";

// تحديث المصروف
if (isset($_POST['update'])) {
    $title = $_POST['title'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];

    mysqli_query($conn, "UPDATE expenses SET title='$title', amount='$amount', date='$date' WHERE id=$id AND user_id=$user_id");
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>تعديل المصروف</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cairo&display=swap" rel="stylesheet">
<style>body{font-family:'Cairo',sans-serif;}</style>
</head>
<body class="p-4">
<div class="container" style="max-width: 500px;">
    <h2>تعديل المصروف</h2>
    <?php if($message) echo "<div class='alert alert-success'>$message</div>"; ?>
    <form method="POST" class="mt-3">
        <div class="mb-3">
            <label>اسم المصروف</label>
            <input type="text" name="title" class="form-control" value="<?php echo $expense['title']; ?>" required>
        </div>
        <div class="mb-3">
            <label>المبلغ</label>
            <input type="number" step="0.01" name="amount" class="form-control" value="<?php echo $expense['amount']; ?>" required>
        </div>
        <div class="mb-3">
            <label>التاريخ</label>
            <input type="date" name="date" class="form-control" value="<?php echo $expense['date']; ?>" required>
        </div>
        <button type="submit" name="update" class="btn btn-success">تحديث المصروف</button>
        <a href="index.php" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>