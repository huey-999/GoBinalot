<?php include '../includes/admin_header.php'; ?>
<?php
$categoryOptions = $conn->query("SELECT id, name, status FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="main-content">
  <div class="tab-content">
    <!-- TABS: MENU ITEMS | CATEGORIES -->
    <ul class="nav nav-tabs mb-3" id="menuTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="menu-items-tab" data-bs-toggle="tab" data-bs-target="#menu-items-tab-pane" type="button" role="tab">Menu Items</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories-tab-pane" type="button" role="tab">Categories</button>
      </li>
    </ul>

    <div class="tab-content" id="menuTabsContent">

      <!-- TAB 1: MENU ITEMS -->
      <div class="tab-pane fade show active" id="menu-items-tab-pane" role="tabpanel">
        <div class="page-header d-flex justify-content-between align-items-center">
          <div>
            <h1 class="dashboard-heading">Menu Management</h1>
            <p class="page-subtitle">Manage food items, pricing, availability, and store settings.</p>
          </div>
        </div>

      <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <?php $count = $conn->query("SELECT count(*) FROM menu_items")->fetchColumn(); ?>
            <span class="text-muted small fw-semibold">Total Items</span>
            <h4 class="fw-bold text-dark mt-1 mb-0"><?= $count; ?></h4>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <?php $count = $conn->query("SELECT count(*) FROM menu_items WHERE is_available = 'Y'")->fetchColumn(); ?>
            <span class="text-muted small fw-semibold">Available</span>
            <h4 class="fw-bold text-dark mt-1 mb-0"><?= $count; ?></h4>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <?php $count = $conn->query("SELECT count(*) FROM menu_items WHERE is_available = 'N'")->fetchColumn(); ?>
            <span class="text-muted small fw-semibold">Unavailable</span>
            <h4 class="fw-bold text-dark mt-1 mb-0"><?= $count; ?></h4>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <?php $count = $conn->query("SELECT count(*) FROM categories")->fetchColumn(); ?>
            <span class="text-muted small fw-semibold">Categories</span>
            <h4 class="fw-bold text-dark mt-1 mb-0"><?= $count; ?></h4>
          </div>
        </div>
      </div>

      <div class="table-card p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="fw-bold text-success mb-0">Menu Items</h4>
          <button type="button" id="addMenuItemBtn" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addMenuItemModal">
            <i class="fa-solid fa-plus me-1"></i> Add Item
          </button>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle table-sm mb-0 datatable-table">
            <thead class="table-light">
              <tr>
                <th>Item</th>
                <th>Category</th>
                <th>Price</th>
                <th>Description</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $query = "SELECT m.*, c.name AS category_name
                              FROM menu_items m
                              LEFT JOIN categories c ON c.id = m.category_id";
                $statement = $conn->prepare($query);
                $statement->execute();
                $result = $statement->fetchAll(PDO::FETCH_ASSOC);

                if ($result) {
                  foreach ($result as $menu) {
                    $menuDescription = trim((string) ($menu['description'] ?? ''));
                    if ($menuDescription === '') {
                      $menuDescription = 'No description provided.';
                    } elseif (mb_strlen($menuDescription) > 90) {
                      $menuDescription = mb_substr($menuDescription, 0, 90) . '...';
                    }
              ?>
              <tr data-menu-id="<?= $menu['id'] ?>">
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width:38px;height:38px; overflow:hidden; border:1px solid rgba(0,0,0,0.08);">
                      <?php if (!empty($menu['image'])) : ?>
                        <img src="../img/menu/<?= htmlspecialchars($menu['image']) ?>" alt="<?= htmlspecialchars($menu['name']) ?>" style="width:100%;height:100%;object-fit:cover;">
                      <?php else : ?>
                        <i class="fa-solid fa-bowl-food text-success"></i>
                      <?php endif; ?>
                    </div>
                    <div>
                      <div class="fw-semibold"><?= $menu['name'] ?></div>
                    </div>
                  </div>
                </td>
                <td><?= $menu['category_name'] ?? 'Uncategorized' ?></td>
                <td>₱<?= number_format((float) $menu['price'], 2) ?></td>
                <td class="text-muted small description-cell"><?= htmlspecialchars($menuDescription) ?></td>
                <td>
                  <?php if ($menu['is_available'] === 'Y') : ?>
                    <span class="badge bg-success">Available</span>
                  <?php else : ?>
                    <span class="badge bg-secondary">Unavailable</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="d-flex flex-wrap gap-1">
                    <button type="button" class="btn btn-xs btn-outline-secondary btn-edit-menu-item"
                            data-menu-id="<?= (int) $menu['id'] ?>"
                            data-menu-name="<?= $menu['name'] ?>"
                            data-menu-category-id="<?= (int) ($menu['category_id'] ?? 0) ?>"
                            data-menu-price="<?= (string) $menu['price'] ?>"
                            data-menu-description="<?= $menu['description'] ?? '' ?>"
                            data-menu-status="<?= $menu['is_available'] ?>"
                            data-menu-image="<?= htmlspecialchars($menu['image'] ?? '') ?>"
                            title="Edit"><i class="fa-solid fa-pen"></i></button>
                    <button type="button" class="btn btn-xs btn-outline-danger btn-delete-menu-item" data-menu-id="<?= (int) $menu['id'] ?>" data-menu-name="<?= $menu['name'] ?>" title="Delete"><i class="fa-solid fa-trash"></i></button>
                  </div>
                </td>
              </tr>
              <?php
                  }
                } else {
                  echo '<tr><td colspan="6" class="text-center text-muted small">No menu items found.</td></tr>';
                }
              ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="table-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="fw-bold text-success mb-0">Store Settings</h5>
        </div>

        <div class="row g-3">
          <div class="col-md-6">
            <div class="border rounded p-3 h-100">
              <div class="small text-muted mb-2">GCash QR Image</div>
              <div class="d-flex align-items-center gap-3">
                <div class="bg-light rounded" style="width:88px;height:88px; display:flex; align-items:center; justify-content:center;">
                  <i class="fa-solid fa-qrcode fa-2x text-success"></i>
                </div>
                <div>
                  <div class="small fw-semibold">Current QR</div>
                  <button class="btn btn-outline-success btn-sm mt-2">Upload New QR</button>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="border rounded p-3 h-100">
              <div class="small text-muted mb-2">Delivery Fee</div>
              <label class="form-label small text-muted mb-1">Fee (PHP)</label>
              <input type="number" class="form-control form-control-sm mb-2" value="49">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" checked>
                <label class="form-check-label small">Enabled for delivery orders</label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 2: CATEGORIES -->
    <div class="tab-pane fade" id="categories-tab-pane" role="tabpanel">
      <div class="page-header d-flex justify-content-between align-items-center">
        <div>
          <h1 class="dashboard-heading">Menu Categories</h1>
          <p class="page-subtitle">Manage food categories used to group your menu items.</p>
        </div>
      </div>

      <div class="table-card p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="fw-bold text-success mb-0">Categories</h4>
          <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="fa-solid fa-plus me-1"></i> Add Category
          </button>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle table-sm mb-0 datatable-table">
            <thead class="table-light">
              <tr>
                <th>Category</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $query = "SELECT * FROM categories";
                $statement = $conn->prepare($query);
                $statement->execute();
                
                $statement->setFetchMode(PDO::FETCH_ASSOC);
                $result = $statement->fetchAll();

                if ($result) {
                  foreach ($result as $category) {              
              ?>
              <tr>
                <td class="fw-bold"><?= $category['name'] ?></td>
                <td>
                  <?php if($category['status'] === 'active') : ?>
                    <span class="badge bg-success">Active</span>
                  <?php else : ?>
                    <span class="badge bg-secondary">Inactive</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="d-flex flex-wrap gap-1">
                    <button class="btn btn-xs btn-outline-secondary btn-edit-category" data-category-id="<?= (int) $category['id'] ?>" data-category-name="<?= htmlspecialchars($category['name']) ?>" data-category-status="<?= htmlspecialchars($category['status']) ?>" title="Edit"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn btn-xs btn-outline-danger btn-delete-category" data-category-id="<?= (int) $category['id'] ?>" data-category-name="<?= htmlspecialchars($category['name']) ?>" title="Delete"><i class="fa-solid fa-trash"></i></button>
                  </div>
                </td>
              </tr>
              <?php
                }
              } else {
                echo '<tr><td colspan="3" class="text-center text-muted small">No categories found.</td></tr>';
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</main>

<!-- ADD MENU MODAL -->
<div class="modal fade" id="addMenuItemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-success">Add New Menu Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3">
        <form id="addMenuItemForm" enctype="multipart/form-data">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Dish Title</label>
            <input type="text" name="menu_name" class="form-control form-control-sm" placeholder="e.g. Inihaw na Liempo Binalot" required>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label small fw-semibold">Category</label>
              <select name="category_id" class="form-select form-select-sm" required>
                <option value="">Select category</option>
                <?php
                  foreach ($categoryOptions as $category) {
                    echo '<option value="' . $category['id'] . '">' . $category['name'] . '</option>';
                  }
                ?>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label small fw-semibold">Price (PHP)</label>
              <input type="number" name="price" step="0.01" min="0" class="form-control form-control-sm" placeholder="180" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Image</label>
            <input type="file" name="image" class="form-control form-control-sm" accept="image/*">
            <div class="form-text small text-muted">Optional. Accepts jpg, png, or webp files.</div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Description</label>
            <textarea name="description" class="form-control form-control-sm" rows="3" placeholder="Brief description of dish..."></textarea>
          </div>

          <div class="form-check form-switch mb-3">
            <input class="form-check-input" name="is_available" type="checkbox" checked>
            <label class="form-check-label small">Available for order</label>
          </div>

          <div class="d-flex gap-2 modal-form-row">
            <button type="submit" name="add_menu_item" class="btn btn-primary-custom modal-form-btn">Save Item</button>
            <button type="button" class="btn btn-outline-secondary modal-form-btn" data-bs-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editMenuItemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-success">Edit Menu Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3">
        <form id="editMenuItemForm" enctype="multipart/form-data">
          <input type="hidden" name="menu_id">

          <div class="mb-3">
            <label class="form-label small fw-semibold">Dish Title</label>
            <input type="text" name="menu_name" class="form-control form-control-sm" required>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label small fw-semibold">Category</label>
              <select name="category_id" class="form-select form-select-sm" required>
                <option value="">Select category</option>
                <?php
                  foreach ($categoryOptions as $category) {
                    echo '<option value="' . (int) $category['id'] . '">' . htmlspecialchars($category['name']) . '</option>';
                  }
                ?>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label small fw-semibold">Price (PHP)</label>
              <input type="number" name="price" step="0.01" min="0" class="form-control form-control-sm" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Image</label>
            <input type="file" name="image" class="form-control form-control-sm" accept="image/*">
            <div class="form-text small text-muted">Optional. Upload a new image to replace the current one.</div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Description</label>
            <textarea name="description" class="form-control form-control-sm" rows="3"></textarea>
          </div>

          <div class="form-check form-switch mb-3">
            <input class="form-check-input" name="is_available" type="checkbox">
            <label class="form-check-label small">Available</label>
          </div>

          <div class="d-flex gap-2 modal-form-row">
            <button type="submit" class="btn btn-primary-custom modal-form-btn">Update Item</button>
            <button type="button" class="btn btn-outline-secondary modal-form-btn" data-bs-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="toggleAvailabilityModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-dark">Availability Update</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3">
        <p class="mb-0 small">Mark this item as available or unavailable for customer ordering.</p>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-dark btn-sm">Save</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="archiveMenuModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-danger">Archive Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3">
        <p class="mb-0 small">This will archive the item without deleting its menu history.</p>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-danger btn-sm">Archive</button>
      </div>
    </div>
  </div>
</div>

<!-- ADD CATEGORY MODAL -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-success">Add New Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-3">
        <form>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Category Name</label>
            <input type="text" name="category_name" class="form-control form-control-sm" placeholder="e.g. Rice Meals, Grilled, Seafood" required>
          </div>

          <div class="form-check form-switch mb-3">
            <input class="form-check-input" name="status" type="checkbox" checked>
            <label class="form-check-label small">Active (visible to customers)</label>
          </div>

        <div class="d-flex gap-2 modal-form-row">
          <button type="submit" class="btn btn-primary-custom modal-form-btn">Save Category</button>
          <button type="button" class="btn btn-outline-secondary modal-form-btn" data-bs-dismiss="modal">Close</button>
        </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- EDIT CATEGORY MODAL -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-success">Edit Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-3">
        <form>
          <input type="hidden" name="category_id">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Category Name</label>
            <input type="text" name="category_name" class="form-control form-control-sm" required>
          </div>

          <div class="form-check form-switch mb-3">
            <input class="form-check-input" name="status" type="checkbox" checked>
            <label class="form-check-label small">Active (visible to customers)</label>
          </div>

        <div class="d-flex gap-2 modal-form-row">
          <button type="submit" class="btn btn-primary-custom modal-form-btn">Update Category</button>
          <button type="button" class="btn btn-outline-secondary modal-form-btn" data-bs-dismiss="modal">Close</button>
        </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/admin_footer.php'; ?>