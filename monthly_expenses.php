<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

$welcome = preg_match('/[\x{0600}-\x{06FF}]/u',$name) ? " أهلا👋" : "👋Welcome";

$categories = [
    'food'=>['label'=>'طعام','color'=>'#FFCDD2'],
    'transport'=>['label'=>'مواصلات','color'=>'#C8E6C9'],
    'shopping'=>['label'=>'تسوق','color'=>'#BBDEFB'],
    'entertainment'=>['label'=>'ترفيه','color'=>'#FFF9C4'],
    'others'=>['label'=>'أخرى','color'=>'#D7CCC8']
];

$selectedMonth = $_GET['month'] ?? '';

$result = mysqli_query($conn,"
    SELECT * FROM expenses 
    WHERE user_id=$user_id
    ".($selectedMonth ? " AND DATE_FORMAT(date,'%Y-%m')='$selectedMonth'" : "")."
    ORDER BY date DESC
");

$expenses_by_month = [];
while($row = mysqli_fetch_assoc($result)){
    $month_year = date('Y-m', strtotime($row['date']));
    $expenses_by_month[$month_year][] = $row;
}

$allMonthsRes = mysqli_query($conn,"
    SELECT DISTINCT DATE_FORMAT(date,'%Y-%m') as month FROM expenses WHERE user_id=$user_id ORDER BY month DESC
");
$allMonths = [];
while($r = mysqli_fetch_assoc($allMonthsRes)){
    $allMonths[] = $r['month'];
}

function arabicDate($date){
    $months = ['01'=>'يناير','02'=>'فبراير','03'=>'مارس','04'=>'أبريل','05'=>'مايو','06'=>'يونيو',
               '07'=>'يوليو','08'=>'أغسطس','09'=>'سبتمبر','10'=>'أكتوبر','11'=>'نوفمبر','12'=>'ديسمبر'];
    $d = date('d', strtotime($date));
    $m = date('m', strtotime($date));
    $y = date('Y', strtotime($date));
    return $d . ' ' . $months[$m] . ' ' . $y;
}

?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>مصروفات الشهور السابقة</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cairo&display=swap" rel="stylesheet">
<style>
body{
font-family:'Cairo',sans-serif;
background:#f4f6f9;
direction: rtl;
text-align: right;
}
.category-cell {
    padding: 5px 10px;
    border-radius: 5px;
    font-weight: bold;
}
</style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="container mt-4">
<h2 class="text-center mb-4"><?php echo $welcome." ".$name; ?> - مصروفات الشهور السابقة</h2>

<form method="GET" class="mb-4 d-flex justify-content-end">
    <select name="month" class="form-select w-auto me-2">
        <option value="">عرض كل الشهور</option>
        <?php foreach($allMonths as $m){ ?>
            <option value="<?php echo $m; ?>" <?php echo ($m==$selectedMonth)?'selected':''; ?>>
                <?php echo arabicDate($m.'-01'); ?>
            </option>
        <?php } ?>
    </select>
    <button class="btn btn-primary">فلتر</button>
</form>

<?php foreach($expenses_by_month as $month => $expenses){ 
    $total = array_sum(array_column($expenses,'amount'));
    $displayMonth = arabicDate($month.'-01');
?>
<div class="card shadow mb-4">
<div class="card-header bg-info text-white">
<?php echo $displayMonth; ?> - إجمالي المصروفات: <?php echo number_format($total,2); ?> جنيه
</div>

<div class="table-responsive">
<table class="table table-striped mb-0">
<tr>
<th>#</th>
<th>العنوان</th>
<th>المبلغ</th>
<th>التاريخ</th>
<th>الفئة</th>
</tr>
<?php 
$i=1;
foreach($expenses as $row){
    $cat = $categories[$row['category']] ?? ['label'=>$row['category'],'color'=>'#E0E0E0'];
?>
<tr>
<td><?php echo $i++; ?></td>
<td><?php echo $row['title']; ?></td>
<td><?php echo $row['amount']; ?></td>
<td><?php echo arabicDate($row['date']); ?></td>
<td>
    <span class="category-cell" style="background: <?php echo $cat['color']; ?>">
        <?php echo $cat['label']; ?>
    </span>
</td>
</tr>
<?php } ?>
</table>
</div>
</div>
<?php } ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>