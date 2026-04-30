<?php
session_start();
include "db.php";

// التأكد من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

$budgetRow = mysqli_fetch_assoc(mysqli_query($conn,"SELECT budget FROM users WHERE id=$user_id"));
$budget = $budgetRow['budget'] ?? 0;

$totalRow = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT SUM(amount) as total FROM expenses WHERE user_id=$user_id
"));
$total = $totalRow['total'] ?? 0;



// --- إضافة مصروف مع إيصال ---
if (isset($_POST['add'])) {
    $title = $_POST['title'];
    $amount = $_POST['amount'];

if($amount <= 0){
    echo "<div class='alert alert-danger'>المبلغ غير صحيح</div>";
    return;
}
    $date = !empty($_POST['date']) ? $_POST['date'] : date('Y-m-d');
    $category = $_POST['category'];

    $receipt_name = null;
    if(isset($_FILES['receipt']) && $_FILES['receipt']['error'] == 0){
        $receipt_name = time() . "_" . $_FILES['receipt']['name'];
        move_uploaded_file($_FILES['receipt']['tmp_name'], "uploads/".$receipt_name);
    }

    mysqli_query($conn,"INSERT INTO expenses (title, amount, date, user_id, category, receipt)
    VALUES ('$title','$amount','$date','$user_id','$category','$receipt_name')");
    header("Location: index.php");
    exit();
}

// --- تعديل مصروف + إيصال ---
if (isset($_POST['update'])) {
    $id = $_POST['edit_id'];
    $title = $_POST['edit_title'];
    $amount = $_POST['edit_amount'];
    $date = $_POST['edit_date'];
    $category = $_POST['edit_category'];

    $receipt_name = $_POST['old_receipt'] ?? null;
    if(isset($_FILES['edit_receipt']) && $_FILES['edit_receipt']['error'] == 0){
        if(!empty($receipt_name) && file_exists("uploads/".$receipt_name)){
            unlink("uploads/".$receipt_name);
        }
        $receipt_name = time() . "_" . $_FILES['edit_receipt']['name'];
        move_uploaded_file($_FILES['edit_receipt']['tmp_name'], "uploads/".$receipt_name);
    }

    mysqli_query($conn,"UPDATE expenses SET
        title='$title',
        amount='$amount',
        date='$date',
        category='$category',
        receipt='$receipt_name'
        WHERE id=$id AND user_id=$user_id");
}

// --- حذف مصروف + إيصال ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $row = mysqli_fetch_assoc(mysqli_query($conn,"SELECT receipt FROM expenses WHERE id=$id AND user_id=$user_id"));
    if($row && !empty($row['receipt']) && file_exists("uploads/".$row['receipt'])){
        unlink("uploads/".$row['receipt']);
    }
    mysqli_query($conn,"DELETE FROM expenses WHERE id=$id AND user_id=$user_id");
}

// --- عرض المصروفات ---
$search = $_GET['search'] ?? '';

$query = "SELECT * FROM expenses WHERE user_id=$user_id";

if($search){
    $query .= " AND title LIKE '%$search%'";
}

$query .= " ORDER BY date DESC";

$result = mysqli_query($conn,$query);

// ترتيب البيانات حسب السنة
$expenses_by_year = [];
while($row = mysqli_fetch_assoc($result)){
    $year = date('Y', strtotime($row['date']));
    $expenses_by_year[$year][] = $row;
}

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

// ألوان لكل شهر
$month_colors = [
    '01'=>'#FFC0CB',
    '02'=>'#ADD8E6',
    '03'=>'#90EE90',
    '04'=>'#FFD700',
    '05'=>'#FFA500',
    '06'=>'#20B2AA',
    '07'=>'#FFB6C1',
    '08'=>'#87CEFA',
    '09'=>'#98FB98',
    '10'=>'#FFA07A',
    '11'=>'#DDA0DD',
    '12'=>'#F0E68C'
];
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
img.receipt-img{
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 5px;
}

    filter: invert(1);

</style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="container mt-4">
<?php
function isArabic($text){
    return preg_match('/[\x{0600}-\x{06FF}]/u',$text);
}
$welcome = isArabic($name) ? "أهلا👋" : "👋Welcome";
?>
<h2 class="text-center mb-4"><?php echo $welcome . " " . $name; ?></h2>

<div class="alert alert-info">
    الميزانية الشهرية: <?php echo $budget; ?> جنيه <br>
    المصروف الحالي: <?php echo $total; ?> جنيه <br>

    <?php if($budget > 0){ ?>
        المتبقي: <?php echo $budget - $total; ?> جنيه
    <?php } ?>
</div>

<?php
if($budget > 0){
    if($total >= $budget){
        echo "<div class='alert alert-danger'>❌ لقد تجاوزت الميزانية!</div>";
    }
    elseif($total >= $budget * 0.8){
        echo "<div class='alert alert-warning'>⚠️ أنت قربت تخلص الميزانية</div>";
    }
    else {
        echo "<div class='alert alert-success'>👍 أنت في الأمان</div>";
    }
}
?>

<!-- إضافة مصروف -->
<div class="card shadow p-3 mb-4">
<h4>💰 إضافة مصروف</h4>

<form method="POST" class="row g-2" enctype="multipart/form-data">

    <!-- الميزانية -->

    <!-- اسم المصروف -->
    <div class="col-md-4">
        <input type="text" name="title" class="form-control" placeholder="اسم المصروف" required>
    </div>

    <!-- المبلغ -->
    <div class="col-md-2">
        <input type="number" step="0.01" name="amount" class="form-control" placeholder="المبلغ" required>
    </div>

    <!-- الإيصال -->
    <div class="col-md-4">
        <input type="file" name="receipt" class="form-control">
    </div>

    <!-- التاريخ -->
    <div class="col-md-3 d-flex gap-1" dir="rtl">
        <input type="date" name="date" class="form-control" required>
    </div>

    <!-- الفئة -->
    <div class="col-md-2">
        <select name="category" class="form-select" required>
            <option value="">-- اختر الفئة --</option>
            <option value="food">طعام</option>
            <option value="transport">مواصلات</option>
            <option value="shopping">تسوق</option>
            <option value="entertainment">ترفيه</option>
            <option value="bills">فواتير</option>
            <option value="others">أخرى</option>
        </select>
    </div>

    <!-- زر الإضافة -->
    <div class="col-12 text-end mt-2">
        <button name="add" class="btn btn-primary">إضافة</button>
    </div>

</form>
</div>

<form method="GET" class="mb-3">
    <input type="text" name="search" class="form-control" placeholder="ابحث عن مصروف">
</form>

<!-- عرض المصروفات حسب السنة + تلوين حسب الشهر -->
<?php foreach($expenses_by_year as $year => $expenses): ?>
<div class="card shadow mb-4">
    <div class="card-header bg-info text-white">
        أجمالى المصروفات لعام <?= $year ?>
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
                <th>تعديل</th>
                <th>حذف</th>
            </tr>
            <?php
            $i=1;
            foreach($expenses as $row){
                $month = date('m', strtotime($row['date']));
                $bg_color = $month_colors[$month] ?? '#ffffff';
                echo "<tr style='background-color:$bg_color'>
                    <td>$i</td>
                    <td>{$row['title']}</td>
                    <td>{$row['amount']}</td>
                    <td>{$row['date']}</td>
                    <td>".categoryArabic($row['category'])."</td>
                    <td>";
                if(!empty($row['receipt'])){
                    echo "<img src='uploads/{$row['receipt']}' class='receipt-img'>";
                } else {
                    echo "لا يوجد إيصال";
                }
                echo "</td>
                    <td>
                        <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editModal{$row['id']}'>تعديل</button>
                    </td>
                    <td>
                        <a class='btn btn-danger btn-sm' href='?delete={$row['id']}'>حذف</a>
                    </td>
                </tr>";
                
                // ===== Modal التعديل =====
                ?>
                <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $row['id'] ?>" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form method="POST" enctype="multipart/form-data">
                        <div class="modal-header">
                          <h5 class="modal-title" id="editModalLabel<?= $row['id'] ?>">تعديل المصروف</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" name="edit_id" value="<?= $row['id'] ?>">
                          <input type="hidden" name="old_receipt" value="<?= $row['receipt'] ?>">

                          <div class="mb-2">
                            <label>العنوان</label>
                            <input type="text" name="edit_title" class="form-control" value="<?= $row['title'] ?>" required>
                          </div>

                          <div class="mb-2">
                            <label>المبلغ</label>
                            <input type="number" step="0.01" name="edit_amount" class="form-control" value="<?= $row['amount'] ?>" required>
                          </div>

                          <div class="mb-2">
                            <label>التاريخ</label>
                            <input type="date" name="edit_date" class="form-control" value="<?= $row['date'] ?>" required>
                          </div>

                          <div class="mb-2">
                            <label>الفئة</label>
                            <select name="edit_category" class="form-select" required>
                              <option value="food" <?= $row['category']=='food'?'selected':'' ?>>طعام</option>
                              <option value="transport" <?= $row['category']=='transport'?'selected':'' ?>>مواصلات</option>
                              <option value="shopping" <?= $row['category']=='shopping'?'selected':'' ?>>تسوق</option>
                              <option value="entertainment" <?= $row['category']=='entertainment'?'selected':'' ?>>ترفيه</option>
                              <option value="bills" <?= $row['category']=='bills'?'selected':'' ?>>فواتير</option>
                              <option value="others" <?= $row['category']=='others'?'selected':'' ?>>أخرى</option>
                            </select>
                          </div>

                          <div class="mb-2">
                            <label>إيصال جديد (اختياري)</label>
                            <input type="file" name="edit_receipt" class="form-control">
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                          <button type="submit" name="update" class="btn btn-primary">حفظ التعديلات</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
                <?php
                $i++;
            }
            ?>
        </table>
    </div>
</div>
<?php endforeach; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>