<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>
<script src="../js/script.js"></script>
<script src="../js/action.js"></script>

<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title fw-bold text-danger">Delete</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-3">
        <p class="mb-0 modal-message">Are you sure you want to delete this item?</p>
      </div>

      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger btn-sm btn-confirm-delete">Delete</button>
      </div>
    </div>
  </div>
</div>

<script>
  window.showDeleteConfirmation = function (message, callback, options = {}) {
    const $modal = $('#deleteConfirmModal');
    const $message = $modal.find('.modal-message');
    const $title = $modal.find('.modal-title');
    const $confirmButton = $modal.find('.btn-confirm-delete');

    $message.html(message || 'Are you sure you want to delete this item?');
    $title.text(options.title || 'Delete');
    $confirmButton.text(options.confirmText || 'Delete');
    $modal.data('delete-callback', callback || null);

    const modal = new bootstrap.Modal($modal[0]);
    modal.show();
  };

  $('#deleteConfirmModal .btn-confirm-delete').on('click', function () {
    const $modal = $('#deleteConfirmModal');
    const callback = $modal.data('delete-callback');

    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
    if (modalInstance) {
      modalInstance.hide();
    }

    if (typeof callback === 'function') {
      callback();
    }

    $modal.removeData('delete-callback');
  });

  document.addEventListener('click', function (event) {
    const logoutLink = event.target.closest('.logout-confirm');
    if (!logoutLink) return;

    event.preventDefault();
    window.showDeleteConfirmation('Are you sure you want to logout?', function () {
      window.location.href = logoutLink.href;
    }, {
      title: 'Logout',
      confirmText: 'Logout'
    });
  });

  document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery && $.fn.DataTable) {
      $('.datatable-table').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        info: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'All']],
        language: {
          search: '',
          searchPlaceholder: 'Search records...',
          lengthMenu: 'Show _MENU_',
          info: 'Showing _START_ to _END_ of _TOTAL_ entries',
          paginate: {
            previous: 'Prev',
            next: 'Next'
          }
        },
        initComplete: function () {
          const table = this.api();
          const $container = $(table.table().container());
          $container.find('.dataTables_filter input').addClass('form-control form-control-sm');
          $container.find('.dataTables_length select').addClass('form-select form-select-sm');
        }
      });
    }
  });
</script>
</body>
</html>