
const bootstrap = window.bootstrap;

// Global reference to the table
let $table;

// Initialize submissions table, search and sorting
(function() {
  function initTable() {
    $table = $('#submissionsTable');
    if (!$table.length || typeof $table.bootstrapTable !== 'function') {
      console.warn('Table or bootstrap-table not found');
      return;
    }

    // Initialize table with custom data extraction
    $table.bootstrapTable({
      // Tell bootstrap-table to get data from data-* attributes
      onPostBody: function() {
        // This ensures data attributes are read properly
      }
    });

    // Hook up search box
    const $search = document.getElementById('tableSearch');
    if ($search) {
      $search.addEventListener('input', function (e) {
        $table.bootstrapTable('resetSearch', e.target.value);
      });
    }

    // Sorting controls (if they exist)
    const sortColumn = document.getElementById('sortColumn');
    const sortOrder = document.getElementById('sortOrder');

    function applySort() {
      const sortName = sortColumn ? sortColumn.value : 'date';
      const order = sortOrder ? sortOrder.value : 'desc';
      const fieldMap = {
        type: 'type',
        title: 'title',
        category: 'category',
        date: 'date',
        status: 'status'
      };
      const mapped = fieldMap[sortName] || 'date';
      $table.bootstrapTable('refreshOptions', {
        sortName: mapped,
        sortOrder: order
      });
    }

    if (sortColumn) sortColumn.addEventListener('change', applySort);
    if (sortOrder) sortOrder.addEventListener('change', applySort);

    // Initial sort
    applySort();

    // Setup filter buttons AFTER table is initialized
    setupFilterButtons();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTable);
  } else {
    initTable();
  }
})();

// Setup filter buttons with simple display toggle (from old working version)
function setupFilterButtons() {
  document.querySelectorAll('.filter-btn').forEach((btn) => {
    btn.addEventListener('click', function() {
      // Update active state
      document.querySelectorAll('.filter-btn').forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');
      
      // Apply filter
      const filter = btn.dataset.filter;
      console.log('Filter clicked:', filter);
      
      filterTable(filter);
    });
  });
}

// Filter table based on type/status (simple display toggle)
function filterTable(filter) {
  const rows = document.querySelectorAll('.submission-row');
  let visibleCount = 0;

  rows.forEach((row) => {
    let shouldShow = false;

    if (filter === 'all') {
      shouldShow = true;
    } else if (["Pending", "In Progress", "Resolved", "Rejected"].includes(filter)) {
      shouldShow = row.dataset.status === filter;
    } else if (["Complaint", "Suggestion"].includes(filter)) {
      shouldShow = row.dataset.type === filter;
    }

    row.style.display = shouldShow ? '' : 'none';
    if (shouldShow) visibleCount++;
  });

  console.log(`Filter applied: ${filter}, Visible rows: ${visibleCount}`);
}

// View submission modal - MUST be global
window.viewSubmission = function(id, type) {
  console.log('viewSubmission called:', id, type);
  const modalBody = document.getElementById('viewModalBody');
  
  if (!modalBody) {
    console.error('Modal body not found');
    return;
  }
  
  // Show loading state
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
      console.error('Error loading submission:', err);
      modalBody.innerHTML = `
        <div class="alert alert-danger" role="alert">
          <i class="bi bi-exclamation-triangle"></i>
          <strong>Error loading submission details:</strong> ${err.message}
        </div>`;
    });
};

// Edit submission modal - MUST be global
window.editSubmission = function(id, type) {
  console.log('editSubmission called:', id, type);
  const modalBody = document.getElementById('editModalBody');
  
  if (!modalBody) {
    console.error('Modal body not found');
    return;
  }
  
  // Show loading state
  modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
  const modal = new bootstrap.Modal(document.getElementById('editModal'));
  modal.show();
  
  fetch(`edit_submission.php?id=${id}&type=${encodeURIComponent(type)}`)
    .then((res) => { 
      if (!res.ok) throw new Error(`HTTP ${res.status}`); 
      return res.text();
    })
    .then((html) => {
      modalBody.innerHTML = html;
    })
    .catch((err) => {
      console.error('Error loading edit form:', err);
      modalBody.innerHTML = `
        <div class="alert alert-danger" role="alert">
          <i class="bi bi-exclamation-triangle"></i>
          <strong>Error loading edit form:</strong> ${err.message}
        </div>`;
    });
};

// Delete submission - MUST be global
window.deleteSubmission = function(id, type) {
  console.log('deleteSubmission called:', id, type);
  
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
      // Show loading
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
            confirmButtonColor: '#dc3545'
          }).then(() => {
            location.reload();
          });
        } else {
          Swal.fire('Error!', data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error!', 'Failed to delete submission: ' + error.message, 'error');
      });
    }
  });
};

// Submit edit form - MUST be global
window.submitEditForm = function(id, type) {
  console.log('submitEditForm called:', id, type);
  
  const title = document.getElementById('edit-title')?.value.trim();
  const description = document.getElementById('edit-description')?.value.trim();
  const category = document.getElementById('edit-category')?.value.trim();
  const priorityEl = document.getElementById('edit-priority');
  const priority = priorityEl ? priorityEl.value : null;

  if (!title || !description) {
    Swal.fire({
      icon: 'error',
      title: 'Validation Error',
      text: 'Please fill in all required fields'
    });
    return;
  }

  if (title.length > 255) {
    Swal.fire({
      icon: 'error',
      title: 'Validation Error',
      text: 'Title must be 255 characters or less'
    });
    return;
  }

  if (description.length < 10) {
    Swal.fire({
      icon: 'error',
      title: 'Validation Error',
      text: 'Description must be at least 10 characters'
    });
    return;
  }

  const formData = new FormData();
  formData.append('id', id);
  formData.append('type', type);
  formData.append('title', title);
  formData.append('description', description);
  formData.append('category', category);
  if (priority) formData.append('priority', priority);

  // Handle file upload if present
  const fileInput = document.getElementById('new-attachment');
  if (fileInput && fileInput.files.length > 0) {
    formData.append('new_attachment', fileInput.files[0]);
  }

  // Show loading
  Swal.fire({
    title: 'Saving Changes...',
    text: 'Please wait',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  fetch(`edit_submission.php?id=${id}&type=${encodeURIComponent(type)}`, { 
    method: 'POST', 
    body: formData 
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: data.message,
          confirmButtonColor: '#dc3545'
        }).then(() => {
          const modalEl = document.getElementById('editModal');
          const modalInstance = bootstrap.Modal.getInstance(modalEl);
          if (modalInstance) {
            modalInstance.hide();
          }
          location.reload();
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: data.message
        });
      }
    })
    .catch((err) => {
      console.error("Error submitting edit:", err);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Error updating submission: ' + err.message
      });
    });
};