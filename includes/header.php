<?php

include 'config.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$pageTitleMap = [
  'index.php' => 'Binalot sa Bukid',
  'login.php' => 'Binalot sa Bukid',
  'register.php' => 'Binalot sa Bukid',
  'about.php' => 'Binalot sa Bukid',
  'menu.php' => 'Binalot sa Bukid',
  'contact.php' => 'Binalot sa Bukid'
];
$pageTitle = $pageTitleMap[$currentPage] ?? 'Binalot sa Bukid';
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