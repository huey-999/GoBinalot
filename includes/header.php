<?php

include 'config.php';

// Maintenance mode check (non-admin users blocked)
if (getSystemSetting('maintenance_mode', '0') === '1') {
    $userRole = $_SESSION['user_data']['role'] ?? 'guest';
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
$siteName = getSystemSetting('website_name', 'Binalot sa Bukid');
$pageTitleMap = [
  'index.php' => $siteName,
  'login.php' => $siteName,
  'register.php' => $siteName,
  'about.php' => $siteName,
  'menu.php' => $siteName,
  'contact.php' => $siteName
];
$pageTitle = $pageTitleMap[$currentPage] ?? $siteName;
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

  <!-- Google Fonts (Playfair Display / Merriweather & Inter for a warm homey touch) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Merriweather:ital,wght@0,700;1,400&display=swap" rel="stylesheet">

  <link rel="icon" type="image/png" href="img/logo.jpg">   
  
  <link rel="stylesheet" href="css/style.css">
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

  <!-- ==========================================================================
       ========================= STICKY NAVIGATION BAR =========================
       ========================================================================== -->
  <nav class="navbar navbar-expand-lg navbar-light fixed-top" id="mainNavbar">
    <div class="container">
      
      <!-- LOGO: Binalot sa Bukid logo image emblem -->
      <a class="navbar-brand d-flex align-items-center" href="index.php">
        <img id="restaurant-logo" src="img/logo.jpg" alt="Binalot sa Bukid Logo" style="height: 55px; width: auto;">
      </a>

      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
          <li class="nav-item"><a class="nav-link active" href="index.php#home">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#menu">Menu</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#about">About Us</a></li>
          <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
            <a class="btn btn-primary-custom btn-sm text-uppercase px-4 py-2" href="login.php">Order Now</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>