<?php include '../includes/admin_header.php'; ?>

<main class="main-content">
  <div class="tab-content">

    <!-- TAB 1: DASHBOARD OVERVIEW -->
    <div class="tab-pane fade show active" id="dashboard-tab">
      <div class="page-header d-flex justify-content-between align-items-center">
        <div>
          <h1 class="dashboard-heading">Dashboard Overview</h1>
          <p class="page-subtitle">Daily summaries and real-time activity performance.</p>
        </div>
      </div>

      <!-- Stat Cards -->
      <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card d-flex align-items-center justify-content-between p-3">
            <div>
              <span class="text-muted small fw-semibold">Orders Received Today</span>
              <h4 class="fw-bold text-dark mt-1 mb-0">46</h4>
            </div>
            <div class="stat-icon-wrapper bg-light-success">
              <i class="fa-solid fa-receipt"></i>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card d-flex align-items-center justify-content-between p-3">
            <div>
              <span class="text-muted small fw-semibold">Pending Orders & Payments</span>
              <h4 class="fw-bold text-dark mt-1 mb-0">12</h4>
            </div>
            <div class="stat-icon-wrapper bg-light-warning">
              <i class="fa-solid fa-wallet"></i>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card d-flex align-items-center justify-content-between p-3">
            <div>
              <span class="text-muted small fw-semibold">Preparing / Delivering</span>
              <h4 class="fw-bold text-dark mt-1 mb-0">9</h4>
            </div>
            <div class="stat-icon-wrapper bg-light-primary">
              <i class="fa-solid fa-kitchen-set"></i>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card d-flex align-items-center justify-content-between p-3">
            <div>
              <span class="text-muted small fw-semibold">Completed Orders</span>
              <h4 class="fw-bold text-dark mt-1 mb-0">31</h4>
            </div>
            <div class="stat-icon-wrapper bg-light-danger">
              <i class="fa-solid fa-circle-check"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Sales Summary Cards -->
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <div class="table-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold text-success mb-0">Daily Sales</h6>
              <span class="badge bg-success-subtle text-success border border-success-subtle">Today</span>
            </div>
            <h3 class="fw-bold text-dark mb-1">₱18,450</h3>
            <small class="text-muted">+12.4% vs yesterday</small>
          </div>
        </div>

        <div class="col-md-4">
          <div class="table-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold text-success mb-0">Weekly Sales</h6>
              <span class="badge bg-primary-subtle text-primary border border-primary-subtle">This Week</span>
            </div>
            <h3 class="fw-bold text-dark mb-1">₱126,200</h3>
            <small class="text-muted">+8.7% from last week</small>
          </div>
        </div>

        <div class="col-md-4">
          <div class="table-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-bold text-success mb-0">Monthly Sales</h6>
              <span class="badge bg-warning-subtle text-warning border border-warning-subtle">This Month</span>
            </div>
            <h3 class="fw-bold text-dark mb-1">₱482,350</h3>
            <small class="text-muted">+15.2% from last month</small>
          </div>
        </div>
      </div>

      <!-- Recent Orders Table -->
      <div class="table-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="fw-bold text-success mb-0">Recent Orders</h5>
          <span class="badge bg-success-subtle text-success border border-success-subtle">Live Updates</span>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle table-sm mb-0">
            <thead class="table-light">
              <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="fw-bold">#ORD-1082</td>
                <td>Maria Santos</td>
                <td>2x Chicken Inasal Binalot</td>
                <td>₱370.00</td>
                <td><span class="badge bg-warning text-dark">Pending</span></td>
                <td><span class="badge bg-warning text-dark">Preparing</span></td>
                <td><button type="button" class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#viewOrderModal">Details</button></td>
              </tr>

              <tr>
                <td class="fw-bold">#ORD-1081</td>
                <td>Juan Dela Cruz</td>
                <td>1x Pork Adobo, 1x Halo-Halo</td>
                <td>₱295.00</td>
                <td><span class="badge bg-success">Paid</span></td>
                <td><span class="badge bg-info text-dark">Out for Delivery</span></td>
                <td><button type="button" class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#viewOrderModal">Details</button></td>
              </tr>

              <tr>
                <td class="fw-bold">#ORD-1080</td>
                <td>Ana Reyes</td>
                <td>3x Crispy Lechon Kawali</td>
                <td>₱585.00</td>
                <td><span class="badge bg-success">Paid</span></td>
                <td><span class="badge bg-success">Completed</span></td>
                <td><button type="button" class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#viewOrderModal">Details</button></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</main>

<!-- MODAL 2: ORDER DETAILS -->
<div class="modal fade" id="viewOrderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-success">Order Details #ORD-1082</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-3">
        <div class="mb-3 pb-2 border-bottom">
          <span class="text-muted small">Customer Name:</span>
          <div class="fw-bold">Maria Santos</div>
        </div>

        <div class="mb-3">
          <span class="text-muted small">Items Ordered:</span>
          <ul class="list-group list-group-flush small">
            <li class="list-group-item d-flex justify-content-between px-0">
              2x Chicken Inasal Binalot <span>₱370.00</span>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              1x Sago't Gulaman <span>₱60.00</span>
            </li>
          </ul>
        </div>

        <div class="d-flex justify-content-between fw-bold fs-6 pt-2 border-top">
          <span>Total:</span>
          <span class="text-success">₱430.00</span>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/admin_footer.php'; ?>