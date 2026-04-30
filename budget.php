<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if(isset($_POST['save_budget'])){
    $budget = $_POST['budget'];

    mysqli_query($conn,"
        UPDATE users 
        SET budget='$budget' 
        WHERE id='$user_id'
    ");

    $message = "تم حفظ الميزانية بنجاح";
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>الميزانية</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">

<div class="container" style="max-width:500px">

<h3 class="mb-3">💰 تحديد الميزانية الشهرية</h3>

<?php if(isset($message)) echo "<div class='alert alert-success'>$message</div>"; 

$current = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT budget FROM users WHERE id=$user_id
"));
?>

<form method="POST">

    <input type="number" name="budget" class="form-control" value="<?= $current['budget'] ?? '' ?>" placeholder="اكتب الميزانية">

    <button name="save_budget" class="btn btn-success mt-2 w-100">
        حفظ الميزانية
    </button>

</form>

</div>

</body>
</html>
<?php if(isset($message)) echo "<div class='alert alert-success'>$message</div>"; ?>