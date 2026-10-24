<?php include '../includes/admin_header.php'; ?>
<?php $isCustomer = ($_SESSION['user_data']['role'] ?? '') === 'customer'; ?>

<main class="main-content">
  <div class="tab-content">
    <div class="tab-pane fade show active" id="settings-tab">

      <div class="page-header d-flex justify-content-between align-items-center">
        <div>
          <h1 class="dashboard-heading">Settings</h1>
          <?php if($isCustomer) : ?>
                <p class="page-subtitle">Manage your delivery addresses.</p>
            <?php else : ?>
                <p class="page-subtitle">Manage customer delivery addresses.</p>
            <?php endif; ?>
        </div>
      </div>

        <?php if ($isCustomer) : ?>
        <div class="col-12">
          <div class="table-card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h4 class="fw-bold text-success mb-0">Saved Addresses</h4>
              <button class="btn btn-primary-custom btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addAddressModal">Add Address</button>
            </div>
              <div class="address-list">
                <div class="address-item default">
                  <div class="d-flex justify-content-between align-items-center gap-3">
                    <span class="badge bg-success-subtle text-success">Default</span>
                  </div>
                  <h6 class="address-title">Home Address</h6>
                  <p class="address-copy">
                    123 Harmonya St., Brgy. San Isidro<br>
                    Baguio City, Benguet 2600<br>
                    Philippines
                  </p>
                  <div class="address-buttons">
                    <button class="btn btn-outline-success btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#editAddressModal">Edit</button>
                    <button class="btn btn-outline-secondary btn-sm" type="button">Delete</button>
                  </div>
                </div>

                <div class="address-item">
                  <div class="d-flex justify-content-between align-items-center gap-3">
                    <span class="badge bg-light text-secondary border">Office</span>
                  </div>
                  <h6 class="address-title">Work Address</h6>
                  <p class="address-copy">
                    45 Ledesma Building, Session Road<br>
                    Baguio City, Benguet 2600<br>
                    Philippines
                  </p>
                  <div class="address-buttons">
                    <button class="btn btn-outline-success btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#editAddressModal">Edit</button>
                    <button class="btn btn-outline-secondary btn-sm" type="button">Delete</button>
                  </div>
                </div>

                <div class="address-item">
                  <div class="d-flex justify-content-between align-items-center gap-3">
                    <span class="badge bg-light text-secondary border">Family</span>
                  </div>
                  <h6 class="address-title">Parent's House</h6>
                  <p class="address-copy">
                    9 Bonifacio Road, Purok 4<br>
                    La Trinidad, Benguet 2601<br>
                    Philippines
                  </p>
                  <div class="address-buttons">
                    <button class="btn btn-outline-success btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#editAddressModal">Edit</button>
                    <button class="btn btn-outline-secondary btn-sm" type="button">Delete</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ADD NEW ADDRESS MODAL -->
  <div class="modal fade" id="addAddressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-sm">
        <div class="modal-header border-0 pb-2">
          <h5 class="modal-title fw-bold text-success">Add New Address</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body p-3">
          <form class="settings-form">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label" for="addressType">Address Type</label>
                <select class="form-select" id="addressType">
                  <option selected>Home</option>
                  <option>Work</option>
                  <option>Family</option>
                  <option>Office</option>
                  <option>Other</option>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label" for="completeAddress">Complete Address</label>
                <textarea class="form-control" id="completeAddress" rows="4" placeholder="Street, building name, unit number, barangay, etc."></textarea>
              </div>

              <div class="col-12">
                <label class="form-label" for="phone">Phone Number</label>
                <input class="form-control" id="phone" type="tel" placeholder="e.g. 0917 123 4567" />
              </div>

              <div class="col-12">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <input class="form-check-input" type="checkbox" id="isDefault" role="switch" />
                  <label class="form-check-label mb-0" for="isDefault">Set as default address</label>
                </div>
              </div>
            </div>

            <div class="d-flex gap-2 mt-3">
              <button type="submit" class="btn btn-primary-custom modal-form-btn">Save Address</button>
              <button type="button" class="btn btn-outline-secondary modal-form-btn" data-bs-dismiss="modal">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- EDIT ADDRESS MODAL -->
  <div class="modal fade" id="editAddressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-sm">
        <div class="modal-header border-0 pb-2">
          <h5 class="modal-title fw-bold text-success">Edit Address</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body p-3">
          <form class="settings-form">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label" for="editAddressType">Address Type</label>
                <select class="form-select" id="editAddressType">
                  <option selected>Home</option>
                  <option>Work</option>
                  <option>Family</option>
                  <option>Office</option>
                  <option>Other</option>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label" for="editCompleteAddress">Complete Address</label>
                <textarea class="form-control" id="editCompleteAddress" rows="4" placeholder="Street, building name, unit number, barangay, etc."></textarea>
              </div>

              <div class="col-12">
                <label class="form-label" for="editPhone">Phone Number</label>
                <input class="form-control" id="editPhone" type="tel" placeholder="e.g. 0917 123 4567" />
              </div>

              <div class="col-12">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <input class="form-check-input" type="checkbox" id="editIsDefault" role="switch" />
                  <label class="form-check-label mb-0" for="editIsDefault">Set as default address</label>
                </div>
              </div>
            </div>

            <div class="d-flex gap-2 mt-3">
              <button type="submit" class="btn btn-primary-custom modal-form-btn">Save Changes</button>
              <button type="button" class="btn btn-outline-secondary modal-form-btn" data-bs-dismiss="modal">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include '../includes/admin_footer.php'; ?>
