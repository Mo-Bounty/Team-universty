<?php
include "db.php";

$id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM expenses WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: index.php");
exit();
?>