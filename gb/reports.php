<?php include '../includes/admin_header.php'; ?>

<main class="main-content">
  <div class="tab-content">
    <div class="tab-pane fade show active" id="reports-tab">
      <div class="page-header d-flex justify-content-between align-items-center">
        <div>
          <h1 class="dashboard-heading">Reports & Feedback</h1>
          <p class="page-subtitle">Track sales performance and customer satisfaction.</p>
        </div>

        <div class="d-flex gap-2">
          <button class="btn btn-outline-secondary btn-sm">Export CSV</button>
          <button class="btn btn-success btn-sm">Print Report</button>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <span class="text-muted small fw-semibold">Daily Sales</span>
            <h4 class="fw-bold text-dark mt-1 mb-0">₱18,450</h4>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <span class="text-muted small fw-semibold">Weekly Sales</span>
            <h4 class="fw-bold text-dark mt-1 mb-0">₱126,200</h4>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <span class="text-muted small fw-semibold">Monthly Sales</span>
            <h4 class="fw-bold text-dark mt-1 mb-0">₱482,350</h4>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card p-3">
            <span class="text-muted small fw-semibold">Avg. Rating</span>
            <h4 class="fw-bold text-dark mt-1 mb-0">4.8/5</h4>
          </div>
        </div>
      </div>

      <div class="table-card p-3 mb-3">
        <div class="row g-2 align-items-end">
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Date Range</label>
            <select class="form-select form-select-sm">
              <option selected>Today</option>
              <option>This Week</option>
              <option>This Month</option>
              <option>Custom Range</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">From</label>
            <input type="date" class="form-control form-control-sm">
          </div>

          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">To</label>
            <input type="date" class="form-control form-control-sm">
          </div>

          <div class="col-md-3">
            <div class="d-flex gap-2">
              <button class="btn btn-success btn-sm w-100">Apply</button>
              <button class="btn btn-outline-secondary btn-sm">Reset</button>
            </div>
          </div>
        </div>
      </div>

      <div class="table-card p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="fw-bold text-success mb-0">Sales Report</h5>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle table-sm mb-0">
            <thead class="table-light">
              <tr>
                <th>Date</th>
                <th>Completed Orders</th>
                <th>Order Count</th>
                <th>Payment Total</th>
                <th>Sales</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Oct 21, 2026</td>
                <td>31</td>
                <td>46</td>
                <td>₱18,450</td>
                <td>₱18,450</td>
              </tr>
              <tr>
                <td>Oct 20, 2026</td>
                <td>29</td>
                <td>41</td>
                <td>₱16,980</td>
                <td>₱16,980</td>
              </tr>
              <tr>
                <td>Oct 19, 2026</td>
                <td>27</td>
                <td>39</td>
                <td>₱15,260</td>
                <td>₱15,260</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="table-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="fw-bold text-success mb-0">Customer Feedback</h5>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <div class="border rounded p-3 h-100">
              <div class="small text-muted">5-Star Reviews</div>
              <div class="fw-bold fs-5 mt-1">68%</div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="border rounded p-3 h-100">
              <div class="small text-muted">4-Star Reviews</div>
              <div class="fw-bold fs-5 mt-1">22%</div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="border rounded p-3 h-100">
              <div class="small text-muted">3-Star or Below</div>
              <div class="fw-bold fs-5 mt-1">10%</div>
            </div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle table-sm mb-0">
            <thead class="table-light">
              <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Rating</th>
                <th>Comment</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>#ORD-1082</td>
                <td>Maria Santos</td>
                <td><span class="badge bg-success">5.0</span></td>
                <td>Fast delivery and delicious food. Highly recommended.</td>
                <td>Oct 21, 2026</td>
              </tr>

              <tr>
                <td>#ORD-1081</td>
                <td>Juan Dela Cruz</td>
                <td><span class="badge bg-warning text-dark">4.5</span></td>
                <td>Good taste, but packaging could be improved.</td>
                <td>Oct 20, 2026</td>
              </tr>

              <tr>
                <td>#ORD-1080</td>
                <td>Ana Reyes</td>
                <td><span class="badge bg-success">5.0</span></td>
                <td>Very satisfying meal and excellent service.</td>
                <td>Oct 19, 2026</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include '../includes/admin_footer.php'; ?>
