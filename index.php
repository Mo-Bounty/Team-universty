<?php
session_start();
include "db.php";

// التأكد إن المستخدم مسجل دخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

// إضافة مصروف
if (isset($_POST['add'])) {

    $title = $_POST['title'];
    $amount = $_POST['amount'];
    $date = !empty($_POST['date']) ? $_POST['date'] : date('Y-m-d');

    mysqli_query($conn, "INSERT INTO expenses (title, amount, date, user_id)
    VALUES ('$title','$amount','$date','$user_id')");
}

// حذف مصروف
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM expenses WHERE id=$id AND user_id=$user_id");
}

// عرض المصروفات
$result = mysqli_query($conn, "SELECT * FROM expenses WHERE user_id=$user_id ORDER BY id DESC");

// الإجمالي
$totalRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM expenses WHERE user_id=$user_id"));
$total = $totalRow['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>إضافة مصروف</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cairo&display=swap" rel="stylesheet">

<style>
body{
font-family:'Cairo',sans-serif;
background:#f4f6f9;
}
</style>

</head>
<body>

<?php include "navbar.php"; ?>

<div class="container mt-4">

<h3 class="mb-3">أهلاً 👋 <?php echo $name; ?></h3>

<div class="card shadow p-3 mb-4">
<h4>💰 إضافة مصروف</h4>

<form method="POST" class="row g-2">

<div class="col-md-4">
<input type="text" name="title" class="form-control" placeholder="اسم المصروف" required>
</div>

<div class="col-md-3">
<input type="number" step="0.01" name="amount" class="form-control" placeholder="المبلغ" required>
</div>

<div class="col-md-3">
<input type="date" name="date" class="form-control">
</div>

<div class="col-md-2 d-grid">
<button name="add" class="btn btn-primary">إضافة</button>
</div>

</form>
</div>

<div class="card shadow">
<div class="card-header bg-success text-white">
إجمالي المصروفات: <?php echo number_format($total,2); ?> جنيه
</div>

<div class="table-responsive">
<table class="table table-striped mb-0">
<tr>
<th>عدد الاصناف</th>
<th>العنوان</th>
<th>المبلغ</th>
<th>التاريخ</th>
<th>حذف</th>
</tr>

<?php
$i=1;
while($row=mysqli_fetch_assoc($result)){
echo "<tr>
<td>$i</td>
<td>{$row['title']}</td>
<td>{$row['amount']}</td>
<td>{$row['date']}</td>
<td>
<a class='btn btn-danger btn-sm'
href='?delete={$row['id']}'>
حذف
</a>
</td>
</tr>";
$i++;
}
?>

</table>
</div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>