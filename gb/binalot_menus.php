<?php include '../includes/admin_header.php'; ?>

<?php
$categories = $conn->query(
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
     AND (c.status = 'active' OR c.status IS NULL)
   ORDER BY c.name ASC, m.name ASC"
)->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="main-content customer-order-page">
  <div class="customer-order-header">
    <div>
      <p class="eyebrow text-uppercase">Freshly packed</p>
      <h1 class="dashboard-heading mb-0">Build your binalot order</h1>
    </div>

    <div class="d-flex align-items-center gap-2">
      <button type="button" class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
        <i class="fa-solid fa-moon"></i>
      </button>

      <div class="delivery-toggle-group" aria-label="Order type selector">
        <button class="delivery-toggle active" type="button" data-order-type="delivery">
          <i class="fa-solid fa-motorcycle me-1"></i>Delivery
        </button>
        <button class="delivery-toggle" type="button" data-order-type="pickup">
          <i class="fa-solid fa-bag-shopping me-1"></i>Pickup
        </button>
      </div>
    </div>
  </div>

  <div class="customer-order-layout">
    <section class="menu-order-panel">
      <div class="promo-banner">
        <div>
          <p class="promo-tag">Today’s favorite</p>
          <h2>Home-style Filipino comfort food, packed with flavor.</h2>
        </div>
        <div class="promo-badge">
          <span>Free</span>
          <strong>₱49</strong>
          <small>delivery over ₱500</small>
        </div>
      </div>

      <div class="toolbar-row">
        <div class="search-box">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" class="form-control" placeholder="Search menu items..." aria-label="Search menu" />
        </div>

        <div class="filter-pills" aria-label="Category filter">
          <button class="filter-pill active" type="button" data-filter="all">All</button>
          <?php foreach ($categories as $category): ?>
            <button class="filter-pill" type="button" data-filter="<?= htmlspecialchars($category['name']) ?>"><?= htmlspecialchars($category['name']) ?></button>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="menu-grid" id="menuGrid">
        <?php if (!empty($menuItems)) : ?>
          <?php foreach ($menuItems as $menu): ?>
            <?php $image = !empty($menu['image']) ? '../img/menu/' . htmlspecialchars($menu['image']) : 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=900&q=80'; ?>
            <?php $categoryName = htmlspecialchars($menu['category_name'] ?? 'Daily Special'); ?>
            <article class="menu-order-card" data-category="<?= htmlspecialchars($menu['category_name'] ?? 'daily-special') ?>">
              <div class="menu-order-image-wrap">
                <img src="<?= $image ?>" alt="<?= htmlspecialchars($menu['name']) ?>" loading="lazy" />
                <span class="menu-badge"><?= $categoryName ?></span>
              </div>

              <div class="menu-order-body">
                <div class="menu-top-row">
                  <div>
                    <h3><?= htmlspecialchars($menu['name']) ?></h3>
                    <p><?= htmlspecialchars($menu['description'] ?: 'Freshly prepared and served with warm rice and local flavor.') ?></p>
                  </div>
                </div>

                <div class="meta-row">
                  <span><i class="fa-solid fa-star"></i> 4.8</span>
                  <span><i class="fa-solid fa-clock"></i> 15-20 min</span>
                </div>

                <div class="menu-bottom-row">
                  <div class="price-block">
                    <small>Starts at</small>
                    <strong>₱<?= number_format((float) $menu['price'], 2) ?></strong>
                  </div>

                  <div class="qty-wrap">
                    <button class="qty-btn" type="button" data-action="decrease" aria-label="Decrease quantity">-</button>
                    <span class="qty-value">1</span>
                    <button class="qty-btn" type="button" data-action="increase" aria-label="Increase quantity">+</button>
                  </div>

                  <button class="btn btn-success add-to-cart-btn" type="button"
                    data-name="<?= htmlspecialchars($menu['name']) ?>"
                    data-price="<?= (float) $menu['price'] ?>"
                    data-image="<?= $image ?>">
                    <i class="fa-solid fa-cart-plus me-2"></i>Add
                  </button>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        <?php else : ?>
          <div class="empty-menu-state">
            <i class="fa-solid fa-utensils"></i>
            <p>No menu items available right now. Please check back later.</p>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <aside class="order-cart-sidebar">
      <div class="order-cart-card">
        <div class="cart-header">
          <div>
            <p class="eyebrow text-uppercase mb-1">Your order</p>
            <h3>Cart Summary</h3>
          </div>
          <span class="cart-count" id="cartCount">0</span>
        </div>

        <div class="cart-items" id="cartItems">
          <div class="empty-cart">
            <i class="fa-solid fa-basket-shopping"></i>
            <p>Your cart is empty. Add some favorite meals to get started.</p>
          </div>
        </div>

        <div class="cart-note">
          <label for="orderNotes">Order notes</label>
          <textarea id="orderNotes" class="form-control" rows="2" placeholder="No onions, extra rice, add utensils, etc."></textarea>
        </div>

        <div class="cart-promo">
          <label for="promoCode">Promo code</label>
          <div class="promo-input-wrap">
            <input id="promoCode" type="text" value="WELCOME10" aria-label="Promo code" />
            <button type="button">Apply</button>
          </div>
        </div>

        <div class="totals">
          <div class="total-row">
            <span>Subtotal</span>
            <strong id="subtotalValue">₱0.00</strong>
          </div>
          <div class="total-row">
            <span>Delivery</span>
            <strong id="deliveryValue">₱49.00</strong>
          </div>
          <div class="total-row">
            <span>Service fee</span>
            <strong id="serviceValue">₱0.00</strong>
          </div>
          <div class="total-row grand-total">
            <span>Total</span>
            <strong id="totalValue">₱49.00</strong>
          </div>
        </div>

        <div class="checkout-actions">
          <button type="button" class="btn btn-outline-secondary w-100 mb-2">
            <i class="fa-solid fa-heart me-2"></i>Save for later
          </button>
          <button type="button" class="btn btn-success w-100 btn-checkout" data-bs-toggle="modal" data-bs-target="#checkoutModal">
            <i class="fa-solid fa-credit-card me-2"></i>Proceed to Checkout
          </button>
        </div>
      </div>
    </aside>
  </div>
</main>

<div class="modal fade checkout-modal" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header border-0 pb-2">
        <div>
          <p class="modal-pretitle">Almost there</p>
          <h5 class="modal-title fw-bold" id="checkoutModalLabel">Checkout</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="checkoutForm">
        <div class="modal-body p-3">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Full name</label>
              <input type="text" class="form-control" value="John Doe" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone number</label>
              <input type="tel" class="form-control" value="09171234567" required>
            </div>
            <div class="col-12">
              <label class="form-label">Delivery address</label>
              <textarea class="form-control" rows="3" placeholder="House / unit number, street, barangay, city..." required>123 Mabini Street, Lahug, Cebu City</textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Order type</label>
              <select class="form-select">
                <option selected>Delivery</option>
                <option>Pickup</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Payment method</label>
              <select class="form-select">
                <option selected>Cash on Delivery</option>
                <option>GCash</option>
                <option>Bank Transfer</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Special instructions</label>
              <textarea class="form-control" rows="2" id="checkoutNotes" placeholder="Any notes for kitchen or rider?"></textarea>
            </div>
          </div>

          <div class="checkout-summary mt-3">
            <div class="summary-row"><span>Items</span><strong id="checkoutItemsCount">0</strong></div>
            <div class="summary-row"><span>Delivery</span><strong id="checkoutDelivery">₱49.00</strong></div>
            <div class="summary-row total"><span>Total</span><strong id="checkoutTotal">₱49.00</strong></div>
          </div>
        </div>

        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success btn-checkout-submit">Place Order</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include '../includes/admin_footer.php'; ?>

<script>
  const cart = [];
  const cartItemsContainer = document.getElementById('cartItems');
  const subtotalValue = document.getElementById('subtotalValue');
  const deliveryValue = document.getElementById('deliveryValue');
  const serviceValue = document.getElementById('serviceValue');
  const totalValue = document.getElementById('totalValue');
  const cartCount = document.getElementById('cartCount');

  function formatMoney(value) {
    return new Intl.NumberFormat('en-PH', {
      style: 'currency',
      currency: 'PHP'
    }).format(value);
  }

  function renderCart() {
    const subtotal = cart.reduce((sum, item) => sum + item.price * item.qty, 0);
    const delivery = subtotal > 500 ? 0 : 49;
    const serviceFee = subtotal > 0 ? 15 : 0;
    const total = subtotal + delivery + serviceFee;

    subtotalValue.textContent = formatMoney(subtotal);
    deliveryValue.textContent = formatMoney(delivery);
    serviceValue.textContent = formatMoney(serviceFee);
    totalValue.textContent = formatMoney(total);
    cartCount.textContent = cart.reduce((sum, item) => sum + item.qty, 0);

    if (cart.length === 0) {
      cartItemsContainer.innerHTML = `
        <div class="empty-cart">
          <i class="fa-solid fa-basket-shopping"></i>
          <p>Your cart is empty. Add some favorite meals to get started.</p>
        </div>
      `;
      return;
    }

    cartItemsContainer.innerHTML = cart.map(item => `
      <div class="cart-item" data-id="${item.name}">
        <div class="cart-item-thumb">
          <img src="${item.image}" alt="${item.name}" />
        </div>
        <div class="cart-item-body">
          <div class="cart-item-head">
            <h4>${item.name}</h4>
            <button type="button" class="remove-item" data-name="${item.name}">Remove</button>
          </div>
          <div class="cart-item-meta">
            <span>${formatMoney(item.price)} each</span>
            <div class="mini-qty">
              <button type="button" data-action="minus" data-name="${item.name}">-</button>
              <span>${item.qty}</span>
              <button type="button" data-action="plus" data-name="${item.name}">+</button>
            </div>
          </div>
        </div>
      </div>
    `).join('');
  }

  function addToCart(name, price, image) {
    const existingItem = cart.find(item => item.name === name);
    if (existingItem) {
      existingItem.qty += 1;
    } else {
      cart.push({ name, price, image, qty: 1 });
    }
    renderCart();
  }

  document.addEventListener('click', function (event) {
    const addButton = event.target.closest('.add-to-cart-btn');
    if (addButton) {
      const name = addButton.dataset.name;
      const price = Number(addButton.dataset.price || 0);
      const image = addButton.dataset.image || '';
      addToCart(name, price, image);
      return;
    }

    const removeButton = event.target.closest('.remove-item');
    if (removeButton) {
      const name = removeButton.dataset.name;
      const index = cart.findIndex(item => item.name === name);
      if (index >= 0) {
        cart.splice(index, 1);
      }
      renderCart();
      return;
    }

    const qtyButton = event.target.closest('[data-action]');
    if (!qtyButton) return;

    const action = qtyButton.dataset.action;
    const name = qtyButton.dataset.name;
    const item = cart.find(entry => entry.name === name);
    if (!item) return;

    if (action === 'plus') {
      item.qty += 1;
    } else if (action === 'minus') {
      item.qty -= 1;
      if (item.qty <= 0) {
        const index = cart.findIndex(entry => entry.name === name);
        if (index >= 0) cart.splice(index, 1);
      }
    }
    renderCart();
  });

  document.addEventListener('click', function (event) {
    const qtyBtn = event.target.closest('.qty-btn');
    if (!qtyBtn) return;

    const card = qtyBtn.closest('.menu-order-card');
    if (!card) return;

    const qtyValue = card.querySelector('.qty-value');
    let qty = Number(qtyValue.textContent || 1);

    if (qtyBtn.dataset.action === 'increase') {
      qty += 1;
    } else if (qtyBtn.dataset.action === 'decrease' && qty > 1) {
      qty -= 1;
    }

    qtyValue.textContent = qty;
  });

  document.querySelectorAll('.filter-pill').forEach(button => {
    button.addEventListener('click', function () {
      const category = this.dataset.filter;
      document.querySelectorAll('.filter-pill').forEach(item => item.classList.remove('active'));
      this.classList.add('active');

      document.querySelectorAll('.menu-order-card').forEach(card => {
        const matches = category === 'all' || card.dataset.category === category;
        card.style.display = matches ? 'block' : 'none';
      });
    });
  });

  const searchInput = document.querySelector('.search-box input');
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      const term = this.value.trim().toLowerCase();
      document.querySelectorAll('.menu-order-card').forEach(card => {
        const title = card.querySelector('h3')?.textContent.toLowerCase() || '';
        const matches = title.includes(term);
        card.style.display = matches ? 'block' : 'none';
      });
    });
  }

  renderCart();
</script>