<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "db.php";

$user_id = $_SESSION['user_id'];

// جلب بيانات المصروفات
$result = mysqli_query($conn, "SELECT * FROM expenses WHERE user_id = $user_id ORDER BY id DESC");
$totalResult = mysqli_query($conn, "SELECT SUM(amount) as total FROM expenses WHERE user_id = $user_id");
$totalRow = mysqli_fetch_assoc($totalResult);
$total = $totalRow['total'] ?? 0;

// عدد المصروفات
$result_count = mysqli_query($conn, "SELECT COUNT(*) AS count FROM expenses WHERE user_id = $user_id");
$count = mysqli_fetch_assoc($result_count)['count'];

// بيانات الرسم البياني
$chartQuery = mysqli_query($conn, "SELECT title, SUM(amount) as total_amount FROM expenses WHERE user_id = $user_id GROUP BY title");
$labels = [];
$data = [];
while($row = mysqli_fetch_assoc($chartQuery)) {
    $labels[] = $row['title'];
    $data[] = $row['total_amount'];
}
$labelsJSON = json_encode($labels);
$dataJSON = json_encode($data);
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
  direction: rtl;
}
.text-en{
  text-align: left;
  direction: ltr;
}
</style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="container mt-4">
  <h1 class="mb-4 text-en">Dashboard</h1>

  <div class="row mb-4">
    <div class="col-12 col-md-6 mb-3">
      <div class="card text-white bg-success shadow-sm">
        <div class="card-body">
          <h5 class="card-title">إجمالي المصروفات</h5>
          <p class="card-text fs-3"><?php echo number_format($total, 2); ?> جنيه</p>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 mb-3">
      <div class="card text-white bg-info shadow-sm">
        <div class="card-body">
          <h5 class="card-title">عدد المصروفات</h5>
          <p class="card-text fs-3"><?php echo $count; ?></p>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">آخر المصروفات</div>
    <div class="card-body table-responsive">
      <table class="table table-striped mb-0 text-end">
        <thead>
          <tr>
            <th>عدد الاصناف</th>
            <th>اسم المصروف</th>
            <th>المبلغ</th>
            <th>التاريخ</th>
            <th>أفعال</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $i = 1;
          while($row = mysqli_fetch_assoc($result)) {
              echo "<tr>
                      <td>{$i}</td>
                      <td>{$row['title']}</td>
                      <td>{$row['amount']}</td>
                      <td>{$row['date']}</td>
                      <td>
                        <a href='edit.php?id={$row['id']}' class='btn btn-sm btn-warning mb-1'>تعديل</a>
                        <a href='?delete={$row['id']}' class='btn btn-sm btn-danger mb-1'>حذف</a>
                      </td>
                    </tr>";
              $i++;
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <h2 class="mt-5">📊 توزيع المصروفات حسب العنوان</h2>
  <canvas id="expensesChart" width="400" height="200"></canvas>
</div>

<script>
const ctx = document.getElementById('expensesChart').getContext('2d');
const expensesChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo $labelsJSON; ?>,
        datasets: [{
            label: 'المبلغ بالجنيه',
            data: <?php echo $dataJSON; ?>,
            backgroundColor: [
                'rgba(255, 99, 132, 0.6)',
                'rgba(54, 162, 235, 0.6)',
                'rgba(255, 206, 86, 0.6)',
                'rgba(75, 192, 192, 0.6)',
                'rgba(153, 102, 255, 0.6)',
                'rgba(255, 159, 64, 0.6)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: { 
        responsive: true, 
        plugins: { 
            legend: { display: false }, 
            title: { display: true, text: 'توزيع المصروفات حسب العنوان' } 
        }, 
        scales: { y: { beginAtZero: true } } 
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>