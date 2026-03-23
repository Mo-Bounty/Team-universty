<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

if (isset($_POST['add'])) {

    $title = $_POST['title'];
    $amount = $_POST['amount'];
    $date = !empty($_POST['date']) ? $_POST['date'] : date('Y-m-d');

    mysqli_query($conn,"INSERT INTO expenses (title,amount,date,user_id)
    VALUES ('$title','$amount','$date','$user_id')");
}

if (isset($_POST['update'])) {

$id = $_POST['edit_id'];
$title = $_POST['edit_title'];
$amount = $_POST['edit_amount'];
$date = $_POST['edit_date'];

mysqli_query($conn,"UPDATE expenses SET
title='$title',
amount='$amount',
date='$date'
WHERE id=$id AND user_id=$user_id");
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn,"DELETE FROM expenses
    WHERE id=$id AND user_id=$user_id");
}

$result = mysqli_query($conn,"SELECT * FROM expenses
WHERE user_id=$user_id ORDER BY id DESC");

$totalRow = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT SUM(amount) as total FROM expenses WHERE user_id=$user_id"));

$total = $totalRow['total'] ?? 0;


function isArabic($text){
    return preg_match('/[\x{0600}-\x{06FF}]/u',$text);
}

$welcome = isArabic($name) ? " أهلا👋" : "👋Welcome";
?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Expense Tracker</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cairo&display=swap" rel="stylesheet">

<style>
body{
font-family:'Cairo',sans-serif;
background:#f4f6f9;
direction: rtl;
text-align: right;
}
</style>

</head>
<body>

<?php include "navbar.php"; ?>

<div class="container mt-4">

<h2 class="text-center mb-4">
<?php echo $welcome." ".$name; ?>
</h2>

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
<th>#</th>
<th>العنوان</th>
<th>المبلغ</th>
<th>التاريخ</th>
<th>تعديل</th>
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
<button class='btn btn-warning btn-sm'
data-bs-toggle='modal'
data-bs-target='#editModal{$row['id']}'>
تعديل
</button>
</td>

<td>
<a class='btn btn-danger btn-sm'
href='?delete={$row['id']}'>
حذف
</a>
</td>

</tr>";

echo "
<div class='modal fade' id='editModal{$row['id']}' tabindex='-1'>
<div class='modal-dialog'>
<div class='modal-content'>

<div class='modal-header'>
<h5 class='modal-title'>تعديل المصروف</h5>
<button type='button' class='btn-close' data-bs-dismiss='modal'></button>
</div>

<form method='POST'>
<div class='modal-body'>

<input type='hidden' name='edit_id' value='{$row['id']}'>

<input type='text' name='edit_title'
class='form-control mb-2'
value='{$row['title']}' required>

<input type='number' step='0.01'
name='edit_amount'
class='form-control mb-2'
value='{$row['amount']}' required>

<input type='date'
name='edit_date'
class='form-control'
value='{$row['date']}'>

</div>

<div class='modal-footer'>
<button name='update' class='btn btn-success'>
حفظ
</button>
</div>

</form>

</div>
</div>
</div>
";

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