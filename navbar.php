<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
<div class="container-fluid">

<a class="navbar-brand order-lg-2 ms-lg-auto" href="#">Expense Tracker</a>

<button class="navbar-toggler order-lg-3" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
<span class="navbar-toggler-icon"></span>
</button>

<div class="collapse navbar-collapse order-lg-1" id="navbarNav">
<ul class="navbar-nav">

<li class="nav-item">
<a class="nav-link <?php if(basename($_SERVER['PHP_SELF'])=='index.php') echo 'active'; ?>" href="index.php">
إضافة مصروف
</a>
</li>

<li class="nav-item">
    <a class="nav-link" href="monthly_expenses.php">مصروفات الشهور</a>
</li>

<li class="nav-item">
<a class="nav-link <?php if(basename($_SERVER['PHP_SELF'])=='dashboard.php') echo 'active'; ?>" href="dashboard.php">
Dashboard
</a>
</li>

<li class="nav-item">
<a class="nav-link" href="logout.php">تسجيل خروج</a>
</li>

</ul>
</div>
</div>
</nav>