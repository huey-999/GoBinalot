if (typeof jQuery === 'undefined') {
  console.warn('jQuery is not loaded. Category AJAX handler skipped.');
} else {
  jQuery(function ($) {
    function refreshCategoryOptions() {
      $.ajax({
        url: '../includes/actions.php',
        type: 'POST',
        dataType: 'json',
        data: { form_type: 'get_categories' },
        success: function (response) {
          if (!response || !response.success || !Array.isArray(response.categories)) return;

          const categoryOptions = response.categories.map(function (category) {
            return '<option value="' + category.id + '">' + escapeHtml(category.name) + '</option>';
          }).join('');

          $('select[name="category_id"]').each(function () {
            const $select = $(this);
            const currentValue = $select.val();
            const placeholderText = $select.data('placeholder') || 'Select category';

            $select.html('<option value="">' + placeholderText + '</option>' + categoryOptions);

            if (currentValue) {
              $select.val(currentValue);
            }
          });
        }
      });
    }

    // Add Category
    const $addCategoryForm = $('#addCategoryModal form');
    const $categoryTableBody = $('#categories-tab-pane tbody');
    const $categoryTabButton = $('#categories-tab');

    $('select[name="category_id"]').attr('data-placeholder', 'Select category');

    refreshCategoryOptions();

    if (!$addCategoryForm.length) return;

    $addCategoryForm.on('submit', function (event) {
      event.preventDefault();

      const $submitButton = $(this).find('button[type="submit"]');
      const originalText = $submitButton.text();
      const categoryName = $(this).find('[name="category_name"]').val().trim();
      const statusChecked = $(this).find('[name="status"]').is(':checked');

      if (!categoryName) {
        alert('Please enter a category name.');
        return;
      }

      $submitButton.prop('disabled', true).text('Saving...');

      $.ajax({
        url: '../includes/actions.php',
        type: 'POST',
        dataType: 'json',
        data: {
          form_type: 'add_category',
          category_name: categoryName,
          status: statusChecked ? 1 : 0
        },
        success: function (response) {
          if (response && response.success) {
            const statusLabel = statusChecked ? 'Active' : 'Inactive';
            const badgeClass = statusChecked ? 'bg-success' : 'bg-secondary';

            const newRow = `
              <tr>
                <td class="fw-bold">${escapeHtml(categoryName)}</td>
                <td><span class="badge ${badgeClass}">${statusLabel}</span></td>
                <td>
                  <div class="d-flex flex-wrap gap-1">
                    <button type="button" class="btn btn-xs btn-outline-secondary btn-edit-category" data-category-id="${response.category.id}" data-category-name="${escapeHtml(categoryName)}" data-category-status="${statusChecked ? 'active' : 'inactive'}" title="Edit">
                      <i class="fa-solid fa-pen"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-danger btn-delete-category" data-category-id="${response.category.id}" data-category-name="${escapeHtml(categoryName)}" title="Delete">
                      <i class="fa-solid fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            `;

            if ($categoryTableBody.length) {
              $categoryTableBody.prepend(newRow);
            }

            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('addCategoryModal'));
            if (modalInstance) modalInstance.hide();

            $addCategoryForm[0].reset();

            if ($categoryTabButton.length) {
              $categoryTabButton.tab('show');
            }

            showToast(response.message || 'Category added successfully!', 'success');
            refreshMenuStats();
            refreshCategoryOptions();
            return;
          }

          showToast(response.message || 'Failed to add category.', 'danger');
        },
        error: function () {
          showToast('Something went wrong while adding the category.', 'danger');
        },
        complete: function () {
          $submitButton.prop('disabled', false).text(originalText);
        }
      });
    });

    // Edit Category — open modal and populate fields
    $categoryTableBody.on('click', '.btn-edit-category', function () {
      const $row = $(this).closest('tr');
      const $editForm = $('#editCategoryModal form');

      $editForm.find('[name="category_id"]').val($(this).data('category-id'));
      $editForm.find('[name="category_name"]').val($(this).data('category-name') || $row.find('td:first').text().trim());
      $editForm.find('[name="status"]').prop('checked', $(this).data('category-status') === 'active');

      // Remember which table row to update after save
      $editForm.data('target-row', $row);

      const editModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
      editModal.show();
    });

    // Edit Category form submit
    $('#editCategoryModal form').on('submit', function (event) {
      event.preventDefault();

      const $form = $(this);
      const $submitButton = $form.find('button[type="submit"]');
      const originalText = $submitButton.text();
      const categoryId = $form.find('[name="category_id"]').val();
      const categoryName = $form.find('[name="category_name"]').val().trim();
      const statusChecked = $form.find('[name="status"]').is(':checked');

      if (!categoryName) {
        alert('Please enter a category name.');
        return;
      }

      $submitButton.prop('disabled', true).text('Updating...');

      $.ajax({
        url: '../includes/actions.php',
        type: 'POST',
        dataType: 'json',
        data: {
          form_type: 'edit_category',
          category_id: categoryId,
          category_name: categoryName,
          status: statusChecked ? 1 : 0
        },
        success: function (response) {
          if (response && response.success) {
            const statusLabel = statusChecked ? 'Active' : 'Inactive';
            const badgeClass = statusChecked ? 'bg-success' : 'bg-secondary';
            const $row = $form.data('target-row');

            if ($row && $row.length) {
              $row.find('td:first').text(categoryName);
              $row.find('td:nth-child(2)').html(`<span class="badge ${badgeClass}">${escapeHtml(statusLabel)}</span>`);
              $row.find('.btn-edit-category')
                .data('category-name', categoryName)
                .data('category-status', statusChecked ? 'active' : 'inactive');
              $row.find('.btn-delete-category').data('category-name', categoryName);
            }

            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('editCategoryModal'));
            if (modalInstance) modalInstance.hide();
            $form[0].reset();
            $form.removeData('target-row');

            showToast(response.message || 'Category updated successfully!', 'success');
            refreshMenuStats();
            refreshCategoryOptions();
            return;
          }

          showToast(response.message || 'Failed to update category.', 'danger');
        },
        error: function () {
          showToast('Something went wrong while updating the category.', 'danger');
        },
        complete: function () {
          $submitButton.prop('disabled', false).text(originalText);
        }
      });
    });

    // Delete Category
    $categoryTableBody.on('click', '.btn-delete-category', function () {
      const categoryId = $(this).data('category-id');
      const categoryName = $(this).data('category-name') || 'this category';
      const $row = $(this).closest('tr');

      showDeleteConfirmation(`Are you sure you want to delete "${escapeHtml(categoryName)}"?`, function () {
        $.ajax({
          url: '../includes/actions.php',
          type: 'POST',
          dataType: 'json',
          data: {
            form_type: 'delete_category',
            category_id: categoryId
          },
          success: function (response) {
            if (response && response.success) {
              $row.remove();
              if ($categoryTableBody.find('tr').length === 0) {
                $categoryTableBody.html('<tr><td colspan="3" class="text-center text-muted small">No categories found.</td></tr>');
              }
              showToast(response.message || 'Category deleted successfully!', 'success');
              refreshMenuStats();
              refreshCategoryOptions();
            } else {
              showToast(response.message || 'Failed to delete category.', 'danger');
            }
          },
          error: function () {
            showToast('Something went wrong while deleting the category.', 'danger');
          }
        });
      }, {
        title: 'Delete Category',
        confirmText: 'Delete'
      });
    });

    // Add Menu Item
    const $addMenuItemForm = $('#addMenuItemForm');
    const $editMenuItemForm = $('#editMenuItemForm');

    if ($addMenuItemForm.length) {
      $addMenuItemForm.on('submit', function (event) {
        event.preventDefault();

        const $submitButton = $(this).find('button[type="submit"]');
        const originalText = $submitButton.text();
        const menuName = $(this).find('[name="menu_name"]').val().trim();
        const categoryId = $(this).find('[name="category_id"]').val();
        const price = $(this).find('[name="price"]').val();
        const description = $(this).find('[name="description"]').val().trim();
        const isAvailable = $(this).find('[name="is_available"]').is(':checked');

        if (!menuName || !categoryId || !price) {
          alert('Please fill in the menu name, category, and price.');
          return;
        }

        $submitButton.prop('disabled', true).text('Saving...');

        const formData = new FormData(this);
        formData.append('form_type', 'add_menu_item');

        $.ajax({
          url: '../includes/actions.php',
          type: 'POST',
          dataType: 'json',
          data: formData,
          processData: false,
          contentType: false,
          success: function (response) {
            if (response && response.success) {
              const menuImageName = response.menu_item && response.menu_item.image ? response.menu_item.image : '';
              const newRow = `
                <tr data-menu-id="${response.menu_item.id}">
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width:38px;height:38px;">
                        ${menuImageName ? '<img src="../img/menu/' + escapeHtml(menuImageName) + '" alt="Menu item" style="width:100%;height:100%;object-fit:cover;border-radius:6px;">' : '<i class="fa-solid fa-bowl-food text-success"></i>'}
                      </div>
                      <div>
                        <div class="fw-semibold">${escapeHtml(menuName)}</div>
                      </div>
                    </div>
                  </td>
                  <td>${escapeHtml(getCategoryNameFromSelect(categoryId))}</td>
                  <td>₱${Number(price).toFixed(2)}</td>
                  <td class="text-muted small">${escapeHtml(description || 'No description provided.')}</td>
                  <td><span class="badge ${isAvailable ? 'bg-success' : 'bg-secondary'}">${isAvailable ? 'Available' : 'Unavailable'}</span></td>
                  <td>
                    <div class="d-flex flex-wrap gap-1">
                      <button type="button" class="btn btn-xs btn-outline-secondary btn-edit-menu-item"
                              data-menu-id="${response.menu_item.id}"
                              data-menu-name="${escapeHtml(menuName)}"
                              data-menu-category-id="${categoryId}"
                              data-menu-price="${price}"
                              data-menu-description="${escapeHtml(description)}"
                              data-menu-status="${isAvailable ? 'Y' : 'N'}"
                              title="Edit"><i class="fa-solid fa-pen"></i></button>
                      <button type="button" class="btn btn-xs btn-outline-danger btn-delete-menu-item" data-menu-id="${response.menu_item.id}" data-menu-name="${escapeHtml(menuName)}" title="Delete"><i class="fa-solid fa-trash"></i></button>
                    </div>
                  </td>
                </tr>
              `;

              const $tableBody = $('#menu-items-tab-pane tbody');
              if ($tableBody.length) {
                $tableBody.prepend(newRow);
              }

              const modalInstance = bootstrap.Modal.getInstance(document.getElementById('addMenuItemModal'));
              if (modalInstance) modalInstance.hide();

              $addMenuItemForm[0].reset();
              $(this).find('[name="is_available"]').prop('checked', true);

              showToast(response.message || 'Menu item added successfully!', 'success');
              refreshMenuStats();
              refreshCategoryOptions();
              return;
            }

            showToast(response.message || 'Failed to add menu item.', 'danger');
          },
          error: function () {
            showToast('Something went wrong while adding the menu item.', 'danger');
          },
          complete: function () {
            $submitButton.prop('disabled', false).text(originalText);
          }
        });
      });
    }

    // Edit Menu Item
    $(document).on('click', '.btn-edit-menu-item', function () {
      const $button = $(this);
      const $form = $('#editMenuItemForm');
      const currentImage = $button.data('menu-image') || '';

      $form.find('[name="menu_id"]').val($button.data('menu-id'));
      $form.find('[name="menu_name"]').val($button.data('menu-name') || '');
      $form.find('[name="category_id"]').val($button.data('menu-category-id'));
      $form.find('[name="price"]').val($button.data('menu-price') || '');
      $form.find('[name="description"]').val($button.data('menu-description') || '');
      $form.find('[name="is_available"]').prop('checked', $button.data('menu-status') === 'Y');
      $form.data('target-row', $button.closest('tr'));
      $form.data('current-image', currentImage);

      const editModal = new bootstrap.Modal(document.getElementById('editMenuItemModal'));
      editModal.show();
    });

    if ($editMenuItemForm.length) {
      $editMenuItemForm.on('submit', function (event) {
        event.preventDefault();

        const $submitButton = $(this).find('button[type="submit"]');
        const originalText = $submitButton.text();
        const menuId = $(this).find('[name="menu_id"]').val();
        const menuName = $(this).find('[name="menu_name"]').val().trim();
        const categoryId = $(this).find('[name="category_id"]').val();
        const price = $(this).find('[name="price"]').val();
        const description = $(this).find('[name="description"]').val().trim();
        const isAvailable = $(this).find('[name="is_available"]').is(':checked');

        if (!menuId || !menuName || !categoryId || !price) {
          alert('Please fill in the required menu item details.');
          return;
        }

        $submitButton.prop('disabled', true).text('Updating...');

        const formData = new FormData(this);
        formData.append('form_type', 'edit_menu_item');

        const currentImage = $editMenuItemForm.data('current-image') || '';
        if (!formData.get('image') || !(formData.get('image') instanceof File) || !formData.get('image').name) {
          if (currentImage) {
            formData.append('keep_existing_image', currentImage);
          }
        }

        $.ajax({
          url: '../includes/actions.php',
          type: 'POST',
          dataType: 'json',
          data: formData,
          processData: false,
          contentType: false,
          success: function (response) {
            if (response && response.success) {
              const $row = $editMenuItemForm.data('target-row');
              const categoryName = getCategoryNameFromSelect(categoryId);
              const statusText = isAvailable ? 'Available' : 'Unavailable';
              const statusClass = isAvailable ? 'bg-success' : 'bg-secondary';
              const imageName = response.menu_item && response.menu_item.image ? response.menu_item.image : currentImage;

              if ($row && $row.length) {
                const imageHtml = imageName
                  ? '<img src="../img/menu/' + escapeHtml(imageName) + '" alt="Menu item" style="width:38px;height:38px;object-fit:cover;border-radius:6px;">'
                  : '<i class="fa-solid fa-bowl-food text-success"></i>';

                $row.find('td:nth-child(1) .rounded').html(imageHtml);
                $row.find('td:nth-child(1) .fw-semibold').text(menuName);
                $row.find('td:nth-child(2)').text(categoryName);
                $row.find('td:nth-child(3)').text('₱' + Number(price).toFixed(2));
                $row.find('td:nth-child(4)').text(description || 'No description provided.');
                $row.find('td:nth-child(5)').html('<span class="badge ' + statusClass + '">' + statusText + '</span>');

                $row.find('.btn-edit-menu-item')
                  .data('menu-name', menuName)
                  .data('menu-category-id', categoryId)
                  .data('menu-price', price)
                  .data('menu-description', description)
                  .data('menu-status', isAvailable ? 'Y' : 'N')
                  .data('menu-image', imageName);

                $row.find('.btn-delete-menu-item').data('menu-name', menuName);
              }

              const modalInstance = bootstrap.Modal.getInstance(document.getElementById('editMenuItemModal'));
              if (modalInstance) modalInstance.hide();

              $editMenuItemForm[0].reset();
              $editMenuItemForm.removeData('target-row');
              $editMenuItemForm.removeData('current-image');

              showToast(response.message || 'Menu item updated successfully!', 'success');
              refreshMenuStats();
              return;
            }

            showToast(response.message || 'Failed to update menu item.', 'danger');
          },
          error: function () {
            showToast('Something went wrong while updating the menu item.', 'danger');
          },
          complete: function () {
            $submitButton.prop('disabled', false).text(originalText);
          }
        });
      });
    }

    // Delete Menu Item
    $(document).on('click', '.btn-delete-menu-item', function () {
      const menuId = $(this).data('menu-id');
      const menuName = $(this).data('menu-name') || 'this menu item';
      const $row = $(this).closest('tr');

      showDeleteConfirmation(`Are you sure you want to delete "${escapeHtml(menuName)}"?`, function () {
        $.ajax({
          url: '../includes/actions.php',
          type: 'POST',
          dataType: 'json',
          data: {
            form_type: 'delete_menu_item',
            menu_id: menuId
          },
          success: function (response) {
            if (response && response.success) {
              $row.remove();

              const $tableBody = $('#menu-items-tab-pane tbody');
              if ($tableBody.length && $tableBody.find('tr').length === 0) {
                $tableBody.html('<tr><td colspan="6" class="text-center text-muted small">No menu items found.</td></tr>');
              }

              showToast(response.message || 'Menu item deleted successfully!', 'success');
              refreshMenuStats();
            } else {
              showToast(response.message || 'Failed to delete menu item.', 'danger');
            }
          },
          error: function () {
            showToast('Something went wrong while deleting the menu item.', 'danger');
          }
        });
      }, {
        title: 'Delete Menu Item',
        confirmText: 'Delete'
      });
    });

    let pendingDelete = null;

    function showDeleteConfirmation(message, callback, options = {}) {
      const $modal = $('#deleteConfirmModal');
      const $message = $modal.find('.modal-message');
      const $title = $modal.find('.modal-title');
      const $confirmButton = $modal.find('.btn-confirm-delete');

      $message.html(message);
      $title.html(options.title || 'Confirm Delete');
      $confirmButton.text(options.confirmText || 'Confirm');

      pendingDelete = callback;

      const modal = new bootstrap.Modal($modal[0]);
      modal.show();
    }

    $('#deleteConfirmModal .btn-confirm-delete').on('click', function () {
      if (typeof pendingDelete === 'function') {
        const modalInstance = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
        if (modalInstance) modalInstance.hide();

        pendingDelete();
        pendingDelete = null;
      }
    });

    $('#deleteConfirmModal').on('hidden.bs.modal', function () {
      pendingDelete = null;
    });

    function showToast(message, type) {
      const toastClass = type === 'success' ? 'text-bg-success' : 'text-bg-danger';
      const toastHtml = `
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
          <div class="toast align-items-center ${toastClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
              <div class="toast-body text-white">${escapeHtml(message)}</div>
              <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
          </div>
        </div>
      `;

      $('body').append(toastHtml);
      const $toast = $('.toast:last');
      const toast = new bootstrap.Toast($toast[0], { delay: 3000 });
      toast.show();

      setTimeout(function () {
        $toast.remove();
      }, 3500);
    }

    function escapeHtml(text) {
      return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    function getCategoryNameFromSelect(categoryId) {
      const option = $('#addMenuItemModal select[name="category_id"] option[value="' + categoryId + '"]');
      if (option.length) return option.text();

      const editOption = $('#editMenuItemModal select[name="category_id"] option[value="' + categoryId + '"]');
      if (editOption.length) return editOption.text();

      return 'Uncategorized';
    }

    function refreshMenuStats() {
      const $statCards = $('#menu-items-tab-pane .stat-card h4');
      if (!$statCards.length) return;

      $.ajax({
        url: '../includes/actions.php',
        type: 'POST',
        dataType: 'json',
        data: {
          form_type: 'get_menu_stats'
        },
        success: function (response) {
          if (response && response.success) {
            const stats = response.stats || {};

            if ($statCards.length >= 4) {
              $statCards.eq(0).text(stats.total_items ?? 0);
              $statCards.eq(1).text(stats.available_items ?? 0);
              $statCards.eq(2).text(stats.unavailable_items ?? 0);
              $statCards.eq(3).text(stats.categories ?? 0);
            }
          }
        }
      });
    }
  });
}