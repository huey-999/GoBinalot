<?php
  $currentPage = basename($_SERVER['PHP_SELF']);
  $role = $_SESSION['user_data']['role'] ?? 'guest';

  $navConfig = [
    'admin' => [
      [
        'title' => 'Management',
        'items' => [
          ['label' => 'Dashboard', 'icon' => 'fa-solid fa-chart-line', 'url' => 'dashboard.php', 'dataPage' => 'dashboard.php'],
          ['label' => 'Orders', 'icon' => 'fa-solid fa-bag-shopping', 'url' => 'orders.php', 'dataPage' => 'orders.php'],
          ['label' => 'Payments', 'icon' => 'fa-solid fa-cash-register', 'url' => 'payments.php', 'dataPage' => 'payments.php'],
          ['label' => 'Delivery', 'icon' => 'fa-solid fa-motorcycle', 'url' => 'deliveries.php', 'dataPage' => 'deliveries.php'],
          ['label' => 'Menu', 'icon' => 'fa-solid fa-utensils', 'url' => 'menus.php', 'dataPage' => 'menus.php'],
        ]
      ],
      [
        'title' => 'System',
        'items' => [
          ['label' => 'Reports', 'icon' => 'fa-solid fa-chart-simple', 'url' => 'reports.php', 'dataPage' => 'reports.php'],
          ['label' => 'View Website', 'icon' => 'fa-solid fa-globe', 'url' => '../index.php', 'dataPage' => '', 'target' => '_blank'],
        ]
      ]
    ],
    'customer' => [
      [
        'title' => 'Customer',
        'items' => [
          ['label' => 'Orders', 'icon' => 'fa-solid fa-bag-shopping', 'url' => 'my_orders.php', 'dataPage' => 'my_orders.php'],
          ['label' => 'Menu', 'icon' => 'fa-solid fa-utensils', 'url' => 'binalot_menus.php', 'dataPage' => 'binalot_menus.php'],
        ]
      ]
    ]
  ];
?>

<aside class="sidebar" id="sidebar">
  <nav class="nav flex-column">
    <?php if (isset($navConfig[$role])): ?>
      <?php foreach ($navConfig[$role] as $section): ?>
        <div class="px-3 mb-2 text-uppercase text-muted extra-small fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">
          <?= htmlspecialchars($section['title']) ?>
        </div>

        <?php foreach ($section['items'] as $item): ?>
          <?php $isActive = (!empty($item['dataPage']) && $currentPage === $item['dataPage']); ?>
          <a
            class="nav-link <?= $isActive ? 'active' : '' ?>"
            data-page="<?= htmlspecialchars($item['dataPage'] ?? '') ?>"
            href="<?= htmlspecialchars($item['url']) ?>"
            <?= $isActive ? 'aria-current="page"' : '' ?>
            <?= !empty($item['target']) ? 'target="' . htmlspecialchars($item['target']) . '"' : '' ?>
          >
            <i class="<?= htmlspecialchars($item['icon']) ?>"></i> <?= htmlspecialchars($item['label']) ?>
          </a>
        <?php endforeach; ?>
      <?php endforeach; ?>
    <?php endif; ?>
  </nav>
</aside>

<div class="sidebar-backdrop"></div>