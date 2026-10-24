<?php

include 'config.php';
include 'auth.php';

requireLogin('../login.php');

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

   <link rel="stylesheet" href="../css/style.css">
</head>
<body>

  <header class="topbar">
    <div class="d-flex align-items-center gap-3">
      <button class="sidebar-toggle-btn" id="sidebarToggleBtn" aria-label="Toggle Sidebar">
        <i class="fa-solid fa-bars"></i>
      </button>
      <a class="navbar-brand d-flex align-items-center gap-2" href="<?= ($_SESSION['user_data']['role'] == 'admin' ? 'dashboard.php' : 'orders.php'); ?> ">
        <img id="restaurant-logo" src="../img/logo.jpg" alt="Binalot sa Bukid Logo" style="height: 40px; width: auto;">
        <span class="fw-bold fs-5 text-success font-heading d-none d-sm-inline">GoBinalot</span>
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
          <li><a class="dropdown-item small text-wrap py-2" href="#"><i class="fa-solid fa-utensils text-success me-2"></i>New order #ORD-1082 received</a></li>
          <li><a class="dropdown-item small text-wrap py-2" href="#"><i class="fa-solid fa-calendar-check text-warning me-2"></i>New reservation for 6 guests</a></li>
        </ul>
      </div>

      <!-- Profile Dropdown -->
      <div class="dropdown">
        <a href="#" class="d-flex align-items-center gap-2 text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
          <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80" alt="Admin Avatar" class="rounded-circle" style="width: 38px; height: 38px; object-fit: cover;">
          <span class="fw-semibold small text-dark d-none d-lg-inline"><?php echo $_SESSION['user_data']['name']; ?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
          <li><a class="dropdown-item small" href="profile.php"><i class="fa-regular fa-user me-2"></i>Profile</a></li>
          <li><a class="dropdown-item small" href="settings.php"><i class="fa-solid fa-sliders me-2"></i>Settings</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item small text-danger" onclick="return confirm('Are you sure you want to logout?');" href="../logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
        </ul>
      </div>
    </div>
  </header>

<?php include('sidebar.php'); ?>