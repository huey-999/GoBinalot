<?php include '../includes/admin_header.php'; ?>

<?php
$categories = $conn->query(
  "SELECT id, name, status
   FROM categories
   WHERE status = 1
   ORDER BY name ASC"
)->fetchAll(PDO::FETCH_ASSOC);

// Fetch customer's saved addresses for checkout
$firstName = htmlspecialchars($_SESSION['user_data']['first_name'] ?? '');
$lastName = htmlspecialchars($_SESSION['user_data']['last_name'] ?? '');
$customerName = trim($firstName . ' ' . $lastName) ?: 'Customer';
$customerPhone = '';
$customerAddress = '';
$customerAddresses = [];

$userId = $_SESSION['user_data']['id'] ?? 0;
if ($userId) {
  $addrStmt = $conn->prepare("SELECT id, type, address, contact_number, is_default FROM addresses WHERE user_id = :user_id ORDER BY is_default DESC, id DESC");
  $addrStmt->execute([':user_id' => $userId]);
  $customerAddresses = $addrStmt->fetchAll(PDO::FETCH_ASSOC);
  
  // Set default values from first/default address
  if (!empty($customerAddresses)) {
    $defaultAddr = $customerAddresses[0];
    $customerPhone = htmlspecialchars($defaultAddr['contact_number'] ?? '');
    $customerAddress = htmlspecialchars($defaultAddr['address'] ?? '');
  }
}

$menuItems = $conn->query(
  "SELECT m.*, c.name AS category_name
   FROM menu_items m
   LEFT JOIN categories c ON c.id = m.category_id
   WHERE m.is_available = 1
     AND (c.status = 1 OR c.status IS NULL)
   ORDER BY c.name ASC, m.name ASC"
)->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="main-content customer-order-page">
  <div class="customer-order-header">
    <div class="order-header-info">
      <p class="eyebrow text-uppercase mb-2">Freshly packed</p>
      <h1 class="dashboard-heading mb-0">Build your binalot order</h1>
    </div>

    <div class="order-header-controls d-flex align-items-center gap-2">
      <div class="delivery-toggle-group" aria-label="Order type selector" role="radiogroup">
        <button class="delivery-toggle" type="button" data-order-type="delivery" role="radio" aria-checked="false">
          <i class="fa-solid fa-motorcycle me-1"></i>Delivery
        </button>
        <button class="delivery-toggle" type="button" data-order-type="pickup" role="radio" aria-checked="false">
          <i class="fa-solid fa-bag-shopping me-1"></i>Pickup
        </button>
      </div>
    </div>
  </div>

  <div class="customer-order-layout">
    <section class="menu-order-panel">
      <!-- <div class="promo-banner">
        <div class="promo-content">
          <p class="promo-tag">Today's favorite</p>
          <h2>Home-style Filipino comfort food, packed with flavor.</h2>
        </div>
        <div class="promo-badge">
          <span class="badge-label">Free</span>
          <strong class="badge-amount">₱49</strong>
          <small class="badge-condition">delivery over ₱500</small>
        </div>
      </div> -->

      <div class="toolbar-row">
        <div class="search-box">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" class="form-control" placeholder="Search menu items..." aria-label="Search menu" />
        </div>

        <div class="filter-pills" aria-label="Category filter" role="tablist">
          <button class="filter-pill active" type="button" data-filter="all" role="tab">All</button>
          <?php foreach ($categories as $category): ?>
            <?php $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', trim($category['name']))); ?>
            <button class="filter-pill" type="button" data-filter="<?= $slug ?>" data-category-name="<?= htmlspecialchars($category['name']) ?>" role="tab"><?= htmlspecialchars($category['name']) ?></button>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="menu-grid" id="menuGrid">
        <?php if (!empty($menuItems)) : ?>
          <?php foreach ($menuItems as $menu): ?>
            <?php
              $image = !empty($menu['image']) ? '../img/menu/' . htmlspecialchars($menu['image']) : 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=900&q=80';
              $categoryName = htmlspecialchars($menu['category_name'] ?? 'Daily Special');
              $categorySlug = !empty($menu['category_name']) ? strtolower(preg_replace('/[^a-z0-9]+/', '-', trim($menu['category_name']))) : 'daily-special';
            ?>
            <article class="menu-order-card" data-category="<?= $categorySlug ?>" data-category-name="<?= $categoryName ?>">
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
                    data-image="<?= $image ?>"
                    data-category="<?= $categorySlug ?>"
                    data-menu-item-id="<?= (int) $menu['id'] ?>">
                    <i class="fa-solid fa-cart-plus me-2"></i>Add to cart
                  </button>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        <?php else : ?>
          <div class="empty-menu-state">
            <div class="empty-icon"><i class="fa-solid fa-utensils"></i></div>
            <p>No menu items available right now.</p>
            <p class="empty-subtitle">Please check back later.</p>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <aside class="order-cart-sidebar">
      <div class="order-cart-card">
        <div class="cart-header">
          <div class="cart-header-info">
            <p class="eyebrow text-uppercase mb-1">Your order</p>
            <h3>Cart Summary</h3>
          </div>
          <span class="cart-count" id="cartCount">0</span>
        </div>

        <div class="cart-items" id="cartItems">
          <div class="empty-cart">
            <i class="fa-solid fa-basket-shopping"></i>
            <p>Your cart is empty</p>
            <p class="empty-subtitle">Add some favorite meals to get started.</p>
          </div>
        </div>

        <!-- <div class="cart-note">
          <label for="orderNotes">Order notes</label>
          <textarea id="orderNotes" class="form-control" rows="2" placeholder="No onions, extra rice, add utensils, etc."></textarea>
        </div>

        <div class="cart-promo">
          <label for="promoCode">Promo code</label>
          <div class="promo-input-wrap">
            <input id="promoCode" type="text" value="WELCOME10" aria-label="Promo code" />
            <button type="button" class="btn-apply-promo">Apply</button>
          </div>
        </div> -->

        <div class="totals">
          <div class="total-row">
            <span>Subtotal</span>
            <strong id="subtotalValue">₱0.00</strong>
          </div>
          <div class="total-row">
            <span>Delivery</span>
            <strong id="deliveryValue">₱49.00</strong>
          </div>
          <div class="total-row grand-total">
            <span>Total</span>
            <strong id="totalValue">₱49.00</strong>
          </div>
        </div>

        <div class="checkout-actions">
          <!-- <button type="button" class="btn btn-outline-secondary w-100 mb-2 btn-save-for-later">
            <i class="fa-solid fa-heart me-2"></i>Save for later
          </button> -->
          <button type="button" class="btn btn-success w-100 btn-checkout" id="openCheckoutBtn">
            <i class="fa-solid fa-credit-card me-2"></i>Proceed to Checkout
          </button>
        </div>
      </div>
    </aside>
  </div>
</main>

<div class="modal fade checkout-modal" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header border-0 pb-2">
        <div>
          <!-- <p class="modal-pretitle">Almost there</p> -->
          <h5 class="modal-title fw-bold" id="checkoutModalLabel">Checkout</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="checkoutForm">
        <div class="modal-body p-3">
          <!-- Customer -->
          <div class="checkout-section mb-3">
            <div class="checkout-section-title">Customer</div>
            <div class="row g-2">
              <div class="col-md-6">
                <label class="form-label">Customer name</label>
                <p class="form-control-static"><?= $customerName ?></p>
              </div>
            </div>
          </div>

          <!-- Delivery address -->
          <div class="checkout-section mb-3" id="checkoutAddressSection" data-order-type="delivery">
            <div class="checkout-section-title">Delivery address</div>
            <?php if (!empty($customerAddresses)): ?>
              <div class="saved-addresses-list" id="savedAddressesList">
                <?php foreach ($customerAddresses as $addr): ?>
                  <div class="saved-address-item">
                    <input type="radio" name="saved_address" id="saved_addr_<?= $addr['id'] ?>" value="<?= $addr['id'] ?>" <?= !empty($addr['is_default']) ? 'checked' : '' ?> onchange="selectSavedAddress(<?= json_encode($addr) ?>)">
                    <label for="saved_addr_<?= $addr['id'] ?>" class="saved-address-label">
                      <?php if (!empty($addr['is_default'])): ?>
                        <span class="address-default-badge">Default</span>
                      <?php endif; ?>
                      <div class="address-type"><?= htmlspecialchars($addr['type'] ?? 'Home') ?></div>
                      <div class="address-text"><?= htmlspecialchars($addr['address']) ?></div>
                      <div class="address-phone"><?= htmlspecialchars($addr['contact_number'] ?? '') ?></div>
                    </label>
                  </div>
                <?php endforeach; ?>
              </div>
              <input type="hidden" id="selectedAddressId" value="<?= $customerAddresses[0]['id'] ?? '' ?>">
              <input type="hidden" id="selectedAddressText" value="<?= htmlspecialchars($customerAddresses[0]['address'] ?? '') ?>">
              <input type="hidden" id="selectedAddressPhone" value="<?= htmlspecialchars($customerAddresses[0]['contact_number'] ?? '') ?>">
            <?php else: ?>
              <textarea class="form-control" rows="2" placeholder="House / unit number, street, barangay, city..." required><?= $customerAddress ?></textarea>
            <?php endif; ?>
          </div>
          <input type="hidden" id="checkoutOrderType" value="1">

          <!-- Order summary -->
          <div class="checkout-section checkout-summary-section">
            <div class="checkout-section-title">Order summary</div>
            <div id="checkoutItemList" class="checkout-item-list mb-2"></div>
            <div class="summary-row"><span>Items</span><strong id="checkoutItemsCount">0</strong></div>
            <div class="summary-row"><span id="checkoutDeliveryLabel">Delivery</span><strong id="checkoutDelivery">₱49.00</strong></div>
            <div class="summary-row total"><span>Total</span><strong id="checkoutTotal">₱49.00</strong></div>
          </div>
        </div>

        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-success btn-checkout-submit" id="btnCheckout" disabled>Place Order</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include '../includes/gcash_modal.php'; ?>>

<!-- Added to cart toast -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1060;">
  <div id="addToCartToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">
        <i class="fa-solid fa-cart-plus me-2"></i>
        <strong id="toastItemName"></strong> added to cart
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

<?php include '../includes/admin_footer.php'; ?>