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

  if (menuTabs.length && menuItems.length) {
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
      });
    });
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

  // Admin sidebar handling
  const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
  const sidebar = document.getElementById('sidebar');
  const sidebarBackdrop = document.querySelector('.sidebar-backdrop');
  const sidebarNavLinks = document.querySelectorAll('.sidebar .nav-link');
  const adminTabs = document.querySelectorAll('.sidebar .nav-link[data-bs-toggle="tab"]');

  function toggleSidebar(show) {
    if (!sidebar || !sidebarBackdrop) return;

    if (show) {
      sidebar.classList.add('show');
      sidebarBackdrop.classList.add('show');
      document.body.style.overflow = 'hidden';
    } else {
      sidebar.classList.remove('show');
      sidebarBackdrop.classList.remove('show');
      document.body.style.overflow = '';
    }
  }

  if (sidebarToggleBtn) {
    sidebarToggleBtn.addEventListener('click', function () {
      const isOpen = sidebar && sidebar.classList.contains('show');
      toggleSidebar(!isOpen);
    });
  }

  if (sidebarBackdrop) {
    sidebarBackdrop.addEventListener('click', function () {
      toggleSidebar(false);
    });
  }

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && sidebar && sidebar.classList.contains('show')) {
      toggleSidebar(false);
    }
  });

  if (sidebarNavLinks.length > 0) {
    const currentPage = window.location.pathname.split('/').pop() || 'dashboard.php';

    sidebarNavLinks.forEach(link => {
      const href = (link.getAttribute('href') || '').split('?')[0].split('#')[0];
      link.classList.remove('active');

      if (href && href.endsWith(currentPage)) {
        link.classList.add('active');
      }
    });
  }

  if (adminTabs.length > 0) {
    adminTabs.forEach(tab => {
      tab.addEventListener('shown.bs.tab', function (e) {
        sidebarNavLinks.forEach(l => l.classList.remove('active'));
        e.target.classList.add('active');

        if (window.innerWidth < 992) {
          toggleSidebar(false);
        }
      });
    });
  }
});