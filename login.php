<?php 

include 'includes/header.php';
include 'includes/auth.php';

redirectIfAuthenticated('gb/dashboard.php');

?>

  <!-- ==========================================================================
       ========================= MAIN LOGIN SECTION =============================
       ========================================================================== -->
  <main class="login-container">
    <div class="container d-flex justify-content-center">
      <div class="login-card">
        
        <!-- Header -->
        <div class="text-center mb-4">
          <h2 class="fw-bold text-success mt-1">Welcome Back</h2>
          <p class="text-muted small mb-0">Sign in to view your orders!</p>
        </div>

        <!-- Alert Notification Box -->
        <div id="loginAlert" class="alert alert-danger d-none py-2 small" role="alert">
          Invalid email address or password. Please try again.
        </div>

        <!-- Login Form -->
        <form action="includes/actions.php" method="post" id="loginForm" class="needs-validation" novalidate>
          <input type="hidden" name="form_type" value="login">
          
          <!-- Email / Mobile Input -->
          <div class="mb-3">
            <!-- <label for="loginEmail" class="form-label fw-semibold small">Email Address or Mobile</label> -->
            <label for="loginEmail" class="form-label fw-semibold small">Email Address</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
              <input type="text" class="form-control" id="loginEmail" name="loginEmail" placeholder="juan@example.com" required>
            </div>
            <div class="invalid-feedback">Please enter a valid email or phone number.</div>
          </div>

          <!-- Password Input -->
          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label for="loginPassword" class="form-label fw-semibold small mb-0">Password</label>
              <a href="#" class="small text-decoration-none text-success">Forgot Password?</a>
            </div>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
              <input type="password" class="form-control" id="loginPassword" name="loginPassword" placeholder="••••••••" required>
              <button class="btn btn-outline-secondary" type="button" id="togglePasswordBtn">
                <i class="fa-solid fa-eye" id="togglePasswordIcon"></i>
              </button>
            </div>
            <div class="invalid-feedback">Please enter your password.</div>
          </div>

          <!-- Remember Me Checkbox -->
          <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="rememberMe">
            <label class="form-check-label small text-muted" for="rememberMe">
              Remember me on this device
            </label>
          </div>

          <!-- Submit Button -->
          <button type="submit" name="login" class="btn btn-primary-custom w-100 py-2.5 text-uppercase fw-bold">
            <i class="fa-solid fa-right-to-bracket me-2"></i>Sign In
          </button>
        </form>

        <!-- Register Link Footer -->
        <div class="text-center mt-4">
          <p class="small text-muted mb-0">
            Don't have an account yet? 
            <a href="register.php" class="fw-bold text-success text-decoration-none">Create Account</a>
          </p>
        </div>

      </div>
    </div>
  </main>

<?php include 'includes/footer.php'; ?>