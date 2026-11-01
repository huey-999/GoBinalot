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
     5. CART FUNCTIONALITY (Binalot Menus Page)
     ========================================================================== */
  const cart = [];
  const cartItemsContainer = document.getElementById('cartItems');
  const subtotalValue = document.getElementById('subtotalValue');
  const deliveryValue = document.getElementById('deliveryValue');
  const serviceValue = document.getElementById('serviceValue');
  const totalValue = document.getElementById('totalValue');
  const cartCount = document.getElementById('cartCount');
  const toastEl = document.getElementById('addToCartToast');
  const toastItemName = document.getElementById('toastItemName');

  function formatMoney(value) {
    return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(value);
  }

  function showAddedToast(itemName) {
    if (!toastEl || !toastItemName) return;
    toastItemName.textContent = itemName;
    const toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 2500 });
    toast.show();
  }

  function renderCart() {
    if (!cartItemsContainer) return;
    const subtotal = cart.reduce((sum, item) => sum + item.price * item.qty, 0);
    const delivery = subtotal > 500 ? 0 : 49;
    const serviceFee = subtotal > 0 ? 15 : 0;
    const total = subtotal + delivery + serviceFee;
    if (subtotalValue) subtotalValue.textContent = formatMoney(subtotal);
    if (deliveryValue) deliveryValue.textContent = formatMoney(delivery);
    if (serviceValue) serviceValue.textContent = formatMoney(serviceFee);
    if (totalValue) totalValue.textContent = formatMoney(total);
    if (cartCount) cartCount.textContent = cart.reduce((sum, item) => sum + item.qty, 0);
    const checkoutItemsCount = document.getElementById('checkoutItemsCount');
    const checkoutDelivery = document.getElementById('checkoutDelivery');
    const checkoutTotal = document.getElementById('checkoutTotal');
    const btnCheckout = document.getElementById('btnCheckout');
    const checkoutItemList = document.getElementById('checkoutItemList');
    if (checkoutItemsCount) checkoutItemsCount.textContent = cart.reduce((sum, item) => sum + item.qty, 0);
    if (checkoutItemList) {
      checkoutItemList.innerHTML = cart.map(item => `<div class="checkout-item-row"><span>${item.name} x${item.qty}</span><strong>${formatMoney(item.price*item.qty)}</strong></div>`).join('');
    }
    if (checkoutDelivery) checkoutDelivery.textContent = formatMoney(delivery);
    if (checkoutTotal) checkoutTotal.textContent = formatMoney(total);
    if (btnCheckout) {
      if (cart.length === 0) btnCheckout.setAttribute('disabled', 'disabled');
      else btnCheckout.removeAttribute('disabled');
    }
    if (cart.length === 0) {
      cartItemsContainer.innerHTML = `<div class="empty-cart"><i class="fa-solid fa-basket-shopping"></i><p>Your cart is empty</p><p class="empty-subtitle">Add some favorite meals to get started.</p></div>`;
      return;
    }
    cartItemsContainer.innerHTML = cart.map(item => `<div class="cart-item" data-name="${item.name}"><div class="cart-item-thumb"><img src="${item.image}" alt="${item.name}" /></div><div class="cart-item-body"><div class="cart-item-head"><h4>${item.name}</h4><button type="button" class="remove-item" data-name="${item.name}">Remove</button></div><div class="cart-item-meta"><span>${formatMoney(item.price)} each</span><div class="mini-qty"><button type="button" data-action="minus" data-name="${item.name}">-</button><span>${item.qty}</span><button type="button" data-action="plus" data-name="${item.name}">+</button></div></div></div></div>`).join('');
  }

  function addToCart(name, price, image, qty = 1) {
    const existingItem = cart.find(item => item.name === name);
    if (existingItem) { existingItem.qty += qty; } else { cart.push({ name, price, image, qty }); }
    renderCart();
    showAddedToast(name);
  }

  const openCheckoutBtn = document.getElementById('openCheckoutBtn');
  const checkoutModalEl = document.getElementById('checkoutModal');
  if (openCheckoutBtn && checkoutModalEl && typeof bootstrap !== 'undefined') {
    openCheckoutBtn.addEventListener('click', function (e) {
      if (cart.length === 0) {
        e.preventDefault();
        e.stopImmediatePropagation();
        return;
      }
      const modal = bootstrap.Modal.getInstance(checkoutModalEl) || new bootstrap.Modal(checkoutModalEl);
      modal.show();
    });
  }

  let currentFilter = 'all';
  let currentSearch = '';

  function applyMenuFilterAndSearch() {
    const q = currentSearch;
    let visibleCount = 0;
    document.querySelectorAll('.menu-order-card').forEach(function (card) {
      const cat = card.getAttribute('data-category');
      const matchFilter = currentFilter === 'all' || cat === currentFilter;
      const name = (card.querySelector('h3')?.textContent || '').toLowerCase();
      const desc = (card.querySelector('p')?.textContent || '').toLowerCase();
      const matchSearch = !q || name.includes(q) || desc.includes(q);
      const show = matchFilter && matchSearch;
      card.style.display = show ? '' : 'none';
      if (show) visibleCount++;
    });
    updateMenuEmptyState(visibleCount > 0);
  }

  function updateMenuEmptyState(visible) {
    let empty = document.getElementById('menuEmptyState');
    if (!empty) {
      empty = document.createElement('div');
      empty.id = 'menuEmptyState';
      empty.className = 'empty-menu-state';
      empty.innerHTML = '<div class="empty-icon"><i class="fa-solid fa-utensils"></i></div><p>No menu items found.</p>';
      document.getElementById('menuGrid')?.appendChild(empty);
    }
    empty.style.display = visible ? 'none' : 'block';
  }

  const searchInput = document.querySelector('.search-box input');
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      currentSearch = this.value.toLowerCase();
      applyMenuFilterAndSearch();
    });
  }

  document.querySelectorAll('.filter-pill').forEach(function (pill) {
    pill.addEventListener('click', function () {
      document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
      this.classList.add('active');
      currentFilter = this.getAttribute('data-filter');
      applyMenuFilterAndSearch();
    });
  });

  applyMenuFilterAndSearch();

  // Add to cart buttons
  document.querySelectorAll('.add-to-cart-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const name = this.getAttribute('data-name');
      const price = parseFloat(this.getAttribute('data-price'));
      const image = this.getAttribute('data-image');
      const qtyEl = this.closest('.menu-order-card').querySelector('.qty-value');
      const qty = qtyEl ? parseInt(qtyEl.textContent, 10) || 1 : 1;
      addToCart(name, price, image, qty);
    });
  });

  // Qty controls
  document.querySelectorAll('.qty-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const card = this.closest('.menu-order-card');
      const valEl = card.querySelector('.qty-value');
      let val = parseInt(valEl.textContent, 10) || 1;
      if (this.getAttribute('data-action') === 'increase') val++;
      else if (val > 1) val--;
      valEl.textContent = val;
    });
  });

  // Cart item controls
  if (cartItemsContainer) {
    cartItemsContainer.addEventListener('click', function (e) {
      const target = e.target.closest('button');
      if (!target) return;
      const name = target.getAttribute('data-name');
      if (target.classList.contains('remove-item')) {
        const idx = cart.findIndex(i => i.name === name);
        if (idx > -1) { cart.splice(idx, 1); renderCart(); }
      } else if (target.getAttribute('data-action') === 'plus') {
        const item = cart.find(i => i.name === name);
        if (item) { item.qty++; renderCart(); }
      } else if (target.getAttribute('data-action') === 'minus') {
        const item = cart.find(i => i.name === name);
        if (item && item.qty > 1) { item.qty--; renderCart(); }
        else if (item) { const idx = cart.indexOf(item); cart.splice(idx,1); renderCart(); }
      }
    });
  }

  const btnCheckout = document.getElementById('btnCheckout');
  if (btnCheckout) {
    btnCheckout.addEventListener('click', function (e) {
      if (cart.length === 0) {
        e.preventDefault();
        e.stopPropagation();
      }
    });
  }

  // Select a saved address from the radio buttons in checkout modal
  window.selectSavedAddress = function(address) {
    const phoneInput = document.querySelector('input[type="tel"]');
    if (phoneInput && address.contact_number) {
      phoneInput.value = address.contact_number;
    }
    const hiddenId = document.getElementById('selectedAddressId');
    const hiddenText = document.getElementById('selectedAddressText');
    const hiddenPhone = document.getElementById('selectedAddressPhone');
    if (hiddenId) hiddenId.value = address.id;
    if (hiddenText) hiddenText.value = address.address;
    if (hiddenPhone) hiddenPhone.value = address.contact_number || '';
  };

});