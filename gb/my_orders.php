<?php include '../includes/admin_header.php'; ?>
<?php
$currentUserRole = $_SESSION['user_data']['role'] ?? 'customer';
$isCustomer = $currentUserRole === 'customer';
$userId = $_SESSION['user_data']['id'] ?? 0;

// Fetch logged-in user's orders server-side
$myOrders = [];
if ($userId) {
  try {
    $stmt = $conn->prepare("
      SELECT o.id, o.order_number, o.subtotal, o.delivery_fee, o.total_amount, o.order_status, o.created_at,
             u.first_name, u.last_name, a.contact_number, a.address,
             GROUP_CONCAT(CONCAT(oi.quantity, 'x ', oi.item_name) SEPARATOR ', ') as items_summary
      FROM orders o
      JOIN users u ON u.id = o.user_id
      LEFT JOIN addresses a ON a.id = o.address_id
      LEFT JOIN order_items oi ON oi.order_id = o.id
      WHERE o.user_id = :user_id
      GROUP BY o.id
      ORDER BY o.created_at DESC
    ");
    $stmt->execute([':user_id' => $userId]);
    $myOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (\PDOException $e) {
    $myOrders = [];
  }
}

$statusBadgeClass = [
  'Pending' => 'bg-warning text-dark',
  'Paid' => 'bg-success',
  'Preparing' => 'bg-warning text-dark',
  'Out for Delivery' => 'bg-info text-dark',
  'Completed' => 'bg-success',
  'Cancelled' => 'bg-danger',
];
?>
<main class="main-content">
  <div class="tab-content">
    <div class="tab-pane fade show active" id="orders-tab">
      <div class="page-header d-flex justify-content-between align-items-center">
        <div>
          <h1 class="dashboard-heading">My Orders</h1>
          <p class="page-subtitle">View, filter, and manage all incoming orders.</p>
        </div>
      </div>

        <div class="table-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h4 class="fw-bold text-success mb-0">Orders</h4>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle table-sm mb-0 datatable-table">
            <thead class="table-light">
              <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Contact</th>
                <th>Items</th>
                <th>Order Status</th>
                <th>Total</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($myOrders)) : ?>
                <?php foreach ($myOrders as $order) : ?>
                  <?php
                    $orderNumber = !empty($order['order_number']) ? $order['order_number'] : ('#GB-' . str_pad((string) $order['id'], 6, '0', STR_PAD_LEFT));
                    $customerName = trim((($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '')));
                    $contact = $order['contact_number'] ?? '';
                    $itemsSummary = !empty($order['items_summary']) ? $order['items_summary'] : '—';
                    $total = number_format((float) ($order['total_amount'] ?? 0), 2);
                    $orderStatus = $order['order_status'] ?? '—';
                    $badgeClass = $statusBadgeClass[$orderStatus] ?? 'bg-secondary';
                    $orderJson = json_encode($order);
                  ?>
                  <tr>
                    <td class="fw-bold"><?= htmlspecialchars($orderNumber) ?></td>
                    <td><?= htmlspecialchars($customerName ?: 'Customer') ?></td>
                    <td><?= htmlspecialchars($contact) ?></td>
                    <td><?= htmlspecialchars($itemsSummary) ?></td>
                    <td><span class="badge <?= htmlspecialchars($badgeClass) ?>"><?= htmlspecialchars($orderStatus) ?></span></td>
                    <td>₱<?= $total ?></td>
                    <td>
                      <div class="d-flex flex-wrap gap-1">
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#viewOrderModal" data-order='<?= $orderJson ?>'>
                          <i class="fa-solid fa-eye"></i>
                        </button>
                        <?php if ($isCustomer && $orderStatus === 'Pending') : ?>
                          <button type="button" class="btn btn-xs btn-success btn-pay-gcash" data-order-id="<?= $order['id'] ?>" data-order-number="<?= htmlspecialchars($orderNumber) ?>" data-total="<?= (float) ($order['total_amount'] ?? 0) ?>" title="Pay via GCash">
                            <i class="fa-solid fa-qrcode"></i>
                          </button>
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
    </div>
  </div>
</main>

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

        <div class="d-flex justify-content-between fw-bold fs-6 mt-3 pt-2 border-top">
          <span>Total</span>
          <span class="text-success order-modal-total">₱0.00</span>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/gcash_modal.php'; ?>

<script>
  (function () {
    document.querySelectorAll('.btn-pay-gcash').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const orderId = btn.getAttribute('data-order-id');
        const orderNumber = btn.getAttribute('data-order-number');
        const total = parseFloat(btn.getAttribute('data-total') || 0);

        document.getElementById('gcashOrderId').value = orderId;
        document.getElementById('gcashAmount').value = total;
        document.getElementById('gcashOrderNumber').textContent = orderNumber;
        document.getElementById('gcashAmountDisplay').textContent = '₱' + total.toFixed(2);
        document.getElementById('gcashQrImage').src = 'https://api.qrserver.com/v1/create-qr-code/?data=' + encodeURIComponent(orderNumber) + '&size=300x300';
        document.getElementById('gcashReferenceNumber').value = '';
        document.getElementById('gcashReceipt').value = '';
        document.getElementById('btnSubmitPayment').disabled = true;

        const modal = new bootstrap.Modal(document.getElementById('gcashPaymentModal'));
        modal.show();
      });
    });
  })();
</script>

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

  // Populate view order modal dynamically from order data
  $('#viewOrderModal').on('show.bs.modal', function (event) {
    const $trigger = $(event.relatedTarget);
    if (!$trigger.length) return;

    const order = $trigger.data('order');
    if (!order || !order.id) return;

    const $modal = $(this);
    const orderNumber = order.order_number || ('#GB-' + String(order.id).padStart(6, '0'));
    const customerName = ((order.first_name || '') + ' ' + (order.last_name || '')).trim() || 'Customer';

    // Modal title
    $modal.find('.modal-title').text('Order #' + escapeHtml(orderNumber));

    // Customer Details
    $modal.find('.cust-name').text(escapeHtml(customerName));
    $modal.find('.cust-contact').text(escapeHtml(order.contact_number || ''));
    $modal.find('.cust-address').text(escapeHtml(order.address || ''));
    $modal.find('.order-reference').text('Reference: #' + escapeHtml(orderNumber));

    // Order Summary
    const orderStatus = escapeHtml(order.order_status || '—');
    const badgeClass = orderStatusBadgeClass[order.order_status] || 'bg-secondary';

    const subtotal = parseFloat(order.subtotal || 0);
    const deliveryFee = parseFloat(order.delivery_fee || 0);
    const totalAmount = parseFloat(order.total_amount || 0);

    $modal.find('.order-summary-body').html(`
      <div class="d-flex justify-content-between small mb-1">
        <span>Order Status</span><span class="badge ${badgeClass}">${orderStatus}</span>
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

    // Bottom total line
    $modal.find('.order-modal-total').text(money(totalAmount));

    // Ordered Items
    const $itemsBody = $modal.find('.items-table-body');
    $itemsBody.html('<tr><td colspan="4" class="text-center text-muted small">Loading items...</td></tr>');

    $.ajax({
      url: '../includes/actions.php',
      type: 'POST',
      dataType: 'json',
      data: {
        form_type: 'get_order_items',
        order_id: order.id,
      },
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
          return `
            <tr>
              <td>${escapeHtml(item.item_name)}</td>
              <td>${parseInt(item.quantity || 1, 10)}</td>
              <td>${money(unitPrice)}</td>
              <td>${money(itemSubtotal)}</td>
            </tr>
          `;
        }).join(''));
      },
      error: function () {
        $itemsBody.html('<tr><td colspan="4" class="text-center text-muted small">Unable to load items.</td></tr>');
      }
    });

    // Progress Timeline
    const status = order.order_status || 'Pending';
    const statusOrder = ['Pending', 'Preparing', 'Out for Delivery', 'Completed'];
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
      let badgeHtml;
      if (step.done) {
        badgeHtml = '<span class="badge bg-success me-2">✓</span>';
      } else if (step.current) {
        badgeHtml = '<span class="badge bg-warning text-dark me-2">•</span>';
      } else {
        badgeHtml = '<span class="badge bg-secondary me-2">•</span>';
      }
      return `
        <div class="d-flex align-items-center mb-2">
          ${badgeHtml}
          <span>${escapeHtml(step.label)}</span>
        </div>
      `;
    }).join(''));
  });
</script>