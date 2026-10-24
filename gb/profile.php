<?php include '../includes/admin_header.php'; ?>
<?php $isCustomer = ($_SESSION['user_data']['role'] ?? '') === 'customer'; ?>

<main class="main-content">
  <div class="tab-content">
    <div class="tab-pane fade show active" id="profile-tab">

      <div class="page-header d-flex justify-content-between align-items-center">
        <div>
          <h1 class="dashboard-heading">Profile</h1>
          <p class="page-subtitle">Update your personal information.</p>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-12">
          <div class="table-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h4 class="fw-bold text-success mb-0">Profile Details</h4>
            </div>
            <form class="settings-form">
              <div class="d-flex align-items-center gap-3 mb-3">
                <div class="profile-avatar-wrap">
                  <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80" alt="Customer avatar">
                  <button type="button" class="avatar-edit-btn" aria-label="Change profile picture">
                    <i class="fa-solid fa-camera"></i>
                  </button>
                </div>
                <div>
                  <h4 class="mb-1 text-success">Maria Santos</h4>
                  <p class="text-muted mb-0 small">Premium customer</p>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label" for="firstName">First name</label>
                  <input class="form-control" id="firstName" type="text" value="Maria" />
                </div>

                <div class="col-md-6">
                  <label class="form-label" for="lastName">Last name</label>
                  <input class="form-control" id="lastName" type="text" value="Santos" />
                </div>

                <div class="col-12">
                  <label class="form-label" for="email">Email</label>
                  <input class="form-control" id="email" type="email" value="maria.santos@email.com" />
                </div>

                <div class="col-12">
                  <label class="form-label" for="phone">Phone</label>
                  <input class="form-control" id="phone" type="tel" value="0917 456 8890" />
                </div>

                <div class="col-12">
                  <label class="form-label" for="birthday">Birthday</label>
                  <input class="form-control" id="birthday" type="date" value="1998-06-15" />
                </div>

                <div class="col-12">
                  <label class="form-label" for="gender">Gender</label>
                  <select class="form-select" id="gender">
                    <option selected>Female</option>
                    <option>Male</option>
                    <option>Prefer not to say</option>
                  </select>
                </div>
              </div>

              <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary-custom px-4 py-2 rounded-pill">Save Changes</button>
                <button type="button" class="btn btn-outline-secondary px-4 py-2 rounded-pill">Cancel</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include '../includes/admin_footer.php'; ?>
