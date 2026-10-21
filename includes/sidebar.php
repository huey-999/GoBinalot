<?php
  $currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar" id="sidebar">
    <nav class="nav flex-column">
      <div class="px-3 mb-2 text-uppercase text-muted extra-small fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Management</div>
      <a class="nav-link" data-page="dashboard.php" href="dashboard.php">
        <i class="fa-solid fa-chart-line"></i> Dashboard
      </a>
      <a class="nav-link" data-page="orders.php" href="orders.php">
        <i class="fa-solid fa-bag-shopping"></i> Orders
      </a>
      <a class="nav-link" data-page="payments.php" href="payments.php">
        <i class="fa-solid fa-cash-register"></i> Payments
      </a>
      <a class="nav-link" data-page="deliveries.php" href="deliveries.php">
        <i class="fa-solid fa-motorcycle"></i> Delivery
      </a>
      <a class="nav-link" data-page="menu.php" href="menus.php">
        <i class="fa-solid fa-utensils"></i> Menu
      </a>

      <div class="px-3 mt-4 mb-2 text-uppercase text-muted extra-small fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">System</div>
      <a class="nav-link" data-page="reports.php" href="reports.php">
        <i class="fa-solid fa-chart-simple"></i> Reports
      </a>
      <a class="nav-link" href="../index.php" target="_blank">
        <i class="fa-solid fa-globe"></i> View Website
      </a>
    </nav>
  </aside>

  <!-- Sidebar Backdrop (Mobile) -->
  <div class="sidebar-backdrop"></div>