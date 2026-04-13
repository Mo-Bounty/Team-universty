<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

$welcome = preg_match('/[\x{0600}-\x{06FF}]/u',$name) ? "أهلا👋" : "👋Welcome";

$categories = [
    'food'=>['label'=>'طعام','color'=>'#FFCDD2'],
    'transport'=>['label'=>'مواصلات','color'=>'#C8E6C9'],
    'shopping'=>['label'=>'تسوق','color'=>'#BBDEFB'],
    'entertainment'=>['label'=>'ترفيه','color'=>'#FFF9C4'],
    'bills'=>['label'=>'فواتير','color'=>'#FFE0B2'],
    'others'=>['label'=>'أخرى','color'=>'#D7CCC8']
];

$selectedYear = $_GET['year'] ?? '';
$selectedMonth = $_GET['month'] ?? '';
$selectedCategory = $_GET['category'] ?? '';

$where = "WHERE user_id=$user_id";
if($selectedYear) $where .= " AND YEAR(date)='$selectedYear'";
if($selectedMonth) $where .= " AND DATE_FORMAT(date,'%Y-%m')='$selectedMonth'";
if($selectedCategory) $where .= " AND category='$selectedCategory'";

$expensesQuery = "SELECT * FROM expenses $where ORDER BY date DESC";
$result = mysqli_query($conn,$expensesQuery);

$expenses_by_year = [];
while($row = mysqli_fetch_assoc($result)){
    $year = date('Y', strtotime($row['date']));
    $expenses_by_year[$year][] = $row;
}

$allYearsRes = mysqli_query($conn,"SELECT DISTINCT YEAR(date) as year FROM expenses WHERE user_id=$user_id ORDER BY year DESC");
$allYears = [];
while($r = mysqli_fetch_assoc($allYearsRes)) $allYears[] = $r['year'];

$allMonthsRes = mysqli_query($conn,"SELECT DISTINCT DATE_FORMAT(date,'%Y-%m') as month FROM expenses WHERE user_id=$user_id ORDER BY month DESC");
$allMonths = [];
while($r = mysqli_fetch_assoc($allMonthsRes)) $allMonths[] = $r['month'];

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
<title>مصروفات</title>

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

img.receipt-img{
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 5px;
    cursor: pointer;
}

#welcome-text{
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.filter-row{
    display:flex;
    justify-content:flex-end;
    gap:10px;
    margin-bottom:20px;
    flex-wrap:wrap;
}
</style>
</head>

<body>

<?php include "navbar.php"; ?>

<div class="container mt-4">

<!-- الترحيب -->
<div class="d-flex justify-content-<?php echo preg_match('/[\x{0600}-\x{06FF}]/u',$name)?'start':'end'; ?>">
    <div id="welcome-text"><?php echo $welcome." ".$name; ?></div>
</div>

<!-- العنوان -->
<h2 class="text-center mb-4">مصروفات الشهور السابقة</h2>

<!-- الفلتر -->
<form method="GET" class="filter-row align-items-center">

    <select name="year" class="form-select">
        <option value="">كل السنوات</option>
        <?php foreach($allYears as $y){ ?>
            <option value="<?php echo $y; ?>" <?php echo ($y==$selectedYear)?'selected':''; ?>>
                <?php echo $y; ?>
            </option>
        <?php } ?>
    </select>

    <select name="month" class="form-select">
        <option value="">كل الشهور</option>
        <?php foreach($allMonths as $m){ ?>
            <option value="<?php echo $m; ?>" <?php echo ($m==$selectedMonth)?'selected':''; ?>>
                <?php echo arabicDate($m.'-01'); ?>
            </option>
        <?php } ?>
    </select>

    <select name="category" class="form-select">
        <option value="">كل الفئات</option>
        <?php foreach($categories as $key=>$cat){ ?>
            <option value="<?php echo $key; ?>" <?php echo ($key==$selectedCategory)?'selected':''; ?>>
                <?php echo $cat['label']; ?>
            </option>
        <?php } ?>
    </select>

    <button class="btn btn-primary px-4">فلتر</button>

    <!-- تصدير -->
    <div class="dropdown">
        <button class="btn btn-success dropdown-toggle px-3" type="button" data-bs-toggle="dropdown">
            تصدير
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#" onclick="exportData('excel')">📊 Excel</a></li>
            <li><a class="dropdown-item" href="#" onclick="exportData('csv')">📝 CSV</a></li>
            <li><a class="dropdown-item" href="#" onclick="exportData('pdf')">📄 PDF</a></li>
        </ul>
    </div>

</form>

<?php 
if(empty($expenses_by_year)){
    $msg = "لا توجد مصروفات";
    if($selectedCategory) $msg .= " لفئة " . ($categories[$selectedCategory]['label'] ?? $selectedCategory);
    if($selectedYear) $msg .= " في سنة " . $selectedYear;
    if($selectedMonth) $msg .= " في شهر " . arabicDate($selectedMonth.'-01');
    echo "<div class='alert alert-warning'>$msg</div>";
}

foreach($expenses_by_year as $year => $expenses){ 
    $total = array_sum(array_column($expenses,'amount'));
?>

<div class="card shadow mb-4">
<div class="card-header bg-info text-white">
<?php echo "سنة ".$year; ?> - إجمالي المصروفات: <?php echo number_format($total,2); ?> جنيه
</div>

<div class="table-responsive">
<table class="table table-striped mb-0">
<tr>
<th>#</th>
<th>العنوان</th>
<th>المبلغ</th>
<th>التاريخ</th>
<th>الفئة</th>
<th>الإيصال</th>
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
<td>
<?php if(!empty($row['receipt'])){ ?>
    <img src='uploads/<?php echo $row['receipt']; ?>' class='receipt-img' onclick="window.open(this.src,'_blank')">
<?php } else { echo "لا يوجد إيصال"; } ?>
</td>
</tr>

<?php } ?>
</table>
</div>
</div>

<?php } ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function exportData(type){
    if(!type) return;

    let url = "export_expenses.php?type=" + type +
    "&year=<?php echo $selectedYear; ?>" +
    "&month=<?php echo $selectedMonth; ?>" +
    "&category=<?php echo $selectedCategory; ?>";

    window.location.href = url;
}
</script>

</body>
</html>