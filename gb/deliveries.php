<?php
include '../includes/config.php';
include '../includes/auth.php';
requireLogin('../login.php');
if (($_SESSION['user_data']['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}
include '../includes/admin_header.php';
?>
<?php
include '../includes/actions.php';

ensureDeliveryTables($conn);

$allDeliveries = [];
$allRiders = [];

try {
    $sql = "SELECT d.id, d.order_id, d.delivery_person, d.delivery_status, d.dispatched_at, d.picked_up_at,
                   d.out_for_delivery_at, d.delivered_at, d.eta, d.failure_reason, d.notes, d.created_at,
                   o.order_number, o.order_type, o.order_status, o.total_amount,
                   u.first_name AS customer_first_name, u.last_name AS customer_last_name,
                   a.contact_number AS customer_phone, a.address AS delivery_address,
                   r.first_name AS rider_first_name, r.last_name AS rider_last_name, r.phone AS rider_phone
            FROM deliveries d
            JOIN orders o ON o.id = d.order_id
            JOIN users u ON u.id = o.user_id
            LEFT JOIN users r ON r.id = d.delivery_person
            LEFT JOIN addresses a ON a.id = o.address_id
            ORDER BY d.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $allDeliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    $allDeliveries = [];
}

try {
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, phone, is_active, created_at FROM users WHERE role = 'rider' ORDER BY first_name, last_name");
    $stmt->execute();
    $allRiders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    $allRiders = [];
}

$statActive = 0;
$statAssigned = 0;
$statPending = 0;
$statDeliveredToday = 0;
$today = date('Y-m-d');
foreach ($allDeliveries as $d) {
    $status = $d['delivery_status'] ?: 'pending_dispatch';
    if ($status !== 'delivered' && $status !== 'cancelled') {
        $statActive++;
    }
    if ($status === 'assigned') {
        $statAssigned++;
    }
    if ($status === 'pending_dispatch') {
        $statPending++;
    }
    if ($status === 'delivered' && !empty($d['delivered_at']) && substr((string) $d['delivered_at'], 0, 10) === $today) {
        $statDeliveredToday++;
    }
}

$deliveryStatusLabels = [
    'pending_dispatch' => 'Pending Dispatch',
    'assigned' => 'Assigned',
    'picked_up' => 'Picked Up',
    'out_for_delivery' => 'Out for Delivery',
    'delivered' => 'Delivered',
    'cancelled' => 'Cancelled',
];
$deliveryStatusBadge = [
    'pending_dispatch' => 'bg-secondary',
    'assigned' => 'bg-success',
    'picked_up' => 'bg-info text-dark',
    'out_for_delivery' => 'bg-warning text-dark',
    'delivered' => 'bg-success',
    'cancelled' => 'bg-danger',
];
?>

<main class="main-content">
  <div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
      <h1 class="dashboard-heading">Delivery Management</h1>
      <p class="page-subtitle">Track assignments, dispatch times, and delivery progress.</p>
    </div>
  </div>

  <ul class="nav nav-tabs mb-3" id="deliveryTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#deliveriesTab" type="button">Deliveries</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" data-bs-toggle="tab" data-bs-target="#ridersTab" type="button">Riders</button>
    </li>
  </ul>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="deliveriesTab">
      <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <span class="text-muted small fw-semibold">Active Deliveries</span>
            <h4 class="fw-bold text-dark mt-1 mb-0"><?= $statActive ?></h4>
          </div>
        </div>
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <span class="text-muted small fw-semibold">Assigned</span>
            <h4 class="fw-bold text-dark mt-1 mb-0"><?= $statAssigned ?></h4>
          </div>
        </div>
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <span class="text-muted small fw-semibold">Pending Dispatch</span>
            <h4 class="fw-bold text-dark mt-1 mb-0"><?= $statPending ?></h4>
          </div>
        </div>
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <span class="text-muted small fw-semibold">Delivered Today</span>
            <h4 class="fw-bold text-dark mt-1 mb-0"><?= $statDeliveredToday ?></h4>
          </div>
        </div>
      </div>

      <div class="table-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="fw-bold text-success mb-0">Delivery Orders</h4>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle table-sm mb-0 datatable-table">
            <thead class="table-light">
              <tr>
                <th>Order</th>
                <th>Customer</th>
                <th>Address</th>
                <th>Contact</th>
                <th>Rider</th>
                <th>Dispatch</th>
                <th>ETA</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($allDeliveries)) : ?>
                <?php foreach ($allDeliveries as $d) : ?>
                  <?php
                    $status = $d['delivery_status'] ?: 'pending_dispatch';
                    $badgeClass = $deliveryStatusBadge[$status] ?? 'bg-secondary';
                    $label = $deliveryStatusLabels[$status] ?? $status;
                    $riderName = !empty($d['rider_first_name']) ? trim($d['rider_first_name'] . ' ' . $d['rider_last_name']) : 'Unassigned';
                    $dispatch = !empty($d['dispatched_at']) ? substr((string) $d['dispatched_at'], 11, 5) : '—';
                    $eta = !empty($d['eta']) ? str_replace('T', ' ', substr((string) $d['eta'], 0, 16)) : '—';
                    $orderNumber = !empty($d['order_number']) ? $d['order_number'] : ('#GB-' . str_pad((string) $d['order_id'], 6, '0', STR_PAD_LEFT));
                  ?>
                  <tr>
                    <td class="fw-bold"><?= htmlspecialchars($orderNumber) ?></td>
                    <td><?= htmlspecialchars(trim(($d['customer_first_name'] ?? '') . ' ' . ($d['customer_last_name'] ?? ''))) ?></td>
                    <td><?= htmlspecialchars($d['delivery_address'] ?? '') ?></td>
                    <td><?= htmlspecialchars($d['customer_phone'] ?? '') ?></td>
                    <td><?= htmlspecialchars($riderName) ?></td>
                    <td><?= htmlspecialchars($dispatch) ?></td>
                    <td><?= htmlspecialchars($eta) ?></td>
                    <td>
<span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($label) ?></span>
</td>
                    <td>
                      <div class="d-flex flex-wrap gap-1">
                        <button class="btn btn-xs btn-outline-secondary view-delivery" data-delivery-id="<?= (int) $d['id'] ?>"><i class="fa-solid fa-eye"></i></button>
                        <?php if ($status === 'pending_dispatch') : ?>
                        <button class="btn btn-xs btn-success assign-delivery" data-order-id="<?= (int) $d['order_id'] ?>" data-delivery-id="<?= (int) $d['id'] ?>"><i class="fa-solid fa-user-plus"></i></button>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="tab-pane fade" id="ridersTab">
      <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addRiderModal">
          <i class="fa-solid fa-plus me-1"></i>Add Rider
        </button>
      </div>
      <div class="table-card p-3">
        <div class="table-responsive">
          <table class="table table-hover align-middle table-sm mb-0 datatable-table">
            <thead class="table-light">
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($allRiders)) : ?>
                <?php foreach ($allRiders as $r) : ?>
                  <?php
                    $isActive = (int) ($r['is_active'] ?? 0) === 1;
                    $status = $isActive ? 'Active' : 'Inactive';
                    $badge = $isActive ? 'bg-success' : 'bg-secondary';
                  ?>
                  <tr>
                    <td><?= htmlspecialchars(trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''))) ?></td>
                    <td><?= htmlspecialchars($r['email'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['phone'] ?? '') ?></td>
                    <td><span class="badge <?= $badge ?>"><?= $status ?></span></td>
                    <td>
                      <button class="btn btn-xs btn-outline-primary edit-rider" data-rider-id="<?= (int) $r['id'] ?>"><i class="fa-solid fa-pen"></i></button>
                      <button class="btn btn-xs <?= $isActive ? 'btn-outline-danger' : 'btn-outline-success' ?> toggle-rider" data-rider-id="<?= (int) $r['id'] ?>" data-active="<?= $isActive ? 1 : 0 ?>"><?= $isActive ? 'Deactivate' : 'Activate' ?></button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- Delivery details modal -->
<div class="modal fade" id="deliveryDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-success">Delivery Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3">
        <div class="row g-3">
          <div class="col-md-5">
            <div class="border rounded p-3 h-100">
              <div class="small text-muted mb-2">Customer</div>
              <div class="fw-bold" id="ddCustomerName"></div>
              <div class="small text-muted mt-2" id="ddCustomerPhone"></div>
              <div class="small text-muted mt-2" id="ddCustomerAddress"></div>
            </div>
          </div>
          <div class="col-md-7">
            <div class="border rounded p-3 h-100">
              <div class="small text-muted mb-2">Delivery Summary</div>
              <div class="d-flex justify-content-between small mb-1"><span>Rider</span><span class="fw-semibold" id="ddRider"></span></div>
              <div class="d-flex justify-content-between small mb-1"><span>Dispatched</span><span id="ddDispatch"></span></div>
              <div class="d-flex justify-content-between small mb-1"><span>ETA</span><span id="ddEta"></span></div>
              <div class="d-flex justify-content-between small mb-1"><span>Delivered</span><span id="ddDeliveryDate"></span></div>
<div class="d-flex justify-content-between small mb-1"><span>Delivery Status</span><span id="ddStatus"></span></div>
              <div class="d-flex justify-content-between small mb-1"><span>Order Status</span><span id="ddOrderStatus"></span></div>
              <div class="d-flex justify-content-between small mb-1"><span>Total</span><span class="fw-bold" id="ddTotal"></span></div>
            </div>
          </div>
        </div>
        <div class="mt-3 border rounded p-3">
          <div class="small text-muted mb-2">Ordered Items</div>
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead><tr><th>Item</th><th>Qty</th><th>Total</th></tr></thead>
              <tbody id="ddItems"><tr><td colspan="3" class="text-center text-muted small">Loading...</td></tr></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Assign rider modal -->
<div class="modal fade" id="assignRiderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-success">Assign Rider</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3">
        <input type="hidden" id="assignOrderId">
        <input type="hidden" id="assignDeliveryId">
        <label class="form-label small text-muted">Select Rider</label>
        <select class="form-select form-select-sm mb-3" id="assignRiderSelect">
          <option value="">Choose rider</option>
          <?php foreach ($allRiders as $r) : ?>
            <?php if ((int) ($r['is_active'] ?? 0) === 1) : ?>
              <option value="<?= (int) $r['id'] ?>"><?= htmlspecialchars(trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''))) ?></option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
        <label class="form-label small text-muted">ETA</label>
        <input type="datetime-local" class="form-control form-control-sm mb-3" id="assignEta">
        <label class="form-label small text-muted">Notes</label>
        <textarea class="form-control form-control-sm" rows="3" id="assignNotes"></textarea>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success btn-sm" id="saveAssignBtn">Assign</button>
      </div>
    </div>
  </div>
</div>

<!-- Add rider modal -->
<div class="modal fade" id="addRiderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-success">Add Rider</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3">
        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label small text-muted">First Name</label>
            <input type="text" class="form-control form-control-sm" id="addRiderFirstName">
          </div>
          <div class="col-md-6">
            <label class="form-label small text-muted">Last Name</label>
            <input type="text" class="form-control form-control-sm" id="addRiderLastName">
          </div>
          <div class="col-md-6">
            <label class="form-label small text-muted">Email</label>
            <input type="email" class="form-control form-control-sm" id="addRiderEmail">
          </div>
          <div class="col-md-6">
            <label class="form-label small text-muted">Phone</label>
            <input type="text" class="form-control form-control-sm" id="addRiderPhone">
          </div>
          <div class="col-12">
            <label class="form-label small text-muted">Password</label>
            <input type="password" class="form-control form-control-sm" id="addRiderPassword">
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success btn-sm" id="saveAddRiderBtn">Create</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit rider modal -->
<div class="modal fade" id="editRiderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-success">Edit Rider</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3">
        <input type="hidden" id="editRiderId">
        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label small text-muted">First Name</label>
            <input type="text" class="form-control form-control-sm" id="editRiderFirstName">
          </div>
          <div class="col-md-6">
            <label class="form-label small text-muted">Last Name</label>
            <input type="text" class="form-control form-control-sm" id="editRiderLastName">
          </div>
          <div class="col-md-6">
            <label class="form-label small text-muted">Email</label>
            <input type="email" class="form-control form-control-sm" id="editRiderEmail">
          </div>
          <div class="col-md-6">
            <label class="form-label small text-muted">Phone</label>
            <input type="text" class="form-control form-control-sm" id="editRiderPhone">
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success btn-sm" id="saveEditRiderBtn">Save</button>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/admin_footer.php'; ?>

<script>
  function escapeHtml(text) {
    return String(text == null ? '' : text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }
  function money(value) {
    return '₱' + parseFloat(value || 0).toFixed(2);
  }

  const deliveryStatusLabels = {
    pending_dispatch: 'Pending Dispatch',
    assigned: 'Assigned',
    picked_up: 'Picked Up',
    out_for_delivery: 'Out for Delivery',
    delivered: 'Delivered',
    cancelled: 'Cancelled'
  };
  const deliveryStatusBadge = {
    pending_dispatch: 'bg-secondary',
    assigned: 'bg-success',
    picked_up: 'bg-info text-dark',
    out_for_delivery: 'bg-info text-dark',
    delivered: 'bg-success',
    cancelled: 'bg-danger'
  };
  const orderStatusBadgeClass = {
    'Pending': 'bg-warning text-dark',
    'Paid': 'bg-success',
    'Preparing': 'bg-warning text-dark',
    'Out for Delivery': 'bg-info text-dark',
    'Completed': 'bg-success',
    'Cancelled': 'bg-danger'
  };

  const ridersCache = <?= json_encode($allRiders) ?>;

  function postAction(data, callback) {
    $.ajax({
      url: '../includes/actions.php',
      type: 'POST',
      dataType: 'json',
      data: data,
      success: function (response) { callback(response || {}); },
      error: function () { callback({ success: false, message: 'Request failed.' }); }
    });
  }

  $(document).ready(function () {
    $(document).on('click', '.view-delivery', function () {
      const deliveryId = $(this).data('delivery-id');
      postAction({ form_type: 'get_delivery', delivery_id: deliveryId }, function (response) {
        if (!response.success || !response.delivery) return;
        const d = response.delivery;
        const items = response.items || [];
        $('#deliveryDetailsModal .modal-title').text('Delivery Details #' + (d.order_number || d.order_id));
        $('#ddCustomerName').text(((d.customer_first_name || '') + ' ' + (d.customer_last_name || '')).trim());
        $('#ddCustomerPhone').text(d.customer_phone || '');
        $('#ddCustomerAddress').text(d.delivery_address || '');
        $('#ddRider').text(d.rider_first_name ? ((d.rider_first_name || '') + ' ' + (d.rider_last_name || '')).trim() : 'Unassigned');
        $('#ddDispatch').text(d.dispatched_at ? String(d.dispatched_at).slice(0, 16).replace('T', ' ') : '—');
        $('#ddEta').text(d.eta ? String(d.eta).slice(0, 16).replace('T', ' ') : '—');
        $('#ddDeliveryDate').text(d.delivered_at ? String(d.delivered_at).slice(0, 16).replace('T', ' ') : '—');
        $('#ddStatus').html(`<span class="badge ${deliveryStatusBadge[d.delivery_status] || 'bg-secondary'}">${escapeHtml(deliveryStatusLabels[d.delivery_status] || d.delivery_status)}</span>`);
        $('#ddOrderStatus').html(`<span class="badge ${orderStatusBadgeClass[d.order_status] || 'bg-secondary'}">${escapeHtml(d.order_status || '—')}</span>`);
        $('#ddTotal').text(money(d.total_amount));
        $('#ddItems').html(items.length ? items.map(function (item) {
          const qty = parseInt(item.quantity || 1, 10);
          const total = parseFloat(item.subtotal || (parseFloat(item.unit_price || 0) * qty));
          return `<tr><td>${escapeHtml(item.item_name)}</td><td>${qty}</td><td>${money(total)}</td></tr>`;
        }).join('') : '<tr><td colspan="3" class="text-center text-muted small">No items found.</td></tr>');
      });
      new bootstrap.Modal(document.getElementById('deliveryDetailsModal')).show();
    });

    $(document).on('click', '.assign-delivery', function () {
      $('#assignOrderId').val($(this).data('order-id'));
      $('#assignDeliveryId').val($(this).data('delivery-id'));
      $('#assignEta').val('');
      $('#assignNotes').val('');
      new bootstrap.Modal(document.getElementById('assignRiderModal')).show();
    });

    $('#saveAssignBtn').on('click', function () {
      postAction({
        form_type: 'assign_delivery',
        order_id: $('#assignOrderId').val(),
        rider_id: $('#assignRiderSelect').val(),
        eta: $('#assignEta').val(),
        notes: $('#assignNotes').val()
      }, function (response) {
        if (response.success) {
          bootstrap.Modal.getInstance(document.getElementById('assignRiderModal')).hide();
          window.location.reload();
        } else {
          alert(response.message || 'Failed to assign rider.');
        }
      });
    });

    $('#saveAddRiderBtn').on('click', function () {
      postAction({
        form_type: 'add_rider',
        first_name: $('#addRiderFirstName').val(),
        last_name: $('#addRiderLastName').val(),
        email: $('#addRiderEmail').val(),
        phone: $('#addRiderPhone').val(),
        password: $('#addRiderPassword').val()
      }, function (response) {
        if (response.success) {
          bootstrap.Modal.getInstance(document.getElementById('addRiderModal')).hide();
          window.location.reload();
        } else {
          alert(response.message || 'Failed to create rider.');
        }
      });
    });

    $(document).on('click', '.edit-rider', function () {
      const riderId = $(this).data('rider-id');
      const rider = ridersCache.find(function (r) { return String(r.id) === String(riderId); });
      if (!rider) return;
      $('#editRiderId').val(rider.id);
      $('#editRiderFirstName').val(rider.first_name || '');
      $('#editRiderLastName').val(rider.last_name || '');
      $('#editRiderEmail').val(rider.email || '');
      $('#editRiderPhone').val(rider.phone || '');
      new bootstrap.Modal(document.getElementById('editRiderModal')).show();
    });

    $('#saveEditRiderBtn').on('click', function () {
      postAction({
        form_type: 'update_rider',
        rider_id: $('#editRiderId').val(),
        first_name: $('#editRiderFirstName').val(),
        last_name: $('#editRiderLastName').val(),
        email: $('#editRiderEmail').val(),
        phone: $('#editRiderPhone').val()
      }, function (response) {
        if (response.success) {
          bootstrap.Modal.getInstance(document.getElementById('editRiderModal')).hide();
          window.location.reload();
        } else {
          alert(response.message || 'Failed to update rider.');
        }
      });
    });

    $(document).on('click', '.toggle-rider', function () {
      const riderId = $(this).data('rider-id');
      const current = parseInt($(this).data('active'), 10) === 1 ? 1 : 0;
      postAction({
        form_type: 'toggle_rider',
        rider_id: riderId,
        is_active: current === 1 ? 0 : 1
      }, function (response) {
        if (response.success) {
          window.location.reload();
        } else {
          alert(response.message || 'Failed to update rider status.');
        }
      });
    });
  });
</script>
