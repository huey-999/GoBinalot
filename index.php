<?php include 'includes/header.php'; ?>

<?php
$activeCategories = $conn->query(
    "SELECT id, name, status
     FROM categories
     WHERE status = 'active'
     ORDER BY name ASC"
)->fetchAll(PDO::FETCH_ASSOC);

$menuItems = $conn->query(
    "SELECT m.*, c.name AS category_name
     FROM menu_items m
     LEFT JOIN categories c ON c.id = m.category_id
     WHERE m.is_available = 'Y'
       AND c.status = 'active'
     ORDER BY c.name ASC"
)->fetchAll(PDO::FETCH_ASSOC);

function gobinalotMenuSlug($value) {
    $slug = strtolower(trim((string) $value));
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/\s+/', '-', $slug);
    return trim($slug, '-');
}
?>

  <!-- ==========================================================================
       ========================= HERO SECTION ===================================
       ========================================================================== -->
  <section id="home" class="hero-section text-center">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <span class="text-uppercase tracking-wide text-warning fw-bold mb-2 d-block" style="letter-spacing: 2px;">Welcome to Binalot sa Bukid</span>
          <h1 class="hero-title mb-3">Authentic Filipino Home-Style Meals</h1>
          <p class="hero-subtitle mb-4">Savor your traditional Filipino favorites wrapped in fresh banana leaves, served with salted egg, fresh tomatoes, and warm rice!</p>
          <div class="hero-buttons d-flex flex-wrap justify-content-center gap-3">
            <a href="#menu" class="btn btn-primary-custom"><i class="fa-solid fa-leaf me-2"></i>Explore Our Menu</a>
            <a href="login.php" class="btn btn-outline-light rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center justify-content-center">Order Now</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==========================================================================
       ========================= INTERACTIVE MENU SECTION ======================
       ========================================================================== -->
  <section id="menu" class="pt-5">
    <div class="container py-4">
      <div class="text-center section-title">
        <span class="text-uppercase text-muted small fw-bold" style="letter-spacing: 1px;">Home-Style Specialties</span>
        <h2 class="display-5 fw-bold text-success">Our Binalot Menu</h2>
      </div>

      <div class="menu-nav text-center mb-4">
        <div class="nav nav-pills justify-content-center" id="menu-tabs">
          <button class="nav-link active" data-filter="all">All</button>
          <?php foreach ($activeCategories as $category) : ?>
            <?php $categoryFilter = gobinalotMenuSlug($category['name']); ?>
            <button class="nav-link" data-filter="<?= htmlspecialchars($categoryFilter) ?>"><?= htmlspecialchars($category['name']) ?></button>
          <?php endforeach; ?>
        </div>
      </div>

      <?php if (!empty($menuItems)) : ?>
        <div class="row g-4" id="menu-items-container">
          <?php foreach ($menuItems as $menu) : ?>
            <?php $categorySlug = gobinalotMenuSlug($menu['category_name'] ?? 'uncategorized'); ?>
            <div class="col-md-6 col-lg-4 menu-item-card" data-category="<?= htmlspecialchars($categorySlug) ?>">
              <div class="menu-card">
                <div class="menu-img-wrapper">
                  <img src="<?= !empty($menu['image']) ? 'img/menu/' . htmlspecialchars($menu['image']) : 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=600&q=80'; ?>" class="menu-img" alt="<?= htmlspecialchars($menu['name']) ?>">
                </div>
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="card-title fw-bold mb-0"><?= htmlspecialchars($menu['name']) ?></h5>
                    <span class="menu-price">₱<?= number_format((float) $menu['price'], 2) ?></span>
                  </div>
                  <?php if (($menu['category_name'] ?? '') === 'Binalot Meals') : ?>
                    <span class="badge badge-leaf text-white mb-2"><i class="fa-solid fa-leaf me-1"></i>Bestseller</span>
                  <?php endif; ?>
                  <p class="card-text text-muted small"><?= htmlspecialchars($menu['description'] ?: 'Freshly prepared with care and served with a warm Filipino touch.') ?></p>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div id="menu-empty-state" class="text-center py-5 text-muted" style="display: none;">
          <p class="mb-0">No available menu items in this category right now.</p>
        </div>
      <?php else : ?>
        <div id="menu-empty-state" class="text-center py-5 text-muted">
          <p class="mb-0">No menu items are currently available. Please check back soon.</p>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- ==========================================================================
       ========================= ABOUT US SECTION ===============================
       ========================================================================== -->
  <section id="about" class="py-5 bg-light">
    <div class="container py-4">
      <div class="row align-items-center g-5">
        <div class="col-lg-6">
          <div class="position-relative">
            <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80" alt="Dining at Binalot sa Bukid" class="about-img">
          </div>
        </div>
        <div class="col-lg-6">
          <span class="text-uppercase text-success small fw-bold" style="letter-spacing: 1px;">Our Story</span>
          <h2 class="display-6 fw-bold mb-3 text-success">Warm Filipino Tradition & Home-Style Flavors</h2>
          <p class="text-muted mb-4">At <strong>Binalot sa Bukid</strong>, we bring back the joyous tradition of Filipino family dining (*salo-salo*) wrapped in natural, fragrant banana leaves. Each *binalot* meal is prepared with passion and inherited family recipes that bring you closer to home.</p>
          
          <!-- Key Features List -->
          <div class="row g-3">
            <div class="col-sm-6">
              <div class="d-flex align-items-start gap-3">
                <div class="feature-icon flex-shrink-0"><i class="fa-solid fa-leaf"></i></div>
                <div>
                  <h6 class="fw-bold mb-1">Fresh Banana Leaves</h6>
                  <p class="small text-muted mb-0">Naturally imparts an aromatic fragrance and rich aroma to warm rice and viands.</p>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="d-flex align-items-start gap-3">
                <div class="feature-icon flex-shrink-0"><i class="fa-solid fa-utensils"></i></div>
                <div>
                  <h6 class="fw-bold mb-1">Authentic Recipes</h6>
                  <p class="small text-muted mb-0">Crafted using locally-sourced fresh ingredients and time-honored techniques.</p>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="d-flex align-items-start gap-3">
                <div class="feature-icon flex-shrink-0"><i class="fa-solid fa-heart"></i></div>
                <div>
                  <h6 class="fw-bold mb-1">Family-Friendly Prices</h6>
                  <p class="small text-muted mb-0">Generous, hearty meals designed to be accessible and shared by everyone.</p>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="d-flex align-items-start gap-3">
                <div class="feature-icon flex-shrink-0"><i class="fa-solid fa-house"></i></div>
                <div>
                  <h6 class="fw-bold mb-1">Cozy Farm Ambience</h6>
                  <p class="small text-muted mb-0">A relaxing dining environment reminiscent of provincial country living.</p>
                </div>
              </div>
            </div>
          </div><!-- End Features Row -->

        </div>
      </div>
    </div>
  </section>

  <?php include 'includes/footer.php'; ?>