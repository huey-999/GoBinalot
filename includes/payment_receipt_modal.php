<!-- ==========================================================================
     Payment Receipt Modals (shared across admin pages)
     Provides: #receiptModal, #verifyPaymentModal, #rejectPaymentModal
     ========================================================================== -->
<!-- Receipt View Modal -->
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
                <img id="receiptImage" src="" alt="Receipt" style="max-width:100%;max-height:100%;object-fit:contain;border-radius:8px;" />
              </div>
              <div class="mt-2">
                <a id="receiptDownload" class="btn btn-xs btn-outline-secondary" target="_blank" rel="noopener"><i class="fa-solid fa-download me-1"></i> Save</a>
              </div>
            </div>
          </div>

          <div class="col-md-7">
            <div class="border rounded p-3 h-100">
              <div class="small text-muted mb-2">Payment Details</div>
              <div class="d-flex justify-content-between small mb-2">
                <span>Customer</span>
                <span class="fw-semibold" id="receiptCustomer">—</span>
              </div>
              <div class="d-flex justify-content-between small mb-2">
                <span>Order ID</span>
                <span class="fw-semibold" id="receiptOrderId">—</span>
              </div>
              <div class="d-flex justify-content-between small mb-2">
                <span>Method</span>
                <span id="receiptMethod">—</span>
              </div>
              <div class="d-flex justify-content-between small mb-2">
                <span>Claimed Ref. No.</span>
                <span id="receiptRefNo">—</span>
              </div>
              <div class="d-flex justify-content-between small mb-2">
                <span>Claimed Amount</span>
                <span id="receiptAmount">—</span>
              </div>
              <div class="d-flex justify-content-between small mb-2">
                <span>Submitted</span>
                <span id="receiptSubmitted">—</span>
              </div>
              <div class="d-flex justify-content-between small mb-2">
                <span>Status</span>
                <span id="receiptStatus" class="fw-semibold">—</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Verify Payment Modal -->
<div class="modal fade" id="verifyPaymentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-success">Approve Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-3">
        <input type="hidden" id="verifyPaymentId">
        <p class="small mb-3">Verify that the submitted payment details match the order amount and transaction reference.</p>

        <label class="form-label small text-muted">Verified By</label>
        <input type="text" class="form-control form-control-sm mb-3" value="Admin" readonly>

        <label class="form-label small text-muted">Verification Notes</label>
        <textarea class="form-control form-control-sm" id="verifyNotes" rows="3" placeholder="Optional notes..."></textarea>
      </div>

      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success btn-sm" id="btnVerifyPayment">Approve Payment</button>
      </div>
    </div>
  </div>
</div>

<!-- Reject Payment Modal -->
<div class="modal fade" id="rejectPaymentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-danger">Reject Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-3">
        <input type="hidden" id="rejectPaymentId">
        <label class="form-label small text-muted">Rejection Reason</label>
        <select class="form-select form-select-sm mb-3" id="rejectReason">
          <option selected>Choose reason</option>
          <option>Wrong reference number</option>
          <option>Amount mismatch</option>
          <option>Receipt image unclear</option>
          <option>Invalid transaction</option>
          <option>Other</option>
        </select>

        <label class="form-label small text-muted">Notes</label>
        <textarea class="form-control form-control-sm" id="rejectNotes" rows="3" placeholder="Describe why the payment was rejected..."></textarea>
      </div>

      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-danger btn-sm" id="btnRejectPayment">Reject Payment</button>
      </div>
    </div>
  </div>
</div>
