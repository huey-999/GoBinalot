<?php
include '../includes/config.php';
include '../includes/auth.php';
requireLogin('../login.php');

$userRole = $_SESSION['user_data']['role'] ?? '';
$userId = (int) ($_SESSION['user_data']['id'] ?? 0);
if (!in_array($userRole, ['rider', 'admin'], true)) {
    header('Location: ../login.php');
    exit;
}

include '../includes/actions.php';
ensureDeliveryTables($conn);

$myDeliveries = [];
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
            WHERE d.delivery_person = :rider_id
            ORDER BY d.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':rider_id' => $userId]);
    $myDeliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    $myDeliveries = [];
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

$statAssigned = 0;
$statPickedUp = 0;
$statOutForDelivery = 0;
$statDeliveredToday = 0;
$today = date('Y-m-d');
foreach ($myDeliveries as $d) {
    $status = $d['delivery_status'] ?: 'pending_dispatch';
    if ($status === 'assigned') $statAssigned++;
    if ($status === 'picked_up') $statPickedUp++;
    if ($status === 'out_for_delivery') $statOutForDelivery++;
    if ($status === 'delivered' && !empty($d['delivered_at']) && substr((string) $d['delivered_at'], 0, 10) === $today) {
        $statDeliveredToday++;
    }
}

include '../includes/admin_header.php';
?>
<main class="main-content">
  <div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
      <h1 class="dashboard-heading">My Deliveries</h1>
      <p class="page-subtitle">View and update deliveries assigned to you.</p>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card p-3">
        <span class="text-muted small fw-semibold">Assigned</span>
        <h4 class="fw-bold text-dark mt-1 mb-0"><?= $statAssigned ?></h4>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card p-3">
        <span class="text-muted small fw-semibold">Picked Up</span>
        <h4 class="fw-bold text-dark mt-1 mb-0"><?= $statPickedUp ?></h4>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="stat-card p-3">
        <span class="text-muted small fw-semibold">Out for Delivery</span>
        <h4 class="fw-bold text-dark mt-1 mb-0"><?= $statOutForDelivery ?></h4>
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
      <h4 class="fw-bold text-success mb-0">Assigned Deliveries</h4>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle table-sm mb-0 datatable-table">
        <thead class="table-light">
          <tr>
            <th>Order</th>
            <th>Customer</th>
            <th>Address</th>
            <th>Contact</th>
            <th>Status</th>
            <th>ETA</th>
            <th>Notes</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($myDeliveries)) : ?>
            <?php foreach ($myDeliveries as $d) : ?>
              <?php
                $status = $d['delivery_status'] ?: 'pending_dispatch';
                $badgeClass = $deliveryStatusBadge[$status] ?? 'bg-secondary';
                $label = $deliveryStatusLabels[$status] ?? $status;
                $orderNumber = !empty($d['order_number']) ? $d['order_number'] : ('#GB-' . str_pad((string) $d['order_id'], 6, '0', STR_PAD_LEFT));
                $eta = !empty($d['eta']) ? str_replace('T', ' ', substr((string) $d['eta'], 0, 16)) : '—';
              ?>
              <tr>
                <td class="fw-bold"><?= htmlspecialchars($orderNumber) ?></td>
                <td><?= htmlspecialchars(trim(($d['customer_first_name'] ?? '') . ' ' . ($d['customer_last_name'] ?? ''))) ?></td>
                <td><?= htmlspecialchars($d['delivery_address'] ?? '') ?></td>
                <td><?= htmlspecialchars($d['customer_phone'] ?? '') ?></td>
                <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($label) ?></span></td>
                <td><?= htmlspecialchars($eta) ?></td>
                <td><?= htmlspecialchars($d['notes'] ?? '') ?></td>
                <td>
                  <div class="d-flex flex-wrap gap-1">
                    <button class="btn btn-xs btn-outline-secondary view-delivery" data-delivery-id="<?= (int) $d['id'] ?>"><i class="fa-solid fa-eye"></i></button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
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
              <div class="d-flex justify-content-between small mb-1"><span>Notes</span><span id="ddNotesText">—</span></div>
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
         <div class="row g-3 mt-2">
           <div class="col-12">
<div class="border rounded p-3" id="ddStatusSection" style="display:none">
                <div class="small text-muted mb-2">Update Status</div>
                <div class="d-flex gap-2 flex-wrap mb-2" id="riderActionButtons">
                  <button type="button" class="btn btn-success btn-sm" id="btnMarkPickedUp" style="display:none;">Mark Picked Up</button>
                  <button type="button" class="btn btn-primary btn-sm" id="btnStartDelivery" style="display:none;">Start Delivery</button>
                  <button type="button" class="btn btn-success btn-sm" id="btnMarkDelivered" style="display:none;">Mark Delivered</button>
                  <button type="button" class="btn btn-outline-danger btn-sm" id="btnCancelDelivery" style="display:none;">Cancel Delivery</button>
                </div>
                <div class="small text-muted mb-2" id="ddStatusLockMessage"></div>
              </div>
           </div>
         </div>
      </div>
    </div>
  </div>
</div>

<!-- Cancel delivery modal -->
<div class="modal fade" id="cancelDeliveryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-danger">Cancel Delivery</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3">
        <p class="small text-muted mb-2">Confirm that this delivery should be cancelled.</p>
        <label class="form-label small text-muted">Failure Reason</label>
        <textarea class="form-control form-control-sm" rows="2" id="cancelFailureReason" placeholder="Reason for cancelling this delivery"></textarea>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Keep Delivery</button>
        <button type="button" class="btn btn-danger btn-sm" id="confirmCancelDelivery">Confirm Cancel</button>
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

        window.currentDeliveryId = deliveryId;
        const currentStatus = d.delivery_status || 'assigned';
        const deliveryTransitions = {
          pending_dispatch: [],
          assigned: ['picked_up', 'cancelled'],
          picked_up: ['out_for_delivery', 'cancelled'],
          out_for_delivery: ['delivered', 'cancelled'],
          delivered: [],
          cancelled: []
        };
        const locked = currentStatus === 'delivered' || currentStatus === 'cancelled';

        $('#btnMarkPickedUp, #btnStartDelivery, #btnMarkDelivered, #btnCancelDelivery').hide();
        if (!locked) {
          if (currentStatus === 'assigned') {
            $('#btnMarkPickedUp, #btnCancelDelivery').show();
          } else if (currentStatus === 'picked_up') {
            $('#btnStartDelivery, #btnCancelDelivery').show();
          } else if (currentStatus === 'out_for_delivery') {
            $('#btnMarkDelivered, #btnCancelDelivery').show();
          }
        }

        const hasAnyRiderAction = $('#riderActionButtons .btn:visible').length > 0;
        $('#ddStatusSection').toggle(hasAnyRiderAction);

        // Display notes as read-only text; store original for reference
        const notesText = d.notes ? String(d.notes) : '—';
        $('#ddNotesText').text(notesText);
        $('#ddNotes').hide();

        // Cancel delivery: open confirmation modal with stored failure reason
        const storedFailure = d.failure_reason || '';
        $('#cancelFailureReason').val(storedFailure);
        window.cancelDeliveryId = deliveryId;
        window.cancelCurrentStatus = currentStatus;

        new bootstrap.Modal(document.getElementById('deliveryDetailsModal')).show();
    });

    function updateDeliveryStatus(targetStatus) {
      postAction({
        form_type: 'update_delivery_status',
        delivery_id: window.currentDeliveryId,
        delivery_status: targetStatus,
        failure_reason: '',
        notes: ''
      }, function (response) {
        if (response.success) {
          window.location.reload();
        } else {
          alert(response.message || 'Failed to update delivery status.');
        }
      });
    }

    $('#btnMarkPickedUp').on('click', function () { updateDeliveryStatus('picked_up'); });
    $('#btnStartDelivery').on('click', function () { updateDeliveryStatus('out_for_delivery'); });
    $('#btnMarkDelivered').on('click', function () { updateDeliveryStatus('delivered'); });

    // Cancel delivery: open modal instead of direct update
    $('#btnCancelDelivery').on('click', function () {
      new bootstrap.Modal(document.getElementById('cancelDeliveryModal')).show();
    });

    // Confirm cancel from modal
    $('#confirmCancelDelivery').on('click', function () {
      const reason = $('#cancelFailureReason').val().trim();
      postAction({
        form_type: 'update_delivery_status',
        delivery_id: window.currentDeliveryId,
        delivery_status: 'cancelled',
        failure_reason: reason,
        notes: ''
      }, function (response) {
        if (response.success) {
          bootstrap.Modal.getInstance(document.getElementById('cancelDeliveryModal')).hide();
          window.location.reload();
        } else {
          alert(response.message || 'Failed to cancel delivery.');
        }
      });
    });

    });

  });
</script>
