<?php include 'includes/header.php'; ?>

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
  <section id="menu" class="py-5">
    <div class="container py-4">
      <div class="text-center section-title">
        <span class="text-uppercase text-muted small fw-bold" style="letter-spacing: 1px;">Home-Style Specialties</span>
        <h2 class="display-5 fw-bold text-success">Our Binalot Menu</h2>
      </div>

      <!-- Menu Category Filters -->
      <div class="menu-nav text-center mb-4">
        <div class="nav nav-pills justify-content-center" id="menu-tabs">
          <button class="nav-link active" data-filter="all">All</button>
          <button class="nav-link" data-filter="binalot">Binalot Meals</button>
          <button class="nav-link" data-filter="sabaw">Soups & Main Courses</button>
          <button class="nav-link" data-filter="merienda">Desserts & Drinks</button>
        </div>
      </div>

      <!-- Menu Items Grid -->
      <div class="row g-4" id="menu-items-container">

        <!-- ITEM 1: Binalot Inasal -->
        <div class="col-md-6 col-lg-4 menu-item-card" data-category="binalot">
          <div class="menu-card">
            <div class="menu-img-wrapper">
              <img src="https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=600&q=80" class="menu-img" alt="Chicken Inasal Binalot">
            </div>
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <h5 class="card-title fw-bold mb-0">Chicken Inasal Binalot</h5>
                <span class="menu-price">₱185</span>
              </div>
              <span class="badge badge-leaf text-white mb-2"><i class="fa-solid fa-leaf me-1"></i>Bestseller</span>
              <p class="card-text text-muted small">Grilled marinated chicken infused with lemongrass and annatto, wrapped in banana leaf with steamed rice, salted egg, and fresh tomatoes.</p>
            </div>
          </div>
        </div>

        <!-- ITEM 2: Pork Adobo Binalot -->
        <div class="col-md-6 col-lg-4 menu-item-card" data-category="binalot">
          <div class="menu-card">
            <div class="menu-img-wrapper">
              <img src="https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=600&q=80" class="menu-img" alt="Pork Adobo Binalot">
            </div>
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <h5 class="card-title fw-bold mb-0">Pork Adobo Binalot</h5>
                <span class="menu-price">₱175</span>
              </div>
              <span class="badge badge-special text-white mb-2">Chef's Special</span>
              <p class="card-text text-muted small">Tender pork belly simmered in soy sauce, vinegar, and garlic flakes, wrapped in banana leaf with rice and pickled papaya (*atsara*).</p>
            </div>
          </div>
        </div>

        <!-- ITEM 3: Lechon Kawali Binalot -->
        <div class="col-md-6 col-lg-4 menu-item-card" data-category="binalot">
          <div class="menu-card">
            <div class="menu-img-wrapper">
              <img src="https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=600&q=80" class="menu-img" alt="Crispy Lechon Kawali">
            </div>
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <h5 class="card-title fw-bold mb-0">Crispy Lechon Kawali</h5>
                <span class="menu-price">₱195</span>
              </div>
              <span class="badge badge-special text-white mb-2">Popular</span>
              <p class="card-text text-muted small">Deep-fried golden pork belly with extra crispy skin, served with homemade liver sauce, steamed rice, and salted egg in a banana leaf.</p>
            </div>
          </div>
        </div>

        <!-- ITEM 4: Sinigang na Baboy -->
        <div class="col-md-6 col-lg-4 menu-item-card" data-category="sabaw">
          <div class="menu-card">
            <div class="menu-img-wrapper">
              <img src="https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?auto=format&fit=crop&w=600&q=80" class="menu-img" alt="Sinigang na Baboy">
            </div>
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <h5 class="card-title fw-bold mb-0">Sinigang na Baboy</h5>
                <span class="menu-price">₱280</span>
              </div>
              <p class="card-text text-muted small">Traditional Filipino sour tamarind broth loaded with tender pork, water spinach, radish, taro, and fresh vegetables.</p>
            </div>
          </div>
        </div>

        <!-- ITEM 5: Special Halo-Halo -->
        <div class="col-md-6 col-lg-4 menu-item-card" data-category="merienda">
          <div class="menu-card">
            <div class="menu-img-wrapper">
              <img src="https://images.unsplash.com/photo-1606313564200-e75d5e30476c?auto=format&fit=crop&w=600&q=80" class="menu-img" alt="Special Bukid Halo-Halo">
            </div>
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <h5 class="card-title fw-bold mb-0">Special Bukid Halo-Halo</h5>
                <span class="menu-price">₱120</span>
              </div>
              <span class="badge badge-leaf text-white mb-2">Dessert</span>
              <p class="card-text text-muted small">Refreshing shaved ice dessert topped with creamy leche flan, ube halaya, sweetened banana, coconut gel, milk, and toasted pinipig.</p>
            </div>
          </div>
        </div>

        <!-- ITEM 6: Sago't Gulaman -->
        <div class="col-md-6 col-lg-4 menu-item-card" data-category="merienda">
          <div class="menu-card">
            <div class="menu-img-wrapper">
              <img src="https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?auto=format&fit=crop&w=600&q=80" class="menu-img" alt="Cold Sago't Gulaman">
            </div>
            <div class="card-body p-4">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <h5 class="card-title fw-bold mb-0">Cold Sago't Gulaman</h5>
                <span class="menu-price">₱60</span>
              </div>
              <p class="card-text text-muted small">Classic Filipino beverage prepared with brown sugar syrup (*arnibal*), tapioca pearls, grass jelly cubes, and shaved ice.</p>
            </div>
          </div>
        </div>

      </div><!-- End Menu Container -->
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