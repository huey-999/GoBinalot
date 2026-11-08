<!-- ==========================================================================
     GCash Payment Modal (shared across customer pages)
     ========================================================================== -->
<div class="modal fade" id="gcashPaymentModal" tabindex="-1" aria-labelledby="gcashPaymentModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg gcash-payment-modal">
      <div class="modal-header border-0 pb-2">
        <div>
          <p class="modal-pretitle text-success small fw-semibold">Scan to Pay</p>
          <h5 class="modal-title fw-bold" id="gcashPaymentModalLabel">GCash Payment</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="gcashPaymentForm">
        <input type="hidden" id="gcashOrderId" name="order_id">
        <input type="hidden" id="gcashAmount" name="amount">

        <div class="modal-body p-4">
          <div class="row g-4">
            <!-- Left: QR code card -->
            <div class="col-md-5">
              <div class="gcash-qr-card text-center">
                <div class="small text-muted mb-2">Order</div>
                <div class="fw-bold mb-3" id="gcashOrderNumber">#—</div>
                <div class="gcash-qr-box">
                  <img id="gcashQrImage" src="" alt="GCash QR" class="gcash-qr-img">
                </div>
                <div class="small text-muted mt-3">Scan with GCash app</div>
                <div class="small text-muted mt-1 gcash-qr-note">Payment is verified by admin after receipt upload.</div>
              </div>
            </div>

            <!-- Right: payment details card -->
            <div class="col-md-7">
              <div class="gcash-details-card">
                <div class="gcash-detail-row">
                  <span class="gcash-detail-label">Amount to pay</span>
                  <span class="gcash-amount" id="gcashAmountDisplay">₱0.00</span>
                </div>

                <div class="gcash-detail-row gcash-detail-input-row">
                  <label class="gcash-detail-label" for="gcashReferenceNumber">GCash Reference No.</label>
                  <input type="text" class="form-control" name="reference_number" id="gcashReferenceNumber" placeholder="Enter GCash reference no." required>
                </div>

                <div class="gcash-detail-row gcash-detail-input-row">
                  <label class="gcash-detail-label" for="gcashReceipt">Upload Receipt</label>
                  <input type="file" class="form-control" name="receipt" id="gcashReceipt" accept="image/*" required>
                  <div class="form-text small">JPG, PNG, or WEBP. Max 5MB recommended.</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer border-0 pt-2 pb-4 px-4">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-success btn-gcash-submit" id="btnSubmitPayment" disabled>Submit Payment</button>
        </div>
      </form>
    </div>
  </div>
</div>
