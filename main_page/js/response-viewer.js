/**
 * Response Viewer Modal
 * Displays admin responses in a beautiful modal overlay
 */

// Global function to view responses
window.viewResponses = function(id, type) {
  // Show loading state
  Swal.fire({
    title: 'Loading Responses...',
    text: 'Please wait',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  // Fetch responses from server
  fetch(`get_responses.php?id=${id}&type=${type}`)
    .then(response => {
      if (!response.ok) throw new Error('HTTP ' + response.status);
      return response.json();
    })
    .then(data => {
      Swal.close();
      
      if (!data.success) {
        throw new Error(data.message || 'Failed to load responses');
      }

      // Display responses in modal
      displayResponseModal(data.responses, id, type);
    })
    .catch(error => {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Failed to load responses: ' + error.message,
        confirmButtonColor: '#c41e3a'
      });
    });
};

function displayResponseModal(responses, id, type) {
  // Create backdrop
  const backdrop = document.createElement('div');
  backdrop.className = 'response-viewer-backdrop';
  backdrop.onclick = function(e) {
    if (e.target === backdrop) {
      closeResponseModal();
    }
  };

  // Create container
  const container = document.createElement('div');
  container.className = 'response-viewer-container';

  // Create header
  const header = document.createElement('div');
  header.className = 'response-viewer-header';
  header.innerHTML = `
    <h2>
      <i class="bi bi-envelope-open"></i>
      Admin Responses
    </h2>
    <p>Response${responses.length !== 1 ? 's' : ''} for ${type.charAt(0).toUpperCase() + type.slice(1)} #${String(id).padStart(5, '0')}</p>
    <div class="response-viewer-close" onclick="closeResponseModal()">
      <i class="bi bi-x"></i>
    </div>
  `;

  // Create body
  const body = document.createElement('div');
  body.className = 'response-viewer-body';

  if (responses.length === 0) {
    body.innerHTML = `
      <div class="no-responses">
        <i class="bi bi-inbox"></i>
        <h3>No Responses Yet</h3>
        <p>The admin hasn't responded to this submission yet.</p>
      </div>
    `;
  } else {
    // Create response items
    responses.forEach((response, index) => {
      const responseItem = document.createElement('div');
      responseItem.className = 'response-item';
      responseItem.style.animationDelay = `${index * 0.1}s`;

      const adminInitials = response.admin_name.split(' ').map(n => n[0]).join('').toUpperCase();
      const roleLabel = response.admin_role === 'super_admin' ? 'Super Administrator' : 'SSC Administrator';
      const formattedDate = formatDate(response.date_responded);

      responseItem.innerHTML = `
        <div class="response-admin-info">
          <div class="response-admin-avatar">
            ${adminInitials}
          </div>
          <div class="response-admin-details">
            <h4 class="response-admin-name">${escapeHtml(response.admin_name)}</h4>
            <p class="response-admin-role">
              <i class="bi bi-shield-check"></i>
              ${roleLabel}
            </p>
          </div>
        </div>
        <div class="response-date">
          <i class="bi bi-calendar-event"></i>
          ${formattedDate}
        </div>
        <div class="response-message">${escapeHtml(response.message)}</div>
      `;

      body.appendChild(responseItem);
    });
  }

  // Assemble modal
  container.appendChild(header);
  container.appendChild(body);
  backdrop.appendChild(container);

  // Add to document
  document.body.appendChild(backdrop);
  document.body.style.overflow = 'hidden'; // Prevent background scrolling

  // Add ESC key listener
  document.addEventListener('keydown', handleEscKey);
}

function closeResponseModal() {
  const backdrop = document.querySelector('.response-viewer-backdrop');
  if (backdrop) {
    backdrop.style.animation = 'fadeOut 0.3s ease forwards';
    const container = backdrop.querySelector('.response-viewer-container');
    if (container) {
      container.style.animation = 'scaleOut 0.3s ease forwards';
    }
    
    setTimeout(() => {
      backdrop.remove();
      document.body.style.overflow = ''; // Restore scrolling
      document.removeEventListener('keydown', handleEscKey);
    }, 300);
  }
}

// Add fadeOut animation
const style = document.createElement('style');
style.textContent = `
  @keyframes fadeOut {
    to { opacity: 0; }
  }
  @keyframes scaleOut {
    to { transform: scale(0.9); opacity: 0; }
  }
`;
document.head.appendChild(style);

function handleEscKey(e) {
  if (e.key === 'Escape') {
    closeResponseModal();
  }
}

function formatDate(dateString) {
  const date = new Date(dateString);
  const options = { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  };
  return date.toLocaleDateString('en-US', options);
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// Make closeResponseModal globally available
window.closeResponseModal = closeResponseModal;