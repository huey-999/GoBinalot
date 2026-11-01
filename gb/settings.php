<?php include '../includes/admin_header.php'; ?>
<?php $isCustomer = ($_SESSION['user_data']['role'] ?? '') === 'customer'; ?>
<?php
$customerAddresses = [];
if ($isCustomer) {
  $userId = $_SESSION['user_data']['id'] ?? 0;

  if ($userId) {
    $addressStmt = $conn->prepare("SELECT id, type, address, contact_number, is_default FROM addresses WHERE user_id = :user_id ORDER BY is_default DESC, id DESC");
    $addressStmt->execute([':user_id' => $userId]);
    $customerAddresses = $addressStmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
?>

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
              <button class="btn btn-success btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                <i class="fa-solid fa-plus me-1"></i> Add Address
              </button>
            </div>
            <div id="addressList" class="address-list">
              <?php if (empty($customerAddresses)) : ?>
                <div class="empty-state text-center text-muted py-4">No saved addresses yet.</div>
              <?php else : ?>
                <?php foreach ($customerAddresses as $address) : ?>
                  <?php $isDefault = (int) ($address['is_default'] ?? 0) === 1; ?>
                  <div class="address-item <?php echo $isDefault ? 'default' : ''; ?>">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                      <span class="badge <?php echo $isDefault ? 'bg-success-subtle text-success' : 'bg-light text-secondary border'; ?>">
                        <?php echo $isDefault ? 'Default' : htmlspecialchars($address['type'] ?? 'Home'); ?>
                      </span>
                    </div>
                    <h6 class="address-title"><?php echo htmlspecialchars($address['type'] ?? 'Home'); ?></h6>
                    <p class="address-copy"><?php echo nl2br(htmlspecialchars($address['address'] ?? '')); ?></p>
                    <p class="address-copy small mb-2"><strong>Phone:</strong> <?php echo htmlspecialchars($address['contact_number'] ?? ''); ?></p>
                    <div class="address-buttons">
                      <button class="btn btn-edit-address" type="button"
                        data-address-id="<?php echo (int) $address['id']; ?>"
                        data-address-type="<?php echo htmlspecialchars($address['type'] ?? 'Home'); ?>"
                        data-address="<?php echo htmlspecialchars($address['address'] ?? ''); ?>"
                        data-phone="<?php echo htmlspecialchars($address['contact_number'] ?? ''); ?>"
                        data-is-default="<?php echo $isDefault ? 1 : 0; ?>"
                        title="Edit">
                        <i class="fa-solid fa-pen"></i>
                      </button>
                      <button class="btn btn-delete-address" type="button" data-address-id="<?php echo (int) $address['id']; ?>" title="Delete">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
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
          <form id="addAddressForm" class="settings-form">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label" for="addressType">Address Type</label>
                <select class="form-select" id="addressType" name="type">
                  <option selected>Home</option>
                  <option>Work</option>
                  <option>Family</option>
                  <option>Office</option>
                  <option>Other</option>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label" for="completeAddress">Address</label>
                <textarea class="form-control" id="completeAddress" name="address" rows="4" placeholder="Street, building name, unit number, barangay, etc."></textarea>
              </div>

              <div class="col-12">
                <label class="form-label" for="phone">Contact Number</label>
                <input class="form-control" id="phone" name="contact_number" type="tel" placeholder="e.g. 0917 123 4567" />
              </div>

              <div class="col-12">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <input class="form-check-input" type="checkbox" id="isDefault" name="is_default" role="switch" />
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
          <form id="editAddressForm" class="settings-form">
            <input type="hidden" name="address_id" value="">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label" for="editAddressType">Address Type</label>
                <select class="form-select" id="editAddressType" name="type">
                  <option>Home</option>
                  <option>Work</option>
                  <option>Family</option>
                  <option>Office</option>
                  <option>Other</option>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label" for="editCompleteAddress">Address</label>
                <textarea class="form-control" id="editCompleteAddress" name="address" rows="4" placeholder="Street, building name, unit number, barangay, etc."></textarea>
              </div>

              <div class="col-12">
                <label class="form-label" for="editPhone">Contact Number</label>
                <input class="form-control" id="editPhone" name="contact_number" type="tel" placeholder="e.g. 0917 123 4567" />
              </div>

              <div class="col-12">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <input class="form-check-input" type="checkbox" id="editIsDefault" name="is_default" role="switch" />
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
