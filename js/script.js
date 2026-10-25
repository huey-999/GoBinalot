document.addEventListener('DOMContentLoaded', function () {
  // Set Copyright Year Dynamically
  const currentYearEl = document.getElementById('currentYear');
  if (currentYearEl) {
    currentYearEl.textContent = new Date().getFullYear();
  }

  // Set minimum date for reservation form to today
  const dateInput = document.getElementById('resDate');
  if (dateInput) {
    const today = new Date().toISOString().split('T')[0];
    dateInput.setAttribute('min', today);
  }

  // Mobile sidebar toggle for admin pages
  const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
  const sidebar = document.getElementById('sidebar');
  const sidebarBackdrop = document.querySelector('.sidebar-backdrop');

  function setSidebarState(isOpen) {
    if (!sidebar) return;

    sidebar.classList.toggle('show', isOpen);

    if (sidebarBackdrop) {
      sidebarBackdrop.classList.toggle('show', isOpen);
      sidebarBackdrop.style.display = isOpen ? 'block' : 'none';
    }
  }

  if (sidebarToggleBtn) {
    sidebarToggleBtn.addEventListener('click', function () {
      if (!sidebar) return;
      const isOpen = !sidebar.classList.contains('show');
      setSidebarState(isOpen);
    });
  }

  if (sidebarBackdrop) {
    sidebarBackdrop.addEventListener('click', function () {
      setSidebarState(false);
    });
  }

  window.addEventListener('resize', function () {
    if (window.innerWidth > 991.98) {
      setSidebarState(false);
    }
  });

  /* ==========================================================================
     1. STICKY NAVBAR SHADOW & MOBILE NAV TOGGLE
     ========================================================================== */
  const navbar = document.getElementById('mainNavbar');
  const navLinks = document.querySelectorAll('.navbar-nav .nav-link:not(.dropdown-toggle)');
  const sections = document.querySelectorAll('section[id]');
  const navbarCollapse = document.getElementById('navbarContent');
  const navbarToggler = document.querySelector('.navbar-toggler');

  function setMobileMenuState(isOpen) {
    if (!navbarCollapse || !navbarToggler) return;

    navbarCollapse.classList.toggle('show', isOpen);
    navbarToggler.setAttribute('aria-expanded', String(isOpen));
    navbarToggler.classList.toggle('collapsed', !isOpen);
  }

  function setActiveNavLink(sectionId) {
    navLinks.forEach(link => {
      const isActive = link.getAttribute('href') === `#${sectionId}`;
      link.classList.toggle('active', isActive);
    });
  }

  if (navbarToggler && navbarCollapse) {
    navbarToggler.addEventListener('click', function () {
      const isCurrentlyOpen = navbarCollapse.classList.contains('show');
      const shouldOpen = !isCurrentlyOpen;

      if (window.innerWidth < 992) {
        setMobileMenuState(shouldOpen);
      }
    });
  }

  function updateActiveNavOnScroll() {
    if (navbar) {
      if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    }

    if (window.innerWidth < 992) return;

    let scrollPosition = window.scrollY + 120;
    let matchedSection = null;

    sections.forEach(section => {
      const sectionTop = section.offsetTop;
      const sectionHeight = section.offsetHeight;
      const sectionId = section.getAttribute('id');

      if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
        matchedSection = sectionId;
      }
    });

    if (matchedSection) {
      setActiveNavLink(matchedSection);
    }
  }

  window.addEventListener('scroll', updateActiveNavOnScroll);
  updateActiveNavOnScroll(); // Trigger initial check on page load

  navLinks.forEach(function (link) {
    link.addEventListener('click', function (event) {
      const href = link.getAttribute('href');

      if (href && href.startsWith('#')) {
        event.preventDefault();
        const target = document.querySelector(href);
        if (target) {
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }

      if (href && href.startsWith('#')) {
        const targetId = href.replace('#', '');
        setActiveNavLink(targetId);
      }

      if (window.innerWidth < 992 && navbarCollapse) {
        setMobileMenuState(false);
      }
    });
  });

  /* ==========================================================================
     2. INTERACTIVE MENU FILTERING SYSTEM
     ========================================================================== */
  const menuTabs = document.querySelectorAll('#menu-tabs .nav-link');
  const menuItems = document.querySelectorAll('.menu-item-card');
  const emptyState = document.getElementById('menu-empty-state');

  if (menuTabs.length && menuItems.length) {
    function updateMenuEmptyState(filterValue) {
      const visibleItems = Array.from(menuItems).filter(function (item) {
        const category = item.getAttribute('data-category');
        return filterValue === 'all' || filterValue === category;
      });

      if (emptyState) {
        emptyState.style.display = visibleItems.length ? 'none' : 'block';
      }
    }

    menuTabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        menuTabs.forEach(t => t.classList.remove('active'));
        this.classList.add('active');

        const filterValue = this.getAttribute('data-filter');

        menuItems.forEach(function (item) {
          const category = item.getAttribute('data-category');
          const show = filterValue === 'all' || filterValue === category;
          item.style.display = show ? 'block' : 'none';
        });

        updateMenuEmptyState(filterValue);
      });
    });

    updateMenuEmptyState('all');
  }

  /* ==========================================================================
     3. GALLERY LIGHTBOX MODAL POPUP
     ========================================================================== */
  const galleryModal = document.getElementById('galleryModal');
  const modalImage = document.getElementById('modalGalleryImage');

  if (galleryModal && modalImage) {
    galleryModal.addEventListener('show.bs.modal', function (event) {
      const triggerItem = event.relatedTarget;
      if (triggerItem) {
        const imgSrc = triggerItem.getAttribute('data-bs-img');
        modalImage.setAttribute('src', imgSrc);
      }
    });
  }

  /* ==========================================================================
     4. RESERVATION FORM VALIDATION & CONFIRMATION MODAL
     ========================================================================== */
  const resForm = document.getElementById('reservationForm');
  const successModalEl = document.getElementById('successModal');

  if (resForm) {
    resForm.addEventListener('submit', function (e) {
      e.preventDefault();

      if (!resForm.checkValidity()) {
        e.stopPropagation();
        resForm.classList.add('was-validated');
      } else {
        const name = document.getElementById('resName').value;
        const email = document.getElementById('resEmail').value;
        const date = document.getElementById('resDate').value;
        const time = document.getElementById('resTime').value;
        const guests = document.getElementById('resGuests').value;

        document.getElementById('confirmName').textContent = name;
        document.getElementById('confirmEmail').textContent = email;
        document.getElementById('confirmDate').textContent = date;
        document.getElementById('confirmTime').textContent = time;
        document.getElementById('confirmGuests').textContent = guests;

        if (successModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
          const successModal = bootstrap.Modal.getInstance(successModalEl) || new bootstrap.Modal(successModalEl);
          successModal.show();
        }

        resForm.reset();
        resForm.classList.remove('was-validated');
      }
    }, false);
  }

  const detailTriggers = document.querySelectorAll('[data-bs-target="#viewOrderModal"]');

  detailTriggers.forEach((trigger) => {
    trigger.addEventListener('click', function () {
      const modalEl = document.getElementById('viewOrderModal');
      if (!modalEl || typeof bootstrap === 'undefined') return;
      const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
    });
  });

  /* ==========================================================================
     4. REGISTRATION / LOGIN VALIDATION & PASSWORD STRENGTH
     ========================================================================== */
  const registerForm = document.getElementById('registerForm');
  const registerAlert = document.getElementById('registerAlert');
  const regPasswordInput = document.getElementById('regPassword');
  const confirmPasswordInput = document.getElementById('confirmPassword');
  const passwordStrengthBar = document.getElementById('passwordStrength');

  function getPasswordStrength(value) {
    if (!value) return '';

    let score = 0;

    if (value.length >= 8) score += 1;
    if (/[a-z]/.test(value) && /[A-Z]/.test(value)) score += 1;
    if (/\d/.test(value)) score += 1;
    if (/[^A-Za-z0-9]/.test(value)) score += 1;

    if (score <= 2) return 'weak';
    if (score === 3) return 'medium';
    return 'strong';
  }

  function updatePasswordStrengthMeter() {
    if (!regPasswordInput || !passwordStrengthBar) return;

    const strength = getPasswordStrength(regPasswordInput.value);
    passwordStrengthBar.setAttribute('data-strength', strength || '');
  }

  function applyValidationState(field, isValid) {
    if (!field) return;

    const inputGroup = field.closest('.input-group');
    const formCheck = field.closest('.form-check');
    const feedback = field.closest('.mb-3, .col-sm-6, .form-check')?.querySelector('.invalid-feedback');

    field.classList.toggle('is-invalid', !isValid);
    field.classList.toggle('is-valid', isValid && Boolean(field.value || field.checked));

    if (inputGroup) {
      inputGroup.classList.toggle('is-invalid', !isValid);
      inputGroup.classList.toggle('is-valid', isValid);
    }

    if (formCheck) {
      formCheck.classList.toggle('is-invalid', !isValid);
      formCheck.classList.toggle('is-valid', isValid);
    }

    if (feedback) {
      feedback.style.display = isValid ? 'none' : 'block';
    }
  }

  function setFieldValidity(field, message) {
    if (!field) return;
    field.setCustomValidity(message || '');
    applyValidationState(field, !message);
  }

  function validateRegisterField(field) {
    if (!field) return true;

    const value = field.value.trim();
    const fieldId = field.id;

    if (fieldId === 'firstName' || fieldId === 'lastName') {
      if (!value) {
        setFieldValidity(field, 'This field is required.');
        return false;
      }
      setFieldValidity(field, '');
      return true;
    }

    if (fieldId === 'regEmail') {
      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!value) {
        setFieldValidity(field, 'Email is required.');
        return false;
      }
      if (!emailPattern.test(value)) {
        setFieldValidity(field, 'Please enter a valid email address.');
        return false;
      }
      setFieldValidity(field, '');
      return true;
    }

    if (fieldId === 'regPhone') {
      const phonePattern = /^(09|\+639)\d{9}$/;
      if (!value) {
        setFieldValidity(field, 'Phone number is required.');
        return false;
      }
      if (!phonePattern.test(value)) {
        setFieldValidity(field, 'Please enter a valid PH mobile number (e.g. 09171234567).');
        return false;
      }
      setFieldValidity(field, '');
      return true;
    }

    if (fieldId === 'regPassword') {
      if (!value) {
        setFieldValidity(field, 'Password is required.');
        return false;
      }
      if (value.length < 8) {
        setFieldValidity(field, 'Password must be at least 8 characters long.');
        return false;
      }
      setFieldValidity(field, '');
      return true;
    }

    if (fieldId === 'confirmPassword') {
      if (!value) {
        setFieldValidity(field, 'Please confirm your password.');
        return false;
      }
      if (value !== regPasswordInput.value) {
        setFieldValidity(field, 'Passwords do not match.');
        return false;
      }
      setFieldValidity(field, '');
      return true;
    }

    if (fieldId === 'agreeTerms') {
      if (!field.checked) {
        setFieldValidity(field, 'You must agree before submitting.');
        return false;
      }
      setFieldValidity(field, '');
      return true;
    }

    return true;
  }

  function validateRegisterForm() {
    if (!registerForm) return true;

    let isValid = true;
    const fieldsToValidate = [
      document.getElementById('firstName'),
      document.getElementById('lastName'),
      document.getElementById('regEmail'),
      document.getElementById('regPhone'),
      document.getElementById('regPassword'),
      document.getElementById('confirmPassword'),
      document.getElementById('agreeTerms')
    ];

    fieldsToValidate.forEach(function (field) {
      if (!validateRegisterField(field)) {
        isValid = false;
      }
    });

    if (regPasswordInput && confirmPasswordInput && confirmPasswordInput.value && regPasswordInput.value !== confirmPasswordInput.value) {
      setFieldValidity(confirmPasswordInput, 'Passwords do not match.');
      isValid = false;
    }

    return isValid;
  }

  if (regPasswordInput) {
    regPasswordInput.addEventListener('input', function () {
      updatePasswordStrengthMeter();
      if (confirmPasswordInput && confirmPasswordInput.value) {
        validateRegisterField(confirmPasswordInput);
      }
      validateRegisterField(regPasswordInput);
    });
  }

  if (confirmPasswordInput) {
    confirmPasswordInput.addEventListener('input', function () {
      validateRegisterField(confirmPasswordInput);
    });
  }

  if (registerForm) {
    registerForm.addEventListener('submit', function (event) {
      event.preventDefault();
      registerForm.classList.remove('was-validated');
      registerForm.classList.add('was-validated');

      if (!validateRegisterForm()) {
        if (registerAlert) {
          registerAlert.textContent = 'Please fix the errors below before submitting.';
          registerAlert.classList.remove('d-none');
        }

        const firstInvalidField = registerForm.querySelector(':invalid, .is-invalid');
        if (firstInvalidField) {
          firstInvalidField.focus();
        }
        return;
      }

      if (registerAlert) {
        registerAlert.classList.add('d-none');
      }

      registerForm.submit();
    });

    registerForm.querySelectorAll('input').forEach(function (input) {
      input.addEventListener('input', function () {
        if (!registerForm.classList.contains('was-validated')) return;
        validateRegisterField(this);
      });

      input.addEventListener('blur', function () {
        if (!registerForm.classList.contains('was-validated')) return;
        validateRegisterField(this);
      });
    });
  }

  if (passwordStrengthBar) {
    passwordStrengthBar.setAttribute('data-strength', '');
  }

  const loginForm = document.getElementById('loginForm');
  const loginAlert = document.getElementById('loginAlert');
  const loginEmailInput = document.getElementById('loginEmail');
  const loginPasswordInput = document.getElementById('loginPassword');

  function validateLoginField(field) {
    if (!field) return true;

    const fieldId = field.id;
    const value = field.value.trim();

    if (fieldId === 'loginEmail') {
      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      const phonePattern = /^(09|\+639)\d{9}$/;

      if (!value) {
        setFieldValidity(field, 'Email is required.');
        return false;
      }

      if (!emailPattern.test(value) && !phonePattern.test(value)) {
        setFieldValidity(field, 'Please enter a valid email or phone number.');
        return false;
      }

      setFieldValidity(field, '');
      return true;
    }

    if (fieldId === 'loginPassword') {
      if (!value) {
        setFieldValidity(field, 'Password is required.');
        return false;
      }

      if (value.length < 8) {
        setFieldValidity(field, 'Password must be at least 8 characters long.');
        return false;
      }

      setFieldValidity(field, '');
      return true;
    }

    return true;
  }

  function validateLoginForm() {
    if (!loginForm) return true;

    let isValid = true;
    const fieldsToValidate = [loginEmailInput, loginPasswordInput];

    fieldsToValidate.forEach(function (field) {
      if (!validateLoginField(field)) {
        isValid = false;
      }
    });

    return isValid;
  }

  if (loginEmailInput) {
    loginEmailInput.addEventListener('input', function () {
      if (loginForm && loginForm.classList.contains('was-validated')) {
        validateLoginField(this);
      }
    });
  }

  if (loginPasswordInput) {
    loginPasswordInput.addEventListener('input', function () {
      if (loginForm && loginForm.classList.contains('was-validated')) {
        validateLoginField(this);
      }
    });
  }

  if (loginForm) {
    loginForm.addEventListener('submit', function (event) {
      event.preventDefault();
      loginForm.classList.remove('was-validated');
      loginForm.classList.add('was-validated');

      if (!validateLoginForm()) {
        if (loginAlert) {
          loginAlert.textContent = 'Invalid email address or password. Please try again.';
          loginAlert.classList.remove('d-none');
        }
        const invalidField = loginForm.querySelector(':invalid, .is-invalid');
        if (invalidField) invalidField.focus();
        return;
      }

      if (loginAlert) {
        loginAlert.classList.add('d-none');
      }

      loginForm.submit();
    });
  }

  const togglePasswordBtn = document.getElementById('togglePasswordBtn');
  const togglePasswordIcon = document.getElementById('togglePasswordIcon');
  const toggleRegPasswordBtn = document.getElementById('toggleRegPasswordBtn');
  const toggleRegPasswordIcon = document.getElementById('toggleRegPasswordIcon');

  function togglePasswordVisibility(button, icon, inputId) {
    const input = document.getElementById(inputId);
    if (!button || !icon || !input) return;

    const isPasswordVisible = input.type === 'text';
    input.type = isPasswordVisible ? 'password' : 'text';
    icon.classList.toggle('fa-eye', isPasswordVisible);
    icon.classList.toggle('fa-eye-slash', !isPasswordVisible);
    button.setAttribute('aria-label', isPasswordVisible ? 'Show password' : 'Hide password');
  }

  if (togglePasswordBtn && togglePasswordIcon) {
    togglePasswordBtn.addEventListener('click', function () {
      togglePasswordVisibility(togglePasswordBtn, togglePasswordIcon, 'loginPassword');
    });
  }

  if (toggleRegPasswordBtn && toggleRegPasswordIcon) {
    toggleRegPasswordBtn.addEventListener('click', function () {
      togglePasswordVisibility(toggleRegPasswordBtn, toggleRegPasswordIcon, 'regPassword');
    });
  }

  /* ==========================================================================
     5. EXISTING FEATURE LOGIC CONTINUES
     ========================================================================== */
});