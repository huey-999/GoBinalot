<?php include '../includes/admin_header.php'; ?>
<?php if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); } ?>

<main class="main-content">
  <div class="tab-content">
    <div class="tab-pane fade show active" id="payments-tab">
      <div class="page-header d-flex justify-content-between align-items-center">
        <div>
          <h1 class="dashboard-heading">Payment Verification</h1>
          <p class="page-subtitle">Review submitted payment proofs and validate transaction details.</p>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
    <span class="text-muted small fw-semibold">Pending Verification</span>
    <h4 class="fw-bold text-dark mt-1 mb-0" id="statPending">0</h4>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
    <span class="text-muted small fw-semibold">Approved Today</span>
    <h4 class="fw-bold text-dark mt-1 mb-0" id="statApproved">0</h4>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
    <span class="text-muted small fw-semibold">Rejected Today</span>
    <h4 class="fw-bold text-dark mt-1 mb-0" id="statRejected">0</h4>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
    <span class="text-muted small fw-semibold">Total Receipts</span>
    <h4 class="fw-bold text-dark mt-1 mb-0" id="statTotal">0</h4>
          </div>
        </div>
      </div>

      <div class="table-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="fw-bold text-success mb-0">Payment Submissions</h4>
          <button class="btn btn-sm btn-outline-secondary" onclick="loadPayments()"><i class="fa-solid fa-rotate me-1"></i> Refresh</button>
        </div>

        <div class="table-responsive">
          <table id="payments-table" class="table table-hover align-middle table-sm mb-0 datatable-table">
            <thead class="table-light">
              <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Method</th>
                <th>Ref. No.</th>
                <th>Amount</th>
                <th>Receipt</th>
                <th>Status</th>
                <th>Verified By</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="paymentsTableBody">
              <tr>
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                <td class="text-center text-muted small py-4">Loading payments...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include '../includes/payment_receipt_modal.php'; ?>

<?php include '../includes/admin_footer.php'; ?>

<script>
  const CSRF_TOKEN = <?= json_encode($_SESSION['csrf_token'] ?? '') ?>;
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

  function paymentStatusText(value) {
    if (!value) return '—';
    const text = String(value);
    return text.charAt(0).toUpperCase() + text.slice(1);
  }

  const statusBadgeClass = {
    pending: 'bg-warning text-dark',
    approved: 'bg-success',
    rejected: 'bg-danger',
  };

  const dtOptions = {
    paging: true,
    searching: true,
    ordering: true,
    info: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'All']],
    language: {
      search: '',
      searchPlaceholder: 'Search records...',
      lengthMenu: 'Show _MENU_',
      info: 'Showing _START_ to _END_ of _TOTAL_ entries',
      paginate: {
        previous: 'Prev',
        next: 'Next'
      }
    },
    initComplete: function () {
      const table = this.api();
      const $container = $(table.table().container());
      $container.find('.dataTables_filter input').addClass('form-control form-control-sm');
      $container.find('.dataTables_length select').addClass('form-select form-select-sm');
    }
  };

  function initPaymentsTable() {
    if (window.jQuery && $.fn.DataTable) {
      try {
        $('#payments-table').DataTable(dtOptions);
      } catch (e) {}
    }
  }

  const $tableBody = $('#paymentsTableBody');

  function loadPayments() {
        $tableBody.html('<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td class="text-center text-muted small py-4">Loading payments...</td></tr>');

     $.ajax({
       url: '../includes/actions.php',
       type: 'POST',
       dataType: 'json',
       data: Object.assign({ form_type: 'get_payments', csrf_token: CSRF_TOKEN }, (function () {
         const params = new URLSearchParams(window.location.search);
         const order = params.get('order') || params.get('order_number');
         return order ? { order_number: order } : {};
       })()),
        success: function (response) {
         if (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable('#payments-table')) {
          try { $('#payments-table').DataTable().clear().destroy(); } catch (e) {}
        }

        if (!response || !response.success || !Array.isArray(response.payments)) {
          $tableBody.html('<tr><td colspan="9" class="text-center text-muted small py-4">Unable to load payments.</td></tr>');
          return;
        }

         const payments = response.payments;
         const today = new Date().toISOString().slice(0, 10);
         let pending = 0, approved = 0, rejected = 0;
         let approvedToday = 0, rejectedToday = 0;

         $tableBody.html(payments.map(function (p) {
           if (p.payment_status === 'pending') pending++;
           else if (p.payment_status === 'approved') {
             approved++;
             if (p.verified_at && String(p.verified_at).slice(0, 10) === today) approvedToday++;
           } else if (p.payment_status === 'rejected') {
             rejected++;
             if (p.verified_at && String(p.verified_at).slice(0, 10) === today) rejectedToday++;
           }

          const customer = escapeHtml(((p.first_name || '') + ' ' + (p.last_name || '')).trim() || 'Customer');
          const badgeClass = statusBadgeClass[p.payment_status] || 'bg-secondary';
          const receiptUrl = p.receipt ? '../img/payments/' + escapeHtml(p.receipt) : '';
          const verifiedBy = p.verified_first_name || p.verified_last_name ? escapeHtml(((p.verified_first_name || '') + ' ' + (p.verified_last_name || '')).trim()) : '—';

          let actions;
          if (p.payment_status === 'pending') {
            actions = `
              <div class="d-flex flex-wrap gap-1">
                <button type="button" class="btn btn-xs btn-outline-secondary btn-view-receipt" data-payment-id="${p.id}" data-order-number="${escapeHtml(p.order_number)}" data-customer="${escapeHtml(customer)}" data-method="${escapeHtml(p.method)}" data-ref="${escapeHtml(p.reference_number)}" data-amount="${p.amount}" data-submitted="${escapeHtml(p.verified_at || p.order_created_at || '')}" data-status="${escapeHtml(p.payment_status)}" data-receipt="${escapeHtml(p.receipt || '')}" title="View Receipt">
                  <i class="fa-solid fa-eye"></i>
                </button>
                <button type="button" class="btn btn-xs btn-success btn-approve-payment" data-payment-id="${p.id}" data-order-number="${escapeHtml(p.order_number)}" title="Approve">
                  <i class="fa-solid fa-check"></i>
                </button>
                <button type="button" class="btn btn-xs btn-outline-danger btn-reject-payment" data-payment-id="${p.id}" data-order-number="${escapeHtml(p.order_number)}" title="Reject">
                  <i class="fa-solid fa-xmark"></i>
                </button>
              </div>
            `;
          } else {
            actions = `
              <button type="button" class="btn btn-xs btn-outline-secondary btn-view-receipt" data-payment-id="${p.id}" data-order-number="${escapeHtml(p.order_number)}" data-customer="${escapeHtml(customer)}" data-method="${escapeHtml(p.method)}" data-ref="${escapeHtml(p.reference_number)}" data-amount="${p.amount}" data-submitted="${escapeHtml(p.verified_at || p.order_created_at || '')}" data-status="${escapeHtml(p.payment_status)}" data-receipt="${escapeHtml(p.receipt || '')}" title="View Details">
                <i class="fa-solid fa-eye"></i>
              </button>
            `;
          }

          return `
            <tr>
              <td class="fw-bold">#${escapeHtml(p.order_number)}</td>
              <td>${customer}</td>
              <td>${escapeHtml(p.method)}</td>
              <td>${escapeHtml(p.reference_number)}</td>
              <td>${money(p.amount)}</td>
              <td>
                ${receiptUrl
                  ? '<button type="button" class="btn btn-xs btn-outline-secondary btn-view-receipt" data-payment-id="' + p.id + '" data-order-number="' + escapeHtml(p.order_number) + '" data-customer="' + escapeHtml(customer) + '" data-method="' + escapeHtml(p.method) + '" data-ref="' + escapeHtml(p.reference_number) + '" data-amount="' + p.amount + '" data-submitted="' + escapeHtml(p.created_at) + '" data-status="' + escapeHtml(p.payment_status) + '" data-receipt="' + escapeHtml(p.receipt) + '" title="View Receipt"><i class="fa-solid fa-image"></i></button>'
                  : '<span class="text-muted small">—</span>'}
              </td>
              <td><span class="badge ${badgeClass}">${escapeHtml(paymentStatusText(p.payment_status))}</span></td>
              <td>${verifiedBy}</td>
              <td>${actions}</td>
            </tr>
          `;
        }).join(''));

        $('#statPending').text(pending);
        $('#statApproved').text(approvedToday);
        $('#statRejected').text(rejectedToday);
        $('#statTotal').text(payments.length);
        initPaymentsTable();
      },
      error: function () {
        if (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable('#payments-table')) {
          try { $('#payments-table').DataTable().clear().destroy(); } catch (e) {}
        }
        $tableBody.html('<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td class="text-center text-muted small py-4">Unable to load payments.</td></tr>');
        initPaymentsTable();
      }
    });
  }

  // View receipt
  $(document).on('click', '.btn-view-receipt', function () {
    const receiptUrl = $(this).data('receipt');
    const $img = $('#receiptImage');
    const $download = $('#receiptDownload');

    if (receiptUrl) {
      $img.attr('src', '../img/payments/' + escapeHtml(receiptUrl)).show();
      $download.attr('href', '../img/payments/' + escapeHtml(receiptUrl)).show();
    } else {
      $img.attr('src', '').hide();
      $download.hide();
    }

    $('#receiptCustomer').text($(this).data('customer'));
    $('#receiptOrderId').text($(this).data('order-number'));
    $('#receiptMethod').text($(this).data('method'));
    $('#receiptRefNo').text($(this).data('ref'));
    $('#receiptAmount').text(money($(this).data('amount')));
    $('#receiptSubmitted').text($(this).data('submitted'));
    $('#receiptStatus').text(paymentStatusText($(this).data('status')));

    const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
    modal.show();
  });

  // Approve payment
  $(document).on('click', '.btn-approve-payment', function () {
    $('#verifyPaymentId').val($(this).data('payment-id'));
    $('#verifyNotes').val('');
    const modal = new bootstrap.Modal(document.getElementById('verifyPaymentModal'));
    modal.show();
  });

  $('#btnVerifyPayment').on('click', function () {
    const paymentId = $('#verifyPaymentId').val();
    const remarks = $('#verifyNotes').val().trim();

    $.ajax({
      url: '../includes/actions.php',
      type: 'POST',
      dataType: 'json',
       data: {
         form_type: 'verify_payment',
         csrf_token: CSRF_TOKEN,
         payment_id: paymentId,
         payment_status: 'approved',
         remarks: remarks,
       },
      success: function (response) {
        if (response && response.success) {
          showToast(response.message || 'Payment approved successfully!', 'success');
          const modal = bootstrap.Modal.getInstance(document.getElementById('verifyPaymentModal'));
          if (modal) modal.hide();
          loadPayments();
          return;
        }
        showToast(response.message || 'Failed to approve payment.', 'danger');
      },
      error: function () {
        showToast('Something went wrong while approving payment.', 'danger');
      }
    });
  });

  // Reject payment
  $(document).on('click', '.btn-reject-payment', function () {
    $('#rejectPaymentId').val($(this).data('payment-id'));
    $('#rejectReason').val('');
    $('#rejectNotes').val('');
    const modal = new bootstrap.Modal(document.getElementById('rejectPaymentModal'));
    modal.show();
  });

  $('#btnRejectPayment').on('click', function () {
    const paymentId = $('#rejectPaymentId').val();
    const reason = $('#rejectReason').val();
    const notes = $('#rejectNotes').val().trim();
    const remarks = reason + (notes ? ' | ' + notes : '');

    if (!reason) {
      alert('Please select a rejection reason.');
      return;
    }

    $.ajax({
      url: '../includes/actions.php',
      type: 'POST',
      dataType: 'json',
       data: {
         form_type: 'verify_payment',
         csrf_token: CSRF_TOKEN,
         payment_id: paymentId,
         payment_status: 'rejected',
         remarks: remarks,
       },
      success: function (response) {
        if (response && response.success) {
          showToast(response.message || 'Payment rejected.', 'success');
          const modal = bootstrap.Modal.getInstance(document.getElementById('rejectPaymentModal'));
          if (modal) modal.hide();
          loadPayments();
          return;
        }
        showToast(response.message || 'Failed to reject payment.', 'danger');
      },
      error: function () {
        showToast('Something went wrong while rejecting payment.', 'danger');
      }
    });
  });

  $(function () {
    loadPayments();
  });
</script>
