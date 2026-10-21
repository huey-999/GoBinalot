<?php include 'includes/header.php'; ?>

  <!-- ==========================================================================
       ========================= MAIN REGISTER SECTION ==========================
       ========================================================================== -->
  <main class="register-container">
    <div class="container d-flex justify-content-center">
      <div class="register-card">
        
        <!-- Header -->
        <div class="text-center mb-4">
          <h2 class="fw-bold text-success mt-1">Create an Account</h2>
          <p class="text-muted small mb-0">Join now and enjoy fast online food ordering!</p>
        </div>

        <!-- Alert Notification Box -->
        <div id="registerAlert" class="alert alert-danger d-none py-2 small" role="alert">
          Please fix the errors below before submitting.
        </div>

        <!-- Registration Form -->
        <form id="registerForm" class="needs-validation" novalidate>
          
          <!-- Full Name Input -->
          <div class="row g-3 mb-3">
            <div class="col-sm-6">
              <label for="firstName" class="form-label fw-semibold small">First Name</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                <input type="text" class="form-control" id="firstName" placeholder="Juan" required>
                <div class="invalid-feedback">First name is required.</div>
              </div>
            </div>
            <div class="col-sm-6">
              <label for="lastName" class="form-label fw-semibold small">Last Name</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                <input type="text" class="form-control" id="lastName" placeholder="Dela Cruz" required>
                <div class="invalid-feedback">Last name is required.</div>
              </div>
            </div>
          </div>

          <!-- Email Address Input -->
          <div class="mb-3">
            <label for="regEmail" class="form-label fw-semibold small">Email Address</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
              <input type="email" class="form-control" id="regEmail" placeholder="juan@example.com" required>
              <div class="invalid-feedback">Please enter a valid email address.</div>
            </div>
          </div>

          <!-- Mobile Number Input -->
          <div class="mb-3">
            <label for="regPhone" class="form-label fw-semibold small">Mobile Number</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
              <input type="tel" class="form-control" id="regPhone" placeholder="09171234567" pattern="^(09|\+639)\d{9}$" required>
              <div class="invalid-feedback">Please enter a valid PH mobile number (e.g. 09171234567).</div>
            </div>
          </div>

          <!-- Password Input -->
          <div class="mb-3">
            <label for="regPassword" class="form-label fw-semibold small">Password</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
              <input type="password" class="form-control" id="regPassword" placeholder="At least 8 characters" minlength="8" required>
              <button class="btn btn-outline-secondary" type="button" id="toggleRegPasswordBtn">
                <i class="fa-solid fa-eye" id="toggleRegPasswordIcon"></i>
              </button>
              <div class="invalid-feedback">Password must be at least 8 characters long.</div>
            </div>
            <!-- Password Strength Bar -->
            <div id="passwordStrength" class="password-strength-bar bg-secondary-subtle"></div>
          </div>

          <!-- Confirm Password Input -->
          <div class="mb-3">
            <label for="confirmPassword" class="form-label fw-semibold small">Confirm Password</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-shield-halved"></i></span>
              <input type="password" class="form-control" id="confirmPassword" placeholder="Re-enter password" required>
              <div class="invalid-feedback" id="confirmPasswordFeedback">Please confirm your password.</div>
            </div>
          </div>

          <!-- Terms and Conditions Checkbox -->
          <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="agreeTerms" required>
            <label class="form-check-label small text-muted" for="agreeTerms">
              I agree to the <a href="#" class="text-success fw-semibold">Terms of Service</a> and <a href="#" class="text-success fw-semibold">Privacy Policy</a>.
            </label>
            <div class="invalid-feedback">You must agree before submitting.</div>
          </div>

          <!-- Submit Button -->
          <button type="submit" class="btn btn-primary-custom w-100 py-2.5 text-uppercase fw-bold">
            <i class="fa-solid fa-user-plus me-2"></i>Create Account
          </button>
        </form>

        <!-- Login Link Footer -->
        <div class="text-center mt-4">
          <p class="small text-muted mb-0">
            Already have an account? 
            <a href="login.php" class="fw-bold text-success text-decoration-none">Sign In Here</a>
          </p>
        </div>

      </div>
    </div>
  </main>

<?php include 'includes/footer.php'; ?>