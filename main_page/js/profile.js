const bootstrap = window.bootstrap;

let $table;
let currentTypeFilter = 'all';
let currentStatusFilter = 'all';
let tableInitialized = false;

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

function setupFilterButtons() {
  document.querySelectorAll('.filter-btn').forEach((btn) => {
    btn.addEventListener('click', function() {
      const filter = btn.dataset.filter;
      const filterType = btn.dataset.filterType;
      
      if (filter === 'all') {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentTypeFilter = 'all';
        currentStatusFilter = 'all';
      } else if (filterType === 'type') {
        document.querySelectorAll('.filter-btn[data-filter-type="type"]').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentTypeFilter = filter;
        document.querySelector('.filter-btn[data-filter="all"]')?.classList.remove('active');
      } else if (filterType === 'status') {
        document.querySelectorAll('.filter-btn[data-filter-type="status"]').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentStatusFilter = filter;
        document.querySelector('.filter-btn[data-filter="all"]')?.classList.remove('active');
      }
      
      applyFilters();
    });
  });
}

function applyFilters() {
  const rows = document.querySelectorAll('.submission-row');
  let visibleCount = 0;

  rows.forEach((row) => {
    const rowType = row.dataset.type;
    const rowStatus = row.dataset.status;
    let shouldShow = true;

    if (currentTypeFilter !== 'all' && rowType !== currentTypeFilter) {
      shouldShow = false;
    }

    if (currentStatusFilter !== 'all' && rowStatus !== currentStatusFilter) {
      shouldShow = false;
    }

    row.style.display = shouldShow ? '' : 'none';
    if (shouldShow) visibleCount++;
  });
}

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

window.viewResponses = function(id, type) {
  const backdrop = document.createElement('div');
  backdrop.className = 'response-viewer-backdrop';
  backdrop.onclick = function(e) {
    if (e.target === backdrop) {
      closeResponseModal();
    }
  };

  const container = document.createElement('div');
  container.className = 'response-viewer-container';
  container.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div></div>';

  backdrop.appendChild(container);
  document.body.appendChild(backdrop);
  document.body.style.overflow = 'hidden';

  document.addEventListener('keydown', handleResponseEscKey);

  fetch(`response.php?id=${id}&type=${encodeURIComponent(type)}`)
    .then(res => {
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return res.text();
    })
    .then(html => {
      container.innerHTML = html;
      if (typeof initializeResponseInteractions === 'function') {
        initializeResponseInteractions(id, type);
      }
    })
    .catch(err => {
      container.innerHTML = `
        <div class="alert alert-danger m-4" role="alert">
          <i class="bi bi-exclamation-triangle"></i>
          <strong>Error loading responses:</strong> ${err.message}
        </div>`;
    });
};

window.openFeedbackModal = function(id, type, title) {
  const backdrop = document.createElement('div');
  backdrop.className = 'feedback-viewer-backdrop';
  backdrop.onclick = function(e) {
    if (e.target === backdrop) {
      closeFeedbackModal();
    }
  };

  const container = document.createElement('div');
  container.className = 'feedback-viewer-container';
  container.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div></div>';

  backdrop.appendChild(container);
  document.body.appendChild(backdrop);
  document.body.style.overflow = 'hidden';

  document.addEventListener('keydown', handleFeedbackEscKey);

  fetch(`feedback.php?id=${id}&type=${encodeURIComponent(type)}`)
    .then(res => {
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return res.text();
    })
    .then(html => {
      container.innerHTML = html;

      if (typeof initializeFeedbackInteractions === 'function') {
        initializeFeedbackInteractions(id, type);
      }
    })
    .catch(err => {
      container.innerHTML = `
        <div class="alert alert-danger m-4" role="alert">
          <i class="bi bi-exclamation-triangle"></i>
          <strong>Error loading feedback form:</strong> ${err.message}
        </div>`;
    });
};

function closeResponseModal() {
  const backdrop = document.querySelector('.response-viewer-backdrop');
  if (backdrop) {
    backdrop.classList.add('closing');
    const container = backdrop.querySelector('.response-viewer-container');
    if (container) {
      container.classList.add('closing');
    }
    
    setTimeout(() => {
      backdrop.remove();
      document.body.style.overflow = '';
      document.removeEventListener('keydown', handleResponseEscKey);
    }, 300);
  }
}

function closeFeedbackModal() {
  const backdrop = document.querySelector('.feedback-viewer-backdrop');
  if (backdrop) {
    backdrop.classList.add('closing');
    const container = backdrop.querySelector('.feedback-viewer-container');
    if (container) {
      container.classList.add('closing');
    }
    
    setTimeout(() => {
      backdrop.remove();
      document.body.style.overflow = '';
      document.removeEventListener('keydown', handleFeedbackEscKey);
    }, 300);
  }
}

function handleResponseEscKey(e) {
  if (e.key === 'Escape') {
    closeResponseModal();
  }
}

function handleFeedbackEscKey(e) {
  if (e.key === 'Escape') {
    closeFeedbackModal();
  }
}

window.closeResponseModal = closeResponseModal;
window.closeFeedbackModal = closeFeedbackModal;