<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "db.php";

$user_id = $_SESSION['user_id'];

// --- بيانات إجمالي المصروفات لكل سنة ---
$totalByYearQuery = mysqli_query($conn,"
    SELECT YEAR(date) as year, SUM(amount) as total
    FROM expenses
    WHERE user_id = $user_id
    GROUP BY year
    ORDER BY year DESC
");

// --- عدد المصروفات لكل سنة ---
$countByYearQuery = mysqli_query($conn,"
    SELECT YEAR(date) as year, COUNT(*) as count
    FROM expenses
    WHERE user_id = $user_id
    GROUP BY year
    ORDER BY year DESC
");

// --- آخر المصروفات لكل سنة ---
$latestByYearQuery = mysqli_query($conn,"
    SELECT *
    FROM expenses
    WHERE user_id = $user_id
    ORDER BY date DESC
");

// --- بيانات الرسم البياني لكل سنة+شهر+فئة ---
$chartQuery = mysqli_query($conn, "
    SELECT YEAR(date) as year, MONTH(date) as month, category, SUM(amount) as total_amount
    FROM expenses
    WHERE user_id = $user_id
    GROUP BY year, month, category
    ORDER BY year, month
");

//  تحويل الفئة إلى عربى
function categoryArabic($cat){
    $map = [
        'food'=>'طعام',
        'transport'=>'مواصلات',
        'shopping'=>'تسوق',
        'entertainment'=>'ترفيه',
        'bills'=>'فواتير',
        'others'=>'أخرى'
    ];
    return $map[$cat] ?? $cat;
}

//   الرسم البياني
$chartData = [];
$categories = [];
while($row = mysqli_fetch_assoc($chartQuery)){
    $year = $row['year'];
    $month = $row['month'];
    $category = $row['category'];
    $amount = $row['total_amount'];

    $chartData[$year][$month][$category] = $amount;
    if(!in_array($category, $categories)){
        $categories[] = $category;
    }
}

// ألوان لكل فئة
$categoryColors = [
    'food'=>'rgba(255, 99, 132, 0.6)',
    'transport'=>'rgba(54, 162, 235, 0.6)',
    'shopping'=>'rgba(255, 206, 86, 0.6)',
    'entertainment'=>'rgba(75, 192, 192, 0.6)',
    'bills'=>'rgba(153, 102, 255, 0.6)',
    'others'=>'rgba(255, 159, 64, 0.6)'
];

//  labels لكل سنة-شهر
$labels = [];
foreach($chartData as $year => $months){
    foreach($months as $month => $cats){
        $labels[] = "$year-$month";
    }
}

//  datasets لكل فئة
$datasets = [];
foreach($categories as $cat){
    $data = [];
    foreach($chartData as $year => $months){
        foreach($months as $month => $cats){
            $data[] = $cats[$cat] ?? 0;
        }
    }
    $datasets[] = [
        'label' => categoryArabic($cat),
        'data' => $data,
        'backgroundColor' => $categoryColors[$cat] ?? 'rgba(200,200,200,0.6)'
    ];
}

$labelsJSON = json_encode($labels);
$datasetsJSON = json_encode($datasets);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cairo&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body{
  font-family:'Cairo',sans-serif;
  background-color:#f8f9fa;
  direction: rtl;
}
.text-ar{
  text-align: right;
}
</style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="container mt-4">
<h1 class="mb-4 text-en" style="text-align: left;">Dashboard</h1>

<!-- جدول إجمالي المصروفات لكل سنة -->
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-success text-white">إجمالي المصروفات لكل سنة</div>
    <div class="card-body table-responsive">
        <table class="table table-striped text-end mb-0">
            <thead>
                <tr>
                    <th>السنة</th>
                    <th>إجمالي المصروفات</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($totalByYearQuery)): ?>
                <tr>
                    <td><?= $row['year'] ?></td>
                    <td><?= number_format($row['total'],2) ?> جنيه</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- جدول عدد المصروفات لكل سنة -->
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-info text-white">عدد المصروفات لكل سنة</div>
    <div class="card-body table-responsive">
        <table class="table table-striped text-end mb-0">
            <thead>
                <tr>
                    <th>السنة</th>
                    <th>عدد المصروفات</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($countByYearQuery)): ?>
                <tr>
                    <td><?= $row['year'] ?></td>
                    <td><?= $row['count'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- جدول آخر المصروفات لكل سنة -->
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-primary text-white">آخر المصروفات لكل سنة</div>
    <div class="card-body table-responsive">
        <table class="table table-striped text-end mb-0">
            <thead>
                <tr>
                    <th>السنة</th>
                    <th>اسم المصروف</th>
                    <th>المبلغ</th>
                    <th>التاريخ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $lastYear = null;
                while($row = mysqli_fetch_assoc($latestByYearQuery)):
                    $year = date('Y', strtotime($row['date']));
                    if($year != $lastYear){
                        $lastYear = $year;
                ?>
                <tr>
                    <td><?= $year ?></td>
                    <td><?= $row['title'] ?></td>
                    <td><?= $row['amount'] ?></td>
                    <td><?= $row['date'] ?></td>
                </tr>
                <?php
                    }
                endwhile;
                ?>
            </tbody>
        </table>
    </div>
</div>

<h2 class="mt-5">📊 المصروفات حسب السنة والشهر والفئة</h2>
<canvas id="expensesChart" width="400" height="200"></canvas>
</div>

<script>
const ctx = document.getElementById('expensesChart').getContext('2d');
const expensesChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo $labelsJSON; ?>,
        datasets: <?php echo $datasetsJSON; ?>
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'المصروفات حسب السنة والشهر والفئة'
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function(context){
                        return context.dataset.label + ': ' + context.raw + ' جنيه';
                    },
                    title: function(context){
                        return 'السنة والشهر: ' + context[0].label;
                    }
                }
            },
            legend: {
                position: 'bottom'
            }
        },
        scales: {
            x: { stacked: true },
            y: { stacked: true, beginAtZero: true }
        }
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>