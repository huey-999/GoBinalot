<?php include '../includes/admin_header.php'; ?>

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
            <h4 class="fw-bold text-dark mt-1 mb-0">148</h4>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <span class="text-muted small fw-semibold">Pending</span>
            <h4 class="fw-bold text-dark mt-1 mb-0">24</h4>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <span class="text-muted small fw-semibold">Preparing</span>
            <h4 class="fw-bold text-dark mt-1 mb-0">19</h4>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <span class="text-muted small fw-semibold">Completed</span>
            <h4 class="fw-bold text-dark mt-1 mb-0">96</h4>
          </div>
        </div>
      </div>

      <div class="table-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="fw-bold text-success mb-0">Orders</h4>
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-success"><i class="fa-solid fa-plus me-1"></i> New Order</button>
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
              <tr>
                <td class="fw-bold">#ORD-1082</td>
                <td>Maria Santos</td>
                <td>09171234567</td>
                <td>2x Chicken Inasal Binalot</td>
                <td>GCash</td>
                <td><span class="badge bg-warning text-dark">Pending</span></td>
                <td><span class="badge bg-warning text-dark">Preparing</span></td>
                <td>₱370.00</td>
                <td>
                  <div class="d-flex flex-wrap gap-1">
                    <button class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#viewOrderModal">
                      <i class="fa-solid fa-eye"></i>
                    </button>
                    <button class="btn btn-xs btn-success" data-bs-toggle="modal" data-bs-target="#statusModal">
                      <i class="fa-solid fa-check"></i>
                    </button>
                    <button class="btn btn-xs btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">
                      <i class="fa-solid fa-xmark"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <tr>
                <td class="fw-bold">#ORD-1081</td>
                <td>Juan Dela Cruz</td>
                <td>09189876543</td>
                <td>1x Pork Adobo, 1x Halo-Halo</td>
                <td>Cash on Delivery</td>
                <td><span class="badge bg-success">Paid</span></td>
                <td><span class="badge bg-info text-dark">Out for Delivery</span></td>
                <td>₱295.00</td>
                <td>
                  <div class="d-flex flex-wrap gap-1">
                    <button class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#viewOrderModal">
                      <i class="fa-solid fa-eye"></i>
                    </button>
                    <button class="btn btn-xs btn-success" data-bs-toggle="modal" data-bs-target="#statusModal">
                      <i class="fa-solid fa-check"></i>
                    </button>
                    <button class="btn btn-xs btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">
                      <i class="fa-solid fa-xmark"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <tr>
                <td class="fw-bold">#ORD-1080</td>
                <td>Ana Reyes</td>
                <td>09223344556</td>
                <td>3x Crispy Lechon Kawali</td>
                <td>Bank Transfer</td>
                <td><span class="badge bg-success">Paid</span></td>
                <td><span class="badge bg-success">Completed</span></td>
                <td>₱585.00</td>
                <td>
                  <div class="d-flex flex-wrap gap-1">
                    <button class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#viewOrderModal">
                      <i class="fa-solid fa-eye"></i>
                    </button>
                    <button class="btn btn-xs btn-outline-dark" data-bs-toggle="modal" data-bs-target="#archiveModal">
                      <i class="fa-solid fa-box-archive"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</main>

<div class="modal fade" id="viewOrderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-success">Order #ORD-1082</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-3">
        <div class="row g-3">
          <div class="col-md-5">
            <div class="border rounded p-3 h-100">
              <div class="small text-muted mb-2">Customer Details</div>
              <div class="fw-bold mb-1">Maria Santos</div>
              <div class="small text-muted">09171234567</div>
              <div class="small text-muted mt-2">Cebu City, Lahug</div>
              <div class="small text-muted mt-2">Reference: #REF-4421</div>
            </div>
          </div>

          <div class="col-md-7">
            <div class="border rounded p-3 h-100">
              <div class="small text-muted mb-2">Order Summary</div>
              <div class="d-flex justify-content-between small mb-1">
                <span>Payment</span>
                <span class="fw-semibold">GCash</span>
              </div>
              <div class="d-flex justify-content-between small mb-1">
                <span>Payment Status</span>
                <span class="badge bg-warning text-dark">Pending</span>
              </div>
              <div class="d-flex justify-content-between small mb-1">
                <span>Order Status</span>
                <span class="badge bg-warning text-dark">Preparing</span>
              </div>
              <div class="d-flex justify-content-between small mb-1">
                <span>Order Date</span>
                <span>Oct 21, 2026</span>
              </div>
              <div class="d-flex justify-content-between small mb-1">
                <span>Delivery Time</span>
                <span>6:30 PM</span>
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
              <tbody>
                <tr>
                  <td>Chicken Inasal Binalot</td>
                  <td>2</td>
                  <td>₱185.00</td>
                  <td>₱370.00</td>
                </tr>
                <tr>
                  <td>Sago't Gulaman</td>
                  <td>1</td>
                  <td>₱60.00</td>
                  <td>₱60.00</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="mt-3 border rounded p-3">
          <div class="small text-muted mb-2">Progress Timeline</div>
          <div class="small">
            <div class="d-flex align-items-center mb-2">
              <span class="badge bg-success me-2">✓</span>
              <span>Order placed</span>
            </div>
            <div class="d-flex align-items-center mb-2">
              <span class="badge bg-success me-2">✓</span>
              <span>Payment received</span>
            </div>
            <div class="d-flex align-items-center mb-2">
              <span class="badge bg-warning text-dark me-2">•</span>
              <span>Preparing food</span>
            </div>
            <div class="d-flex align-items-center">
              <span class="badge bg-secondary me-2">•</span>
              <span>Out for delivery</span>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-between fw-bold fs-6 mt-3 pt-2 border-top">
          <span>Total</span>
          <span class="text-success">₱430.00</span>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-success">Update Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-3">
        <label class="form-label small text-muted">Status</label>
        <select class="form-select form-select-sm mb-3">
          <option>Pending</option>
          <option selected>Preparing</option>
          <option>Out for Delivery</option>
          <option>Completed</option>
          <option>Cancelled</option>
        </select>

        <label class="form-label small text-muted">Notes</label>
        <textarea class="form-control form-control-sm" rows="3" placeholder="Add note for staff..."></textarea>
      </div>

      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success btn-sm">Save</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-danger">Cancel Order</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-3">
        <label class="form-label small text-muted">Reason</label>
        <select class="form-select form-select-sm mb-3">
          <option selected>Choose reason</option>
          <option>Customer requested cancellation</option>
          <option>Payment failed</option>
          <option>Out of stock</option>
          <option>Delivery unavailable</option>
          <option>Other</option>
        </select>

        <label class="form-label small text-muted">Details</label>
        <textarea class="form-control form-control-sm" rows="3" placeholder="Add additional reason or notes..."></textarea>
      </div>

      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-danger btn-sm">Confirm Cancel</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="archiveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-dark">Archive Order</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-3">
        <p class="mb-0 small">This will soft-delete the record and keep its order history available for reporting and audit purposes.</p>
      </div>

      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-dark btn-sm">Archive</button>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/admin_footer.php'; ?>