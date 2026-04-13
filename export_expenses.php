<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    exit("غير مصرح");
}

$user_id = $_SESSION['user_id'];

//  اسم المستخدم
$user_query = mysqli_query($conn, "SELECT name FROM users WHERE id = $user_id");
$user_data = mysqli_fetch_assoc($user_query);
$username = $user_data['name'] ?? 'User';

// الفلاتر
$type = $_GET['type'] ?? 'pdf';
$year = $_GET['year'] ?? '';
$month = $_GET['month'] ?? '';
$category = $_GET['category'] ?? '';

$where = "WHERE user_id=$user_id";
if($year) $where .= " AND YEAR(date)='$year'";
if($month) $where .= " AND DATE_FORMAT(date,'%Y-%m')='$month'";
if($category) $where .= " AND category='$category'";

// تحويل الفئات للعربي
$categories = [
    'food'=>'طعام',
    'transport'=>'مواصلات',
    'shopping'=>'تسوق',
    'entertainment'=>'ترفيه',
    'bills'=>'فواتير',
    'others'=>'أخرى'
];

//  CSV 
if($type == 'csv'){
    $result = mysqli_query($conn,"SELECT * FROM expenses $where ORDER BY date DESC");
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=expenses.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['العنوان', 'المبلغ', 'الفئة', 'التاريخ']);

    while($row = mysqli_fetch_assoc($result)){
        $catName = $categories[$row['category']] ?? $row['category'];
        fputcsv($output, [$row['title'], $row['amount'], $catName, $row['date']]);
    }
    fclose($output);
    exit();
}

//  Excel 
if($type == 'excel'){
    $result = mysqli_query($conn,"SELECT * FROM expenses $where ORDER BY date DESC");
    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=expenses.xls");

    echo "\xEF\xBB\xBF"; // BOM UTF-8
    echo "العنوان\tالمبلغ\tالفئة\tالتاريخ\n";

    while($row = mysqli_fetch_assoc($result)){
        $catName = $categories[$row['category']] ?? $row['category'];
        echo $row['title']."\t".$row['amount']."\t".$catName."\t".$row['date']."\n";
    }
    exit();
}

//  PDF 
if($type == 'pdf'){
    require_once(__DIR__ . '/tcpdf_min/tcpdf.php');

    class MYPDF extends TCPDF {
        public function Footer() {
            $this->SetY(-15);
            $this->SetFont('dejavusans','I',8);
            $this->Cell(0,10,'صفحة '.$this->getAliasNumPage().' / '.$this->getAliasNbPages(),0,0,'C');
        }
    }

    $pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Expenses App');
    $pdf->SetAuthor('Expenses App');
    $pdf->SetTitle('مصروفات');
    $pdf->SetMargins(10, 30, 10);
    $pdf->SetAutoPageBreak(TRUE, 10);
    $pdf->SetFont('dejavusans', '', 12);

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);

    // لوجو الشركة
    if(file_exists('logo.png')){
        $pdf->Image('logo.png', 10, 10, 30);
    }

    $pdf->AddPage();

    // جلب البيانات
    $result = mysqli_query($conn,"SELECT * FROM expenses $where ORDER BY date DESC");

    $html = '<h2 style="text-align:center;">تقرير المصروفات</h2>';
    $html .= '<p>المستخدم: <b>'.$username.'</b></p>';

    $html .= '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse:collapse;">
                <tr style="background-color:#d3d3d3;">
                    <th>العنوان</th>
                    <th>المبلغ</th>
                    <th>الفئة</th>
                    <th>التاريخ</th>
                </tr>';

    $total = 0;
    $row_color = false;

    while($row = mysqli_fetch_assoc($result)){
        $catName = $categories[$row['category']] ?? $row['category'];
        $bg = $row_color ? '#f2f2f2' : '#ffffff';
        $html .= '<tr style="background-color:'.$bg.';">
                    <td>'.$row['title'].'</td>
                    <td>'.$row['amount'].'</td>
                    <td>'.$catName.'</td>
                    <td>'.$row['date'].'</td>
                  </tr>';
        $row_color = !$row_color;
        $total += $row['amount'];
    }

    $html .= '<tr style="background-color:#d3d3d3; font-weight:bold;">
                <td colspan="1">الإجمالي</td>
                <td>'.$total.'</td>
                <td colspan="2"></td>
              </tr>';

    $html .= '</table>';

    ob_end_clean();
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('expenses_report.pdf', 'D');

    exit();
}