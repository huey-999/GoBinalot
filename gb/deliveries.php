<?php include '../includes/admin_header.php'; ?>

<main class="main-content">
  <div class="tab-content">
    <div class="tab-pane fade show active" id="delivery-tab">
      <div class="page-header d-flex justify-content-between align-items-center">
        <div>
          <h1 class="dashboard-heading">Delivery Management</h1>
          <p class="page-subtitle">Track assignments, dispatch times, and delivery progress.</p>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <span class="text-muted small fw-semibold">Active Deliveries</span>
            <h4 class="fw-bold text-dark mt-1 mb-0">14</h4>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <span class="text-muted small fw-semibold">Assigned</span>
            <h4 class="fw-bold text-dark mt-1 mb-0">9</h4>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <span class="text-muted small fw-semibold">Pending Dispatch</span>
            <h4 class="fw-bold text-dark mt-1 mb-0">5</h4>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <span class="text-muted small fw-semibold">Delivered Today</span>
            <h4 class="fw-bold text-dark mt-1 mb-0">26</h4>
          </div>
        </div>
      </div>

      <div class="table-card p-3 mb-3">
        <div class="row g-2 align-items-end">
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Search</label>
            <input type="text" class="form-control form-control-sm" placeholder="Order ID / customer">
          </div>

          <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Date</label>
            <input type="date" class="form-control form-control-sm">
          </div>

          <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Status</label>
            <select class="form-select form-select-sm">
              <option selected>All</option>
              <option>Pending Dispatch</option>
              <option>Assigned</option>
              <option>Picked Up</option>
              <option>Out for Delivery</option>
              <option>Delivered</option>
              <option>Cancelled</option>
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Rider</label>
            <select class="form-select form-select-sm">
              <option selected>All</option>
              <option>Rider A</option>
              <option>Rider B</option>
              <option>Rider C</option>
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
          <h4 class="fw-bold text-success mb-0">Delivery Orders</h4>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle table-sm mb-0">
            <thead class="table-light">
              <tr>
                <th>Order ID</th>
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
              <tr>
                <td class="fw-bold">#ORD-1082</td>
                <td>Maria Santos</td>
                <td>Cebu City, Lahug</td>
                <td>09171234567</td>
                <td>Rider A</td>
                <td>5:10 PM</td>
                <td>6:00 PM</td>
                <td><span class="badge bg-warning text-dark">Out for Delivery</span></td>
                <td>
                  <div class="d-flex flex-wrap gap-1">
                    <button class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#deliveryDetailsModal"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn btn-xs btn-success" data-bs-toggle="modal" data-bs-target="#assignRiderModal"><i class="fa-solid fa-user-plus"></i></button>
                    <button class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#statusModal"><i class="fa-solid fa-arrow-right"></i></button>
                  </div>
                </td>
              </tr>

              <tr>
                <td class="fw-bold">#ORD-1081</td>
                <td>Juan Dela Cruz</td>
                <td>Mandaue, Centro</td>
                <td>09189876543</td>
                <td>Rider B</td>
                <td>4:25 PM</td>
                <td>5:15 PM</td>
                <td><span class="badge bg-info text-dark">Picked Up</span></td>
                <td>
                  <div class="d-flex flex-wrap gap-1">
                    <button class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#deliveryDetailsModal"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn btn-xs btn-success" data-bs-toggle="modal" data-bs-target="#assignRiderModal"><i class="fa-solid fa-user-plus"></i></button>
                    <button class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#statusModal"><i class="fa-solid fa-arrow-right"></i></button>
                  </div>
                </td>
              </tr>

              <tr>
                <td class="fw-bold">#ORD-1078</td>
                <td>Ana Reyes</td>
                <td>Talamban, Cebu</td>
                <td>09223344556</td>
                <td>Unassigned</td>
                <td>—</td>
                <td>—</td>
                <td><span class="badge bg-secondary">Pending Dispatch</span></td>
                <td>
                  <div class="d-flex flex-wrap gap-1">
                    <button class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#deliveryDetailsModal"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn btn-xs btn-success" data-bs-toggle="modal" data-bs-target="#assignRiderModal"><i class="fa-solid fa-user-plus"></i></button>
                    <button class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#statusModal"><i class="fa-solid fa-arrow-right"></i></button>
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

<div class="modal fade" id="deliveryDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-success">Delivery Details #ORD-1082</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-3">
        <div class="row g-3">
          <div class="col-md-5">
            <div class="border rounded p-3 h-100">
              <div class="small text-muted mb-2">Customer</div>
              <div class="fw-bold">Maria Santos</div>
              <div class="small text-muted mt-2">09171234567</div>
              <div class="small text-muted mt-2">Cebu City, Lahug</div>
            </div>
          </div>

          <div class="col-md-7">
            <div class="border rounded p-3 h-100">
              <div class="small text-muted mb-2">Delivery Summary</div>
              <div class="d-flex justify-content-between small mb-1">
                <span>Rider</span>
                <span class="fw-semibold">Rider A</span>
              </div>
              <div class="d-flex justify-content-between small mb-1">
                <span>Dispatch Time</span>
                <span>5:10 PM</span>
              </div>
              <div class="d-flex justify-content-between small mb-1">
                <span>ETA</span>
                <span>6:00 PM</span>
              </div>
              <div class="d-flex justify-content-between small mb-1">
                <span>Payment Status</span>
                <span class="badge bg-success">Paid</span>
              </div>
              <div class="d-flex justify-content-between small mb-1">
                <span>Order Status</span>
                <span class="badge bg-warning text-dark">Out for Delivery</span>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-3 border rounded p-3">
          <div class="small text-muted mb-2">Delivery Timeline</div>
          <div class="small">
            <div class="d-flex align-items-center mb-2">
              <span class="badge bg-success me-2">✓</span>
              <span>Order received and confirmed</span>
            </div>
            <div class="d-flex align-items-center mb-2">
              <span class="badge bg-success me-2">✓</span>
              <span>Food prepared</span>
            </div>
            <div class="d-flex align-items-center mb-2">
              <span class="badge bg-success me-2">✓</span>
              <span>Rider assigned</span>
            </div>
            <div class="d-flex align-items-center">
              <span class="badge bg-warning text-dark me-2">•</span>
              <span>Out for delivery</span>
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
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Chicken Inasal Binalot</td>
                  <td>2</td>
                  <td>₱370.00</td>
                </tr>
                <tr>
                  <td>Sago't Gulaman</td>
                  <td>1</td>
                  <td>₱60.00</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="assignRiderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-success">Assign Rider</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-3">
        <label class="form-label small text-muted">Select Rider</label>
        <select class="form-select form-select-sm mb-3">
          <option selected>Choose rider</option>
          <option>Rider A</option>
          <option>Rider B</option>
          <option>Rider C</option>
        </select>

        <label class="form-label small text-muted">Dispatch Time</label>
        <input type="time" class="form-control form-control-sm mb-3">

        <label class="form-label small text-muted">Notes</label>
        <textarea class="form-control form-control-sm" rows="3" placeholder="Delivery note or rider instructions..."></textarea>
      </div>

      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success btn-sm">Assign</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-success">Update Delivery Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-3">
        <label class="form-label small text-muted">Status</label>
        <select class="form-select form-select-sm mb-3">
          <option>Pending Dispatch</option>
          <option>Assigned</option>
          <option>Picked Up</option>
          <option selected>Out for Delivery</option>
          <option>Delivered</option>
          <option>Cancelled</option>
        </select>

        <label class="form-label small text-muted">Completion Time</label>
        <input type="datetime-local" class="form-control form-control-sm">
      </div>

      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success btn-sm">Save</button>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/admin_footer.php'; ?>
