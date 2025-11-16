/**
 * Profile Page Main Script
 * Handles table initialization, filtering, and modal operations
 */

const bootstrap = window.bootstrap;

let $table;
let currentTypeFilter = 'all';
let currentStatusFilter = 'all';
let tableInitialized = false;

// Initialize submissions table, search and sorting
(function() {
  function initTable() {
    if (tableInitialized) return;

    $table = $('#submissionsTable');
    if (!$table.length || typeof $table.bootstrapTable !== 'function') return;

    if ($table.data('bootstrap.table')) {
      $table.bootstrapTable('destroy');
    }

    $table.bootstrapTable();
    tableInitialized = true;

    const $search = document.getElementById('tableSearch');
    if ($search) {
      $search.addEventListener('input', function (e) {
        $table.bootstrapTable('resetSearch', e.target.value);
      });
    }

    setupFilterButtons();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTable);
  } else {
    initTable();
  }
})();

// Setup filter buttons with proper multi-filter logic
function setupFilterButtons() {
  document.querySelectorAll('.filter-btn').forEach((btn) => {
    btn.addEventListener('click', function() {
      const filter = btn.dataset.filter;
      const filterType = btn.dataset.filterType;
      
      if (filter === 'all') {
        // Clear all filters
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentTypeFilter = 'all';
        currentStatusFilter = 'all';
      } else if (filterType === 'type') {
        // Toggle type filters
        document.querySelectorAll('.filter-btn[data-filter-type="type"]').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentTypeFilter = filter;
        // Remove 'All' active state
        document.querySelector('.filter-btn[data-filter="all"]')?.classList.remove('active');
      } else if (filterType === 'status') {
        // Toggle status filters
        document.querySelectorAll('.filter-btn[data-filter-type="status"]').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentStatusFilter = filter;
        // Remove 'All' active state
        document.querySelector('.filter-btn[data-filter="all"]')?.classList.remove('active');
      }
      
      applyFilters();
    });
  });
}

// Apply combined type and status filters
function applyFilters() {
  const rows = document.querySelectorAll('.submission-row');
  let visibleCount = 0;

  rows.forEach((row) => {
    const rowType = row.dataset.type;
    const rowStatus = row.dataset.status;
    let shouldShow = true;

    // Apply type filter
    if (currentTypeFilter !== 'all' && rowType !== currentTypeFilter) {
      shouldShow = false;
    }

    // Apply status filter
    if (currentStatusFilter !== 'all' && rowStatus !== currentStatusFilter) {
      shouldShow = false;
    }

    row.style.display = shouldShow ? '' : 'none';
    if (shouldShow) visibleCount++;
  });
}

// View submission modal
window.viewSubmission = function(id, type) {
  const modalBody = document.getElementById('viewModalBody');
  
  if (!modalBody) return;
  
  modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
  const modal = new bootstrap.Modal(document.getElementById('viewModal'));
  modal.show();
  
  fetch(`view_submission.php?id=${id}&type=${encodeURIComponent(type)}`)
    .then((res) => { 
      if (!res.ok) throw new Error(`HTTP ${res.status}`); 
      return res.text();
    })
    .then((html) => {
      modalBody.innerHTML = html;
    })
    .catch((err) => {
      modalBody.innerHTML = `
        <div class="alert alert-danger" role="alert">
          <i class="bi bi-exclamation-triangle"></i>
          <strong>Error loading submission details:</strong> ${err.message}
        </div>`;
    });
};

// Edit submission modal
window.editSubmission = function(id, type) {
  const modalBody = document.getElementById('editModalBody');
  
  if (!modalBody) return;
  
  modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
  
  const editModalElement = document.getElementById('editModal');
  if (!editModalElement) return;
  
  const modal = new bootstrap.Modal(editModalElement);
  modal.show();
  
  fetch(`edit_submission.php?id=${id}&type=${encodeURIComponent(type)}`)
    .then((res) => {
      if (!res.ok) throw new Error(`HTTP ${res.status}`); 
      return res.text();
    })
    .then((html) => {
      modalBody.innerHTML = html;
      // Call the initialization function from edit-submission.js
      if (typeof initializeEditForm === 'function') {
        initializeEditForm();
      }
    })
    .catch((err) => {
      modalBody.innerHTML = `
        <div class="alert alert-danger" role="alert">
          <i class="bi bi-exclamation-triangle"></i>
          <strong>Error loading edit form:</strong> ${err.message}
        </div>`;
    });
};

// Delete submission
window.deleteSubmission = function(id, type) {
  Swal.fire({
    title: 'Delete ' + type + '?',
    text: "This action cannot be undone! All associated data will be permanently deleted.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel',
    reverseButtons: true
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        title: 'Deleting...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
      
      fetch('delete_submission.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: id, type: type })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            title: 'Deleted!',
            text: data.message,
            icon: 'success',
            confirmButtonColor: '#28a745',
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            location.reload();
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: data.message,
            confirmButtonColor: '#c41e3a'
          });
        }
      })
      .catch(error => {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to delete submission: ' + error.message,
          confirmButtonColor: '#c41e3a'
        });
      });
    }
  });
};