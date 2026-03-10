<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "db.php";
?>

<?php
include "db.php";

if (isset($_POST['add'])) {
    $title = $_POST['title'];
    $amount = $_POST['amount'];
    $user_id = $_SESSION['user_id'];

    mysqli_query($conn, "INSERT INTO expenses (title, amount, user_id) VALUES ('$title', '$amount', '$user_id')");
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $user_id = $_SESSION['user_id'];
    mysqli_query($conn, "DELETE FROM expenses WHERE id=$id AND user_id=$user_id");
}

$user_id = $_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT * FROM expenses WHERE user_id = $user_id ORDER BY id DESC");
$totalResult = mysqli_query($conn, "SELECT SUM(amount) as total FROM expenses WHERE user_id = $user_id");
$totalRow = mysqli_fetch_assoc($totalResult);
$total = $totalRow['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>تتبع المصروفات الشهرية</title>
<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/css2?family=Cairo&display=swap" rel="stylesheet">
</head>
<body>
<div class="container">
<div class="top-bar">

<div class="user-info">
👤 <?php echo $_SESSION['name']; ?>
</div>

<a href="logout.php" class="logout-btn">تسجيل خروج</a>

</div>
<h2>💰 تتبع المصروفات الشهرية</h2>

<form method="POST">
<input type="text" name="title" placeholder="اسم المصروف" required>
<input type="number" step="0.01" name="amount" placeholder="المبلغ" required>
<button type="submit" name="add">إضافة</button>
</form>

<div class="total">إجمالي المصروفات: <?php echo $total; ?> جنيه</div>

<table>
<tr>
<th>اسم المصروف</th>
<th>المبلغ</th>
<th>تعديل</th>
<th>حذف</th>
</tr>
<?php while($row = mysqli_fetch_assoc($result)) { ?>
<tr>
<td><?php echo $row['title']; ?></td>
<td><?php echo $row['amount']; ?></td>
<td><a class="edit-btn" href="edit.php?id=<?php echo $row['id']; ?>">تعديل</a></td>
<td><a class="delete-btn" href="?delete=<?php echo $row['id']; ?>">حذف</a></td>
</tr>
<?php } ?>
</table>

</div>
</body>
</html>