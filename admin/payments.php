<?php include '../includes/admin_header.php'; ?>

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
            <h4 class="fw-bold text-dark mt-1 mb-0">12</h4>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <span class="text-muted small fw-semibold">Approved Today</span>
            <h4 class="fw-bold text-dark mt-1 mb-0">28</h4>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <span class="text-muted small fw-semibold">Rejected Today</span>
            <h4 class="fw-bold text-dark mt-1 mb-0">4</h4>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <span class="text-muted small fw-semibold">Total Receipts</span>
            <h4 class="fw-bold text-dark mt-1 mb-0">146</h4>
          </div>
        </div>
      </div>

      <div class="table-card p-3 mb-3">
        <div class="row g-2 align-items-end">
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Search</label>
            <input type="text" class="form-control form-control-sm" placeholder="Order ID / customer / ref no.">
          </div>

          <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Date</label>
            <input type="date" class="form-control form-control-sm">
          </div>

          <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Status</label>
            <select class="form-select form-select-sm">
              <option selected>All</option>
              <option>Pending</option>
              <option>Approved</option>
              <option>Rejected</option>
              <option>Resubmitted</option>
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Method</label>
            <select class="form-select form-select-sm">
              <option selected>All</option>
              <option>GCash</option>
              <option>Cash on Delivery</option>
              <option>Bank Transfer</option>
            </select>
          </div>

          <div class="col-md-3">
            <div class="d-flex gap-2">
              <button class="btn btn-success btn-sm w-100">Filter</button>
              <button class="btn btn-outline-secondary btn-sm">Reset</button>
            </div>
          </div>
        </div>
      </div>

      <div class="table-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="fw-bold text-success mb-0">Payment Submissions</h4>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle table-sm mb-0">
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
            <tbody>
              <tr>
                <td class="fw-bold">#ORD-1082</td>
                <td>Maria Santos</td>
                <td>GCash</td>
                <td>GCPAY-4421</td>
                <td>₱430.00</td>
                <td><button class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#receiptModal">View</button></td>
                <td><span class="badge bg-warning text-dark">Pending</span></td>
                <td>—</td>
                <td>
                  <div class="d-flex flex-wrap gap-1">
                    <button class="btn btn-xs btn-success" data-bs-toggle="modal" data-bs-target="#verifyPaymentModal">Approve</button>
                    <button class="btn btn-xs btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectPaymentModal">Reject</button>
                  </div>
                </td>
              </tr>

              <tr>
                <td class="fw-bold">#ORD-1081</td>
                <td>Juan Dela Cruz</td>
                <td>GCash</td>
                <td>GCP-9981</td>
                <td>₱295.00</td>
                <td><button class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#receiptModal">View</button></td>
                <td><span class="badge bg-success">Approved</span></td>
                <td>Admin Liza</td>
                <td>
                  <button class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#receiptModal">Details</button>
                </td>
              </tr>

              <tr>
                <td class="fw-bold">#ORD-1079</td>
                <td>Ana Reyes</td>
                <td>Bank Transfer</td>
                <td>BT-2519</td>
                <td>₱585.00</td>
                <td><button class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#receiptModal">View</button></td>
                <td><span class="badge bg-danger">Rejected</span></td>
                <td>Admin Liza</td>
                <td>
                  <button class="btn btn-xs btn-outline-warning" data-bs-toggle="modal" data-bs-target="#rejectPaymentModal">Reason</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</main>

<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-success">Payment Receipt Review</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-3">
        <div class="row g-3">
          <div class="col-md-5">
            <div class="border rounded p-3 h-100 text-center">
              <div class="bg-light d-flex align-items-center justify-content-center" style="height:220px; border-radius:8px;">
                <i class="fa-solid fa-qrcode fa-4x text-success"></i>
              </div>
            </div>
          </div>

          <div class="col-md-7">
            <div class="border rounded p-3 h-100">
              <div class="small text-muted mb-2">Payment Details</div>
              <div class="d-flex justify-content-between small mb-2">
                <span>Customer</span>
                <span class="fw-semibold">Maria Santos</span>
              </div>
              <div class="d-flex justify-content-between small mb-2">
                <span>Order ID</span>
                <span>#ORD-1082</span>
              </div>
              <div class="d-flex justify-content-between small mb-2">
                <span>Claimed Ref. No.</span>
                <span>GCPAY-4421</span>
              </div>
              <div class="d-flex justify-content-between small mb-2">
                <span>Claimed Amount</span>
                <span>₱430.00</span>
              </div>
              <div class="d-flex justify-content-between small mb-2">
                <span>Submitted</span>
                <span>Oct 21, 2026</span>
              </div>
              <div class="d-flex justify-content-between small mb-2">
                <span>Method</span>
                <span>GCash</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="verifyPaymentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-success">Approve Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-3">
        <p class="small mb-3">Verify that the submitted payment details match the order amount and transaction reference.</p>

        <label class="form-label small text-muted">Verified By</label>
        <input type="text" class="form-control form-control-sm mb-3" value="Admin Liza">

        <label class="form-label small text-muted">Verification Notes</label>
        <textarea class="form-control form-control-sm" rows="3" placeholder="Optional notes..."></textarea>
      </div>

      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success btn-sm">Approve Payment</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="rejectPaymentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-danger">Reject Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-3">
        <label class="form-label small text-muted">Rejection Reason</label>
        <select class="form-select form-select-sm mb-3">
          <option selected>Choose reason</option>
          <option>Wrong reference number</option>
          <option>Amount mismatch</option>
          <option>Receipt image unclear</option>
          <option>Invalid transaction</option>
          <option>Other</option>
        </select>

        <label class="form-label small text-muted">Notes</label>
        <textarea class="form-control form-control-sm" rows="3" placeholder="Describe why the payment was rejected..."></textarea>
      </div>

      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-danger btn-sm">Reject Payment</button>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/admin_footer.php'; ?>
