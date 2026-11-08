<?php include '../includes/admin_header.php'; ?>
<?php
$allOrders = [];
try {
  $stmt = $conn->prepare("
    SELECT o.id, o.order_number, o.subtotal, o.delivery_fee, o.total_amount, o.order_status, o.created_at, o.order_type,
           u.first_name, u.last_name, u.email,
           a.contact_number, a.address,
           GROUP_CONCAT(CONCAT(oi.quantity, 'x ', oi.item_name) SEPARATOR ', ') as items_summary,
           pay.id AS payment_id, pay.method AS payment_method, pay.amount AS payment_amount,
           pay.reference_number AS payment_reference, pay.receipt AS payment_receipt,
           pay.payment_status AS payment_status, pay.verified_by AS payment_verified_by,
           pay.verified_at AS payment_verified_at
    FROM orders o
    JOIN users u ON u.id = o.user_id
    LEFT JOIN addresses a ON a.id = o.address_id
    LEFT JOIN order_items oi ON oi.order_id = o.id
    LEFT JOIN (
      SELECT p1.* FROM payments p1
      INNER JOIN (SELECT order_id, MAX(id) AS max_id FROM payments GROUP BY order_id) p2
        ON p1.id = p2.max_id
    ) pay ON pay.order_id = o.id
    GROUP BY o.id
    ORDER BY o.created_at DESC
  ");
  $stmt->execute();
  $allOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
  $allOrders = [];
}

$statusBadgeClass = [
  'Pending' => 'bg-warning text-dark',
  'Paid' => 'bg-success',
  'Preparing' => 'bg-warning text-dark',
  'Out for Delivery' => 'bg-info text-dark',
  'Completed' => 'bg-success',
  'Cancelled' => 'bg-danger',
];

$orderStats = ['total' => 0, 'pending' => 0, 'preparing' => 0, 'completed' => 0];
try {
  $orderStats['total'] = (int) $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
  $orderStats['pending'] = (int) $conn->query("SELECT COUNT(*) FROM orders WHERE order_status='Pending'")->fetchColumn();
  $orderStats['preparing'] = (int) $conn->query("SELECT COUNT(*) FROM orders WHERE order_status='Preparing'")->fetchColumn();
  $orderStats['completed'] = (int) $conn->query("SELECT COUNT(*) FROM orders WHERE order_status='Completed'")->fetchColumn();
} catch (\PDOException $e) {
}
?>

<main class="main-content">
  <div class="tab-content">
    <div class="tab-pane fade show active" id="orders-tab">
      <div class="page-header d-flex justify-content-between align-items-center">
        <div>
          <h1 class="dashboard-heading">Order Management</h1>
          <p class="page-subtitle">View, filter, and manage all incoming orders.</p>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <span class="text-muted small fw-semibold">Total Orders</span>
            <h4 class="fw-bold text-dark mt-1 mb-0"><?= $orderStats['total'] ?></h4>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <span class="text-muted small fw-semibold">Pending</span>
            <h4 class="fw-bold text-dark mt-1 mb-0"><?= $orderStats['pending'] ?></h4>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <span class="text-muted small fw-semibold">Preparing</span>
            <h4 class="fw-bold text-dark mt-1 mb-0"><?= $orderStats['preparing'] ?></h4>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <span class="text-muted small fw-semibold">Completed</span>
            <h4 class="fw-bold text-dark mt-1 mb-0"><?= $orderStats['completed'] ?></h4>
          </div>
        </div>
      </div>

      <div class="table-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="fw-bold text-success mb-0">Orders</h4>
          <div class="d-flex gap-2">
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle table-sm mb-0 datatable-table">
            <thead class="table-light">
              <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Contact</th>
                <th>Items</th>
                <th>Payment</th>
                <th>Payment Status</th>
                <th>Order Status</th>
                <th>Total</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($allOrders)) : ?>
                <?php foreach ($allOrders as $order) : ?>
                  <?php
                    $orderNumber = !empty($order['order_number']) ? $order['order_number'] : ('#GB-' . str_pad((string) $order['id'], 6, '0', STR_PAD_LEFT));
                    $customerName = trim((($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '')));
                    $contact = $order['contact_number'] ?? '';
                    $itemsSummary = !empty($order['items_summary']) ? $order['items_summary'] : '—';
                    $total = number_format((float) ($order['total_amount'] ?? 0), 2);
                    $orderStatus = $order['order_status'] ?? '—';
                    $paymentStatus = $orderStatus === 'Pending' ? 'Pending' : 'Paid';
                    $paymentBadge = $orderStatus === 'Pending' ? 'bg-warning text-dark' : 'bg-success';
                    $orderBadge = $statusBadgeClass[$orderStatus] ?? 'bg-secondary';
                    $orderJson = json_encode($order);
                  ?>
                  <tr>
                    <td class="fw-bold"><?= htmlspecialchars($orderNumber) ?></td>
                    <td><?= htmlspecialchars($customerName ?: 'Customer') ?></td>
                    <td><?= htmlspecialchars($contact) ?></td>
                    <td><?= htmlspecialchars($itemsSummary) ?></td>
                    <td>GCash</td>
                    <td><span class="badge <?= $paymentBadge ?>"><?= $paymentStatus ?></span></td>
                    <td><span class="badge <?= $orderBadge ?>"><?= htmlspecialchars($orderStatus) ?></span></td>
                    <td>₱<?= $total ?></td>
                    <td>
                      <button class="btn btn-xs btn-outline-secondary btn-view-order" data-bs-toggle="modal" data-bs-target="#viewOrderModal" data-order='<?= $orderJson ?>'>
                        <i class="fa-solid fa-eye"></i>
                      </button>
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

<!-- Admin View Order Modal -->
<div class="modal fade" id="viewOrderModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-success"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-3">
        <div class="row g-3">
          <div class="col-md-5">
            <div class="border rounded p-3 h-100">
              <div class="small text-muted mb-2">Customer Details</div>
              <div class="fw-bold mb-1 cust-name"></div>
              <div class="small text-muted cust-contact"></div>
              <div class="small text-muted mt-2 cust-address"></div>
              <div class="small text-muted mt-2 order-reference"></div>
              <div class="small text-muted mt-2 order-type-line"></div>
            </div>
          </div>

          <div class="col-md-7">
            <div class="border rounded p-3 h-100">
              <div class="small text-muted mb-2">Order Summary</div>
              <div class="order-summary-body">
                <div class="text-center text-muted small py-3">Loading...</div>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-3 border rounded p-3">
          <div class="small text-muted mb-2">Ordered Items</div>
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead>
                <tr>
                  <th>Item</th>
                  <th>Qty</th>
                  <th>Price</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody class="items-table-body">
              </tbody>
            </table>
          </div>
        </div>

        <div class="mt-3 border rounded p-3">
          <div class="small text-muted mb-2">Progress Timeline</div>
          <div class="small progress-timeline-body">
            <div class="text-center text-muted small py-2">Loading...</div>
          </div>
        </div>

        <div class="mt-3 border rounded p-3">
          <div class="small text-muted mb-2">Payment Verification</div>
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
              <span class="small text-muted">Status: </span>
              <span class="badge payment-status-badge">—</span>
            </div>
            <div class="d-flex gap-1 flex-wrap">
              <button type="button" class="btn btn-sm btn-outline-primary btn-view-receipt" style="display:none;">
                <i class="fa-solid fa-receipt me-1"></i> View Receipt
              </button>
              <button type="button" class="btn btn-sm btn-success btn-approve-payment" style="display:none;">
                <i class="fa-solid fa-check me-1"></i> Approve
              </button>
              <button type="button" class="btn btn-sm btn-outline-danger btn-reject-payment" style="display:none;">
                <i class="fa-solid fa-xmark me-1"></i> Reject
              </button>
            </div>
          </div>
          <div class="small text-muted mt-2 payment-submitted-line"></div>
        </div>

        <div class="mt-3 border rounded p-3">
          <div class="small text-muted mb-2">Update Order Status</div>
          <div class="d-flex gap-2 flex-wrap">
            <select class="form-select form-select-sm" id="adminStatusSelect" style="max-width:220px;">
              <option value="Pending">Pending</option>
              <option value="Paid">Paid</option>
              <option value="Preparing">Preparing</option>
              <option value="Out for Delivery">Out for Delivery</option>
              <option value="Completed">Completed</option>
              <option value="Cancelled">Cancelled</option>
            </select>
            <button type="button" class="btn btn-sm btn-success" id="btnAdminSaveStatus">Save Status</button>
          </div>
        </div>

        <div class="d-flex justify-content-between fw-bold fs-6 mt-3 pt-2 border-top">
          <span>Total</span>
          <span class="text-success order-modal-total">₱0.00</span>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/payment_receipt_modal.php'; ?>

<?php include '../includes/admin_footer.php'; ?>

<script>
  function escapeHtml(text) {
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function money(value) {
    return '₱' + parseFloat(value || 0).toFixed(2);
  }

  const orderStatusBadgeClass = {
    'Pending': 'bg-warning text-dark',
    'Paid': 'bg-success',
    'Preparing': 'bg-warning text-dark',
    'Out for Delivery': 'bg-info text-dark',
    'Completed': 'bg-success',
    'Cancelled': 'bg-danger',
  };

  const paymentStatusBadgeClass = {
    'pending': 'bg-warning text-dark',
    'approved': 'bg-success',
    'rejected': 'bg-danger',
  };

  let currentViewOrder = null;

  $('#viewOrderModal').on('show.bs.modal', function (event) {
    const $trigger = $(event.relatedTarget);
    if (!$trigger.length) return;

    const order = $trigger.data('order');
    if (!order || !order.id) return;
    currentViewOrder = order;

    const $modal = $(this);
    const orderNumber = order.order_number || ('#GB-' + String(order.id).padStart(6, '0'));
    const customerName = ((order.first_name || '') + ' ' + (order.last_name || '')).trim() || 'Customer';

    $modal.find('.modal-title').text('Order #' + escapeHtml(orderNumber));

    // Customer Details
    $modal.find('.cust-name').text(escapeHtml(customerName));
    $modal.find('.cust-contact').text(escapeHtml(order.contact_number || ''));
    $modal.find('.cust-address').text(escapeHtml(order.address || ''));
    $modal.find('.order-reference').text('Reference: #' + escapeHtml(orderNumber));
    $modal.find('.order-type-line').text('Order Type: ' + (order.order_type === 0 ? 'Pickup' : 'Delivery'));

    // Order Summary
    const orderStatus = order.order_status || '—';
    const orderBadge = orderStatusBadgeClass[orderStatus] || 'bg-secondary';
    const subtotal = parseFloat(order.subtotal || 0);
    const deliveryFee = parseFloat(order.delivery_fee || 0);
    const totalAmount = parseFloat(order.total_amount || 0);

    $modal.find('.order-summary-body').html(`
      <div class="d-flex justify-content-between small mb-1">
        <span>Order Status</span><span class="badge ${orderBadge}">${escapeHtml(orderStatus)}</span>
      </div>
      <div class="d-flex justify-content-between small mb-1">
        <span>Payment Status</span><span class="badge ${paymentStatusBadgeClass[order.payment_status] || 'bg-secondary'}">${escapeHtml(order.payment_status || '—')}</span>
      </div>
      <div class="d-flex justify-content-between small mb-1">
        <span>Subtotal</span><span>${money(subtotal)}</span>
      </div>
      <div class="d-flex justify-content-between small mb-1">
        <span>Delivery</span><span>${money(deliveryFee)}</span>
      </div>
      <div class="d-flex justify-content-between small mb-1">
        <span>Total</span><span class="fw-bold">${money(totalAmount)}</span>
      </div>
      <div class="d-flex justify-content-between small mb-1">
        <span>Order Date</span><span>${escapeHtml(order.created_at || '—')}</span>
      </div>
    `);

    $modal.find('.order-modal-total').text(money(totalAmount));

    // Status select
    $modal.find('#adminStatusSelect').val(orderStatus);

    // Ordered Items
    const $itemsBody = $modal.find('.items-table-body');
    $itemsBody.html('<tr><td colspan="4" class="text-center text-muted small">Loading items...</td></tr>');

    $.ajax({
      url: '../includes/actions.php',
      type: 'POST',
      dataType: 'json',
      data: { form_type: 'get_order_items', order_id: order.id },
      success: function (response) {
        if (!response || !response.success || !Array.isArray(response.items)) {
          $itemsBody.html('<tr><td colspan="4" class="text-center text-muted small">Unable to load items.</td></tr>');
          return;
        }
        if (!response.items.length) {
          $itemsBody.html('<tr><td colspan="4" class="text-center text-muted small">No items found.</td></tr>');
          return;
        }
        $itemsBody.html(response.items.map(function (item) {
          const unitPrice = parseFloat(item.unit_price || 0);
          const itemSubtotal = parseFloat(item.subtotal || unitPrice * parseInt(item.quantity || 1, 10));
          return `<tr><td>${escapeHtml(item.item_name)}</td><td>${parseInt(item.quantity || 1, 10)}</td><td>${money(unitPrice)}</td><td>${money(itemSubtotal)}</td></tr>`;
        }).join(''));
      },
      error: function () {
        $itemsBody.html('<tr><td colspan="4" class="text-center text-muted small">Unable to load items.</td></tr>');
      }
    });

    // Progress Timeline
    const status = orderStatus;
    const statusOrder = ['Pending', 'Paid', 'Preparing', 'Out for Delivery', 'Completed'];
    const statusIndex = statusOrder.indexOf(status);
    const isCurrent = (s) => statusOrder.indexOf(s) === statusIndex;
    const isDone = (s) => statusOrder.indexOf(s) < statusIndex;
    const timelineSteps = [
      { label: 'Order placed', done: true, current: false },
      { label: 'Payment received', done: status !== 'Pending', current: status === 'Pending' },
      { label: 'Preparing food', done: isDone('Preparing'), current: isCurrent('Preparing') },
      { label: 'Out for delivery', done: isDone('Out for Delivery'), current: isCurrent('Out for Delivery') },
    ];
    $modal.find('.progress-timeline-body').html(timelineSteps.map(function (step) {
      let badgeHtml = step.done
        ? '<span class="badge bg-success me-2">✓</span>'
        : (step.current ? '<span class="badge bg-warning text-dark me-2">•</span>' : '<span class="badge bg-secondary me-2">•</span>');
      return `<div class="d-flex align-items-center mb-2">${badgeHtml}<span>${escapeHtml(step.label)}</span></div>`;
    }).join(''));

    // Payment Verification section
    const $badge = $modal.find('.payment-status-badge');
    $badge.removeClass('bg-warning text-dark bg-success bg-danger bg-secondary')
          .addClass(paymentStatusBadgeClass[order.payment_status] || 'bg-secondary')
          .text(order.payment_status || '—');

    const $viewReceiptBtn = $modal.find('.btn-view-receipt');
    const $approveBtn = $modal.find('.btn-approve-payment');
    const $rejectBtn = $modal.find('.btn-reject-payment');

    if (order.payment_receipt) {
      $viewReceiptBtn.show();
      $modal.find('.payment-submitted-line').text('Submitted: ' + (order.payment_verified_at || order.created_at || '—'));
    } else {
      $viewReceiptBtn.hide();
      $modal.find('.payment-submitted-line').text('No receipt submitted yet.');
    }

    if (order.payment_status === 'pending') {
      $approveBtn.show();
      $rejectBtn.show();
    } else {
      $approveBtn.hide();
      $rejectBtn.hide();
    }
  });

  // View Receipt → open shared receipt modal
  $(document).on('click', '.btn-view-receipt', function () {
    const order = currentViewOrder;
    if (!order) return;

    $('#receiptCustomer').text(((order.first_name || '') + ' ' + (order.last_name || '')).trim() || 'Customer');
    $('#receiptOrderId').text(order.order_number || ('#GB-' + String(order.id).padStart(6, '0')));
    $('#receiptMethod').text(order.payment_method || 'GCash');
    $('#receiptRefNo').text(order.payment_reference || '—');
    $('#receiptAmount').text(money(order.payment_amount || order.total_amount || 0));
    $('#receiptSubmitted').text(order.payment_verified_at || order.created_at || '—');
    $('#receiptStatus').text(order.payment_status || '—');

    const receiptUrl = order.payment_receipt ? '../img/payments/' + escapeHtml(order.payment_receipt) : '';
    const $img = $('#receiptImage');
    const $download = $('#receiptDownload');
    if (receiptUrl) {
      $img.attr('src', receiptUrl).show();
      $download.attr('href', receiptUrl).show();
    } else {
      $img.attr('src', '').hide();
      $download.hide();
    }

    $('#verifyPaymentId').val(order.payment_id || '');
    $('#rejectPaymentId').val(order.payment_id || '');

    const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
    modal.show();
  });

  // Approve payment (shared handler)
  $(document).on('click', '#btnVerifyPayment', function () {
    const paymentId = $('#verifyPaymentId').val();
    const remarks = $('#verifyNotes').val().trim();
    if (!paymentId) return;

    $.ajax({
      url: '../includes/actions.php',
      type: 'POST',
      dataType: 'json',
      data: {
        form_type: 'verify_payment',
        payment_id: paymentId,
        payment_status: 'approved',
        remarks: remarks,
      },
      success: function (response) {
        if (response && response.success) {
          showToast(response.message || 'Payment approved successfully!', 'success');
          const modal = bootstrap.Modal.getInstance(document.getElementById('verifyPaymentModal'));
          if (modal) modal.hide();
          const receiptModal = bootstrap.Modal.getInstance(document.getElementById('receiptModal'));
          if (receiptModal) receiptModal.hide();
          setTimeout(function () { window.location.reload(); }, 800);
          return;
        }
        showToast(response.message || 'Failed to approve payment.', 'danger');
      },
      error: function () {
        showToast('Something went wrong while approving payment.', 'danger');
      }
    });
  });

  // Reject payment (shared handler)
  $(document).on('click', '#btnRejectPayment', function () {
    const paymentId = $('#rejectPaymentId').val();
    const reason = $('#rejectReason').val();
    const notes = $('#rejectNotes').val().trim();
    const remarks = reason + (notes ? ' | ' + notes : '');
    if (!paymentId) return;
    if (!reason || reason === 'Choose reason') {
      alert('Please select a rejection reason.');
      return;
    }

    $.ajax({
      url: '../includes/actions.php',
      type: 'POST',
      dataType: 'json',
      data: {
        form_type: 'verify_payment',
        payment_id: paymentId,
        payment_status: 'rejected',
        remarks: remarks,
      },
      success: function (response) {
        if (response && response.success) {
          showToast(response.message || 'Payment rejected.', 'success');
          const modal = bootstrap.Modal.getInstance(document.getElementById('rejectPaymentModal'));
          if (modal) modal.hide();
          const receiptModal = bootstrap.Modal.getInstance(document.getElementById('receiptModal'));
          if (receiptModal) receiptModal.hide();
          setTimeout(function () { window.location.reload(); }, 800);
          return;
        }
        showToast(response.message || 'Failed to reject payment.', 'danger');
      },
      error: function () {
        showToast('Something went wrong while rejecting payment.', 'danger');
      }
    });
  });

  // Status Save
  $('#btnAdminSaveStatus').on('click', function () {
    const order = currentViewOrder;
    if (!order) return;
    const newStatus = $('#adminStatusSelect').val();
    const remarks = 'Status updated by admin';

    $.ajax({
      url: '../includes/actions.php',
      type: 'POST',
      dataType: 'json',
      data: {
        form_type: 'update_order_status',
        order_id: order.id,
        new_status: newStatus,
        remarks: remarks,
      },
      success: function (response) {
        if (response && response.success) {
          showToast(response.message || 'Order status updated.', 'success');
          setTimeout(function () { window.location.reload(); }, 800);
          return;
        }
        showToast(response.message || 'Failed to update status.', 'danger');
      },
      error: function () {
        showToast('Something went wrong while updating status.', 'danger');
      }
    });
  });

  // Open verify/reject modals from receipt modal buttons
  $(document).on('click', '.btn-approve-payment', function () {
    if (!currentViewOrder || !currentViewOrder.payment_id) return;
    $('#verifyPaymentId').val(currentViewOrder.payment_id);
    $('#verifyNotes').val('');
    new bootstrap.Modal(document.getElementById('verifyPaymentModal')).show();
  });
  $(document).on('click', '.btn-reject-payment', function () {
    if (!currentViewOrder || !currentViewOrder.payment_id) return;
    $('#rejectPaymentId').val(currentViewOrder.payment_id);
    $('#rejectReason').val('Choose reason');
    $('#rejectNotes').val('');
    new bootstrap.Modal(document.getElementById('rejectPaymentModal')).show();
  });
</script>