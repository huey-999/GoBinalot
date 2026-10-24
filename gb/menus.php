<?php include '../includes/admin_header.php'; ?>

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
            <span class="text-muted small fw-semibold">Total Items</span>
            <h4 class="fw-bold text-dark mt-1 mb-0">42</h4>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <span class="text-muted small fw-semibold">Available</span>
            <h4 class="fw-bold text-dark mt-1 mb-0">34</h4>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <span class="text-muted small fw-semibold">Unavailable</span>
            <h4 class="fw-bold text-dark mt-1 mb-0">6</h4>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <span class="text-muted small fw-semibold">Categories</span>
            <h4 class="fw-bold text-dark mt-1 mb-0">8</h4>
          </div>
        </div>
      </div>

      <div class="table-card p-3 mb-3">
        <div class="row g-2 align-items-end">
          <div class="col-md-4">
            <label class="form-label small text-muted mb-1">Search</label>
            <input type="text" class="form-control form-control-sm" placeholder="Find menu item">
          </div>

          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Category</label>
            <select class="form-select form-select-sm">
              <option selected>All</option>
              <option>Binalot Meals</option>
              <option>Soups & Main</option>
              <option>Desserts & Drinks</option>
              <option>Add-ons</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Status</label>
            <select class="form-select form-select-sm">
              <option selected>All</option>
              <option>Available</option>
              <option>Unavailable</option>
            </select>
          </div>

          <div class="col-md-2">
            <div class="d-flex gap-2">
              <button class="btn btn-success btn-sm w-100">Filter</button>
            </div>
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
          <table class="table table-hover align-middle table-sm mb-0">
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
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width:38px;height:38px;">
                      <i class="fa-solid fa-bowl-food text-success"></i>
                    </div>
                    <div>
                      <div class="fw-bold">Chicken Inasal Binalot</div>
                    </div>
                  </div>
                </td>
                <td>Binalot Meals</td>
                <td>₱185.00</td>
                <td class="text-muted small">Grilled chicken wrapped in banana leaf...</td>
                <td><span class="badge bg-success">Available</span></td>
                <td>
                  <div class="d-flex flex-wrap gap-1">
                    <button class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editMenuItemModal"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn btn-xs btn-outline-dark" data-bs-toggle="modal" data-bs-target="#toggleAvailabilityModal"><i class="fa-solid fa-toggle-on"></i></button>
                    <button class="btn btn-xs btn-outline-danger" data-bs-toggle="modal" data-bs-target="#archiveMenuModal"><i class="fa-solid fa-box-archive"></i></button>
                  </div>
                </td>
              </tr>

              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width:38px;height:38px;">
                      <i class="fa-solid fa-bowl-food text-success"></i>
                    </div>
                    <div>
                      <div class="fw-bold">Crispy Lechon Kawali</div>
                    </div>
                  </div>
                </td>
                <td>Binalot Meals</td>
                <td>₱195.00</td>
                <td class="text-muted small">Crispy pork with garlic rice and dipping sauce.</td>
                <td><span class="badge bg-success">Available</span></td>
                <td>
                  <div class="d-flex flex-wrap gap-1">
                    <button class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editMenuItemModal"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn btn-xs btn-outline-dark" data-bs-toggle="modal" data-bs-target="#toggleAvailabilityModal"><i class="fa-solid fa-toggle-on"></i></button>
                    <button class="btn btn-xs btn-outline-danger" data-bs-toggle="modal" data-bs-target="#archiveMenuModal"><i class="fa-solid fa-box-archive"></i></button>
                  </div>
                </td>
              </tr>

              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width:38px;height:38px;">
                      <i class="fa-solid fa-ice-cream text-success"></i>
                    </div>
                    <div>
                      <div class="fw-bold">Special Bukid Halo-Halo</div>
                    </div>
                  </div>
                </td>
                <td>Desserts & Drinks</td>
                <td>₱120.00</td>
                <td class="text-muted small">A sweet treat made with shaved ice, toppings, and milk.</td>
                <td><span class="badge bg-secondary">Unavailable</span></td>
                <td>
                  <div class="d-flex flex-wrap gap-1">
                    <button class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editMenuItemModal"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn btn-xs btn-outline-dark" data-bs-toggle="modal" data-bs-target="#toggleAvailabilityModal"><i class="fa-solid fa-toggle-off"></i></button>
                    <button class="btn btn-xs btn-outline-danger" data-bs-toggle="modal" data-bs-target="#archiveMenuModal"><i class="fa-solid fa-box-archive"></i></button>
                  </div>
                </td>
              </tr>
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
          <table class="table table-hover align-middle table-sm mb-0">
            <thead class="table-light">
              <tr>
                <th>Category</th>
                <th>Description</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="fw-bold">Binalot Meals</td>
                <td class="text-muted small">Traditional Filipino binalot wrapped in banana leaf</td>
                <td><span class="badge bg-success">Active</span></td>
                <td>
                  <div class="d-flex flex-wrap gap-1">
                    <button class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editCategoryModal"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn btn-xs btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                  </div>
                </td>
              </tr>

              <tr>
                <td class="fw-bold">Soups & Main</td>
                <td class="text-muted small">Hearty soups and main course dishes</td>
                <td><span class="badge bg-success">Active</span></td>
                <td>
                  <div class="d-flex flex-wrap gap-1">
                    <button class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editCategoryModal"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn btn-xs btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                  </div>
                </td>
              </tr>

              <tr>
                <td class="fw-bold">Desserts & Drinks</td>
                <td class="text-muted small">Sweet treats and refreshing beverages</td>
                <td><span class="badge bg-success">Active</span></td>
                <td>
                  <div class="d-flex flex-wrap gap-1">
                    <button class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editCategoryModal"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn btn-xs btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                  </div>
                </td>
              </tr>

              <tr>
                <td class="fw-bold">Add-ons</td>
                <td class="text-muted small">Extra toppings and side items</td>
                <td><span class="badge bg-secondary">Inactive</span></td>
                <td>
                  <div class="d-flex flex-wrap gap-1">
                    <button class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editCategoryModal"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn btn-xs btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</main>

<div class="modal fade" id="addMenuItemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-success">Add New Menu Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3">
        <form>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Dish Title</label>
            <input type="text" class="form-control form-control-sm" placeholder="e.g. Inihaw na Liempo Binalot" required>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label small fw-semibold">Category</label>
              <select class="form-select form-select-sm" required>
                <option value="binalot">Binalot Meals</option>
                <option value="sabaw">Soups & Main</option>
                <option value="merienda">Desserts & Drinks</option>
                <option value="addons">Add-ons</option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label small fw-semibold">Price (PHP)</label>
              <input type="number" class="form-control form-control-sm" placeholder="180" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Image</label>
            <input type="file" class="form-control form-control-sm" accept="image/*">
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Description</label>
            <textarea class="form-control form-control-sm" rows="3" placeholder="Brief description of dish..."></textarea>
          </div>

          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" checked>
            <label class="form-check-label small">Available for order</label>
          </div>

          <div class="d-flex gap-2 modal-form-row">
            <button type="submit" class="btn btn-primary-custom modal-form-btn">Save Item</button>
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
        <form>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Dish Title</label>
            <input type="text" class="form-control form-control-sm" value="Chicken Inasal Binalot">
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label small fw-semibold">Category</label>
              <select class="form-select form-select-sm">
                <option selected>Binalot Meals</option>
                <option>Soups & Main</option>
                <option>Desserts & Drinks</option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label small fw-semibold">Price (PHP)</label>
              <input type="number" class="form-control form-control-sm" value="185">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Description</label>
            <textarea class="form-control form-control-sm" rows="3">Grilled chicken wrapped in banana leaf with rice and sauce.</textarea>
          </div>

          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" checked>
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
            <input type="text" class="form-control form-control-sm" placeholder="e.g. Rice Meals, Grilled, Seafood" required>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Description</label>
            <textarea class="form-control form-control-sm" rows="2" placeholder="Brief description of this category..."></textarea>
          </div>

          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" checked>
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
          <div class="mb-3">
            <label class="form-label small fw-semibold">Category Name</label>
            <input type="text" class="form-control form-control-sm" value="Binalot Meals" required>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold">Description</label>
            <textarea class="form-control form-control-sm" rows="2">Traditional Filipino binalot wrapped in banana leaf</textarea>
          </div>

          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" checked>
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