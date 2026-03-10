<?php
session_start();
include "db.php";

$id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$result = mysqli_query($conn, "SELECT * FROM expenses WHERE id=$id AND user_id=$user_id");
$row = mysqli_fetch_assoc($result);

if (isset($_POST['update'])) {

    $title = $_POST['title'];
    $amount = $_POST['amount'];

    mysqli_query($conn, "UPDATE expenses SET title='$title', amount='$amount' WHERE id=$id AND user_id=$user_id");

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>تعديل المصروف</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
<h2>تعديل المصروف</h2>

<form method="POST">
<input type="text" name="title" value="<?php echo $row['title']; ?>" required>
<input type="number" step="0.01" name="amount" value="<?php echo $row['amount']; ?>" required>

<button type="submit" name="update">تحديث</button>
</form>

</div>

</body>
</html>