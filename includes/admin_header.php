<?php

include 'config.php';
include_once 'auth.php';

requireLogin('../login.php');

// Maintenance mode check (non-admin users blocked)
if (getSystemSetting('maintenance_mode', '0') === '1') {
    $userRole = $_SESSION['user_data']['role'] ?? 'customer';
    if ($userRole !== 'admin') {
        http_response_code(503);
        echo '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Under Maintenance</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Merriweather:ital,wght@0,700;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: "Inter", system-ui, -apple-system, sans-serif;
      background: linear-gradient(135deg, #f8f9fa 0%, #e8f0e9 100%);
      color: #232c25;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }
    .maintenance-card {
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 8px 32px rgba(46, 111, 64, 0.12);
      padding: 3rem 2.5rem;
      max-width: 520px;
      width: 100%;
      text-align: center;
      border: 1px solid rgba(46, 111, 64, 0.1);
    }
    .maintenance-icon {
      width: 80px;
      height: 80px;
      margin: 0 auto 1.5rem;
      background: linear-gradient(135deg, #2e6f40 0%, #1f4d2c 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 2rem;
      box-shadow: 0 4px 16px rgba(46, 111, 64, 0.3);
    }
    .maintenance-title {
      font-family: "Merriweather", Georgia, serif;
      font-size: 1.75rem;
      font-weight: 700;
      color: #2e6f40;
      margin-bottom: 0.75rem;
    }
    .maintenance-subtitle {
      color: #5c685e;
      font-size: 1rem;
      line-height: 1.6;
      margin-bottom: 1.5rem;
    }
    .maintenance-divider {
      width: 60px;
      height: 3px;
      background: linear-gradient(90deg, #2e6f40, #c89531);
      margin: 0 auto 1.5rem;
      border-radius: 2px;
    }
    .maintenance-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      background: rgba(46, 111, 64, 0.08);
      color: #2e6f40;
      padding: 0.5rem 1rem;
      border-radius: 999px;
      font-size: 0.85rem;
      font-weight: 600;
    }
    .maintenance-badge i {
      font-size: 0.75rem;
    }
  </style>
</head>
<body>
  <div class="maintenance-card">
    <div class="maintenance-icon">
      <i class="fa-solid fa-screwdriver-wrench"></i>
    </div>
    <h1 class="maintenance-title">Under Maintenance</h1>
    <div class="maintenance-divider"></div>
    <p class="maintenance-subtitle">We are currently performing scheduled maintenance to improve your experience. We will be back shortly. Thank you for your patience!</p>
    <span class="maintenance-badge"><i class="fa-solid fa-circle-notch fa-spin"></i> Maintenance in progress</span>
  </div>
</body>
</html>';
        exit;
    }
}

$currentPage = basename($_SERVER['PHP_SELF']);
$pageTitleMap = [
  'dashboard.php' => 'Dashboard',
  'orders.php' => 'Orders',
  'payments.php' => 'Payments',
  'deliveries.php' => 'Delivery',
  'menus.php' => 'Menu',
  'reports.php' => 'Reports',
  'settings.php' => 'Settings',
  'profile.php' => 'Profile',
  'login.php' => 'Login',
  'register.php' => 'Register'
];
$pageTitle = $pageTitleMap[$currentPage] ?? 'GoBinalot';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $pageTitle; ?></title>

  <!-- Bootstrap 5 CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- FontAwesome Icons CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Google Fonts (Merriweather & Inter) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Merriweather:ital,wght@0,700;1,400&display=swap" rel="stylesheet">

  <link rel="icon" type="image/png" href="../img/logo.jpg">   

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css">

  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

  <?php if (isset($_SESSION['toast'])): ?>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
      <div class="toast align-items-center text-bg-<?= $_SESSION['toast']['type'] === 'success' ? 'success' : 'danger'; ?> border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">
            <?= htmlspecialchars($_SESSION['toast']['message']); ?>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    </div>
    <?php unset($_SESSION['toast']); ?>
  <?php endif; ?>

  <header class="topbar">
    <div class="d-flex align-items-center gap-3">
      <button class="sidebar-toggle-btn" id="sidebarToggleBtn" aria-label="Toggle Sidebar">
        <i class="fa-solid fa-bars"></i>
      </button>
      <a class="navbar-brand d-flex align-items-center gap-2" href="<?= ($_SESSION['user_data']['role'] == 'admin' ? 'dashboard.php' : ($_SESSION['user_data']['role'] == 'rider' ? 'my_deliveries.php' : 'my_orders.php')); ?> ">
        <img id="restaurant-logo" src="../img/logo.jpg" alt="Binalot sa Bukid Logo" style="height: 40px; width: auto;">
        <span class="fw-bold fs-5 text-success font-heading d-none d-sm-inline"><?= htmlspecialchars(getSystemSetting('website_name', 'GoBinalot')) ?></span>
      </a>
    </div>

    <div class="ms-auto d-flex align-items-center gap-3">
      <!-- Quick Notifications -->
      <div class="dropdown">
        <button class="btn btn-light rounded-circle position-relative" type="button" data-bs-toggle="dropdown">
          <i class="fa-regular fa-bell text-secondary"></i>
          <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 p-2" style="width: 280px;">
          <li class="dropdown-header fw-bold">Recent Alerts</li>
          <li><a class="dropdown-item small text-wrap py-2" href="#"><i class="fa-solid fa-utensils text-success me-2"></i>New order #GB-1082 received</a></li>
          <li><a class="dropdown-item small text-wrap py-2" href="#"><i class="fa-solid fa-calendar-check text-warning me-2"></i>New reservation for 6 guests</a></li>
        </ul>
      </div>

      <!-- Profile Dropdown -->
      <div class="dropdown">
        <a href="#" class="d-flex align-items-center gap-2 text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
          <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80" alt="Admin Avatar" class="rounded-circle" style="width: 38px; height: 38px; object-fit: cover;">
          <span class="fw-semibold small text-dark d-none d-lg-inline"><?= $_SESSION['user_data']['first_name'] . ' ' . $_SESSION['user_data']['last_name']; ?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
          <li><a class="dropdown-item small" href="profile.php"><i class="fa-regular fa-user me-2"></i>Profile</a></li>
          <li><a class="dropdown-item small" href="settings.php"><i class="fa-solid fa-sliders me-2"></i>Settings</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item small text-danger logout-confirm" href="../logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
        </ul>
      </div>
    </div>
  </header>

<?php include('sidebar.php'); ?>

  <!-- Bootstrap 5 JS Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Custom Script to Auto-Show Toasts -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const toastElList = document.querySelectorAll('.toast:not([data-manual-toast="true"])');
      const toastList = [...toastElList].map(toastEl => new bootstrap.Toast(toastEl));
      toastList.forEach(toast => toast.show());
    });
  </script>