<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <!-- شعار الموقع -->
    <a class="navbar-brand" href="#">Expense Tracker</a>

    <!-- زر التوسيع في الموبايل -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- روابط القائمة -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">

        <li class="nav-item">
          <a class="nav-link <?php if(basename($_SERVER['PHP_SELF'])=='index.php') echo 'active'; ?>" href="index.php">
            إضافة مصروف
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?php if(basename($_SERVER['PHP_SELF'])=='monthly_expenses.php') echo 'active'; ?>" href="monthly_expenses.php">
            مصروفات الشهور
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?php if(basename($_SERVER['PHP_SELF'])=='dashboard.php') echo 'active'; ?>" href="dashboard.php">
            Dashboard
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="budget.php">
            الميزانية
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="logout.php">تسجيل خروج</a>
        </li>

      </ul>
    </div>
  </div>
</nav>