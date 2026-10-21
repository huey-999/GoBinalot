<!-- ==========================================================================
       ========================= FOOTER SECTION =================================
       ========================================================================== -->
  <footer class="pt-5 pb-4">
    <div class="container">
      <div class="row g-4 mb-4">
        <div class="col-lg-4 col-md-6">
          <h5 class="fw-bold text-success"><i class="fa-solid fa-leaf me-2"></i>Binalot sa Bukid</h5>
          <p class="small mb-3 footer-text">Serving delicious, authentic Filipino comfort food wrapped in fresh banana leaves. See you at our bukid!</p>
          <div>
            <a href="#" class="social-icon" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
            <a href="#" class="social-icon" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
            <a href="#" class="social-icon" aria-label="Tiktok"><i class="fa-brands fa-tiktok"></i></a>
          </div>
        </div>

        <div class="col-lg-4 col-md-6">
          <h5 class="fw-bold">Opening Hours</h5>
          <ul class="list-unstyled small footer-text">
            <li class="mb-2"><strong class="text-white">Monday - Friday:</strong> 10:00 AM – 9:00 PM</li>
            <li class="mb-2"><strong class="text-white">Saturday - Sunday:</strong> 9:00 AM – 10:00 PM</li>
          </ul>
        </div>

        <div class="col-lg-4 col-md-12">
          <h5 class="fw-bold">Location & Contact</h5>
          <ul class="list-unstyled small footer-text">
            <li class="mb-2"><i class="fa-solid fa-location-dot me-2 text-warning"></i> National Highway, Bukid District, Laguna, Philippines</li>
            <li class="mb-2"><i class="fa-solid fa-phone me-2 text-warning"></i> +63 (0917) 123-4567</li>
            <li class="mb-2"><i class="fa-solid fa-envelope me-2 text-warning"></i> kainan@binalotsabukid.ph</li>
          </ul>
        </div>
      </div>

      <hr class="border-secondary my-4">

      <div class="row align-items-center small footer-text">
        <div class="col-md-6 text-center text-md-start">
          &copy; <span id="currentYear"></span> Binalot sa Bukid. All Rights Reserved.
        </div>
        <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
          <a href="#" class="text-light text-decoration-none me-3">Privacy Policy</a>
          <a href="#" class="text-light text-decoration-none">Terms & Conditions</a>
        </div>
      </div>
    </div>
  </footer>

  <!-- ==========================================================================
       ========================= GALLERY MODAL ====================================
       ========================================================================== -->
  <div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content border-0">
        <div class="modal-body p-0">
          <img id="modalGalleryImage" src="" alt="Gallery preview" class="img-fluid w-100">
        </div>
      </div>
    </div>
  </div>

  <!-- ==========================================================================
       ========================= SUCCESS MODAL ===================================
       ========================================================================== -->
  <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title text-success fw-bold" id="successModalLabel">Reservation Confirmed</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-3">Thank you, <strong id="confirmName">Guest</strong>! Your reservation is ready.</p>
          <ul class="list-unstyled small mb-0">
            <li><strong>Email:</strong> <span id="confirmEmail"></span></li>
            <li><strong>Date:</strong> <span id="confirmDate"></span></li>
            <li><strong>Time:</strong> <span id="confirmTime"></span></li>
            <li><strong>Guests:</strong> <span id="confirmGuests"></span></li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- ==========================================================================
       ========================= JAVASCRIPT LOGIC ===============================
       ========================================================================== -->
  <!-- Bootstrap 5 JS Bundle CDN -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/script.js"></script>

</body>
</html>