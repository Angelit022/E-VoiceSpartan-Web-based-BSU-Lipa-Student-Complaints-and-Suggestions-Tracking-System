/**
 * Edit Submission Handler - Updated with "Update" button
 */

let originalFormData = {};

function initializeEditForm() {
  setTimeout(function() {
    const fileInput = document.getElementById('new-attachment');
    const addBtn = document.getElementById('addAttachmentBtn');
    const attachmentsList = document.getElementById('attachmentsList');
    const editForm = document.getElementById('editForm');
    
    if (!fileInput || !addBtn || !attachmentsList || !editForm) return;

    storeOriginalFormData();

    addBtn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      fileInput.click();
    });

    fileInput.addEventListener('change', function(e) {
      if (!this.files || this.files.length === 0) return;
      
      const file = this.files[0];
      handleNewFile(file);
    });

    attachmentsList.addEventListener('click', function(e) {
      let deleteBtn = null;
      if (e.target.classList.contains('delete-attachment-btn') || e.target.classList.contains('btn-remove-file')) {
        deleteBtn = e.target;
      } else if (e.target.closest('.delete-attachment-btn') || e.target.closest('.btn-remove-file')) {
        deleteBtn = e.target.closest('.delete-attachment-btn') || e.target.closest('.btn-remove-file');
      }
      
      if (!deleteBtn) return;
      
      e.preventDefault();
      e.stopPropagation();
      
      const item = deleteBtn.closest('.attachment-item');
      if (!item) return;
      
      const attachmentId = item.getAttribute('data-attachment-id');
      const isNew = item.getAttribute('data-new') === 'true';
      
      if (isNew) {
        removeNewFile(item);
      } else if (attachmentId) {
        deleteExistingFile(attachmentId, item);
      }
    });

    editForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const id = this.getAttribute('data-submission-id');
      const type = this.getAttribute('data-submission-type');
      submitForm(id, type);
      return false;
    });

    function handleNewFile(file) {
      const existing = attachmentsList.querySelectorAll('.attachment-item[data-attachment-id]').length;
      const pending = attachmentsList.querySelectorAll('.attachment-item[data-new="true"]').length;
      const total = existing + pending;
      
      if (total >= 5) {
        Swal.fire({
          icon: 'warning',
          title: 'Maximum Attachments Reached',
          text: 'You can only attach up to 5 files per submission.',
          confirmButtonColor: '#c41e3a'
        });
        fileInput.value = '';
        return;
      }
      
      const maxSize = 10 * 1024 * 1024;
      if (file.size > maxSize) {
        Swal.fire({
          icon: 'error',
          title: 'File Too Large',
          text: 'File size must not exceed 10MB.',
          confirmButtonColor: '#c41e3a'
        });
        fileInput.value = '';
        return;
      }
      
      const validExtensions = ['jpg', 'jpeg', 'png', 'mp4', 'pdf', 'doc', 'docx'];
      const ext = file.name.split('.').pop().toLowerCase();
      if (!validExtensions.includes(ext)) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid File Type',
          text: 'Allowed file types: JPG, PNG, MP4, PDF, DOC, DOCX',
          confirmButtonColor: '#c41e3a'
        });
        fileInput.value = '';
        return;
      }
      
      const noMsg = document.getElementById('noAttachmentsMsg');
      if (noMsg) noMsg.remove();
      
      let icon = 'bi-file-earmark';
      if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) icon = 'bi-image';
      else if (ext === 'pdf') icon = 'bi-file-pdf';
      else if (['doc', 'docx'].includes(ext)) icon = 'bi-file-word';
      else if (ext === 'mp4') icon = 'bi-film';
      
      const fileSize = (file.size / (1024 * 1024)).toFixed(2);
      
      const preview = document.createElement('div');
      preview.className = 'attachment-item new-file-preview';
      preview.setAttribute('data-new', 'true');
      preview.innerHTML = `
        <div class="attachment-info">
          <i class="bi ${icon}"></i>
          <span class="attachment-name">${file.name} (${fileSize} MB) - Ready to upload</span>
        </div>
        <button type="button" class="delete-attachment-btn btn-remove-file" title="Remove file">
          <i class="bi bi-x-circle-fill"></i>
        </button>
      `;
      
      attachmentsList.appendChild(preview);
      updateButtonState();
    }

    function removeNewFile(item) {
      fileInput.value = '';
      
      item.classList.add('removing');
      setTimeout(function() {
        item.remove();
        updateButtonState();
        
        if (attachmentsList.querySelectorAll('.attachment-item').length === 0) {
          attachmentsList.innerHTML = '<div class="no-attachments" id="noAttachmentsMsg"><i class="bi bi-inbox"></i> No attachments yet</div>';
        }
      }, 300);
    }

    function deleteExistingFile(attachmentId, item) {
      Swal.fire({
        title: 'Delete Attachment?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (!result.isConfirmed) return;

        const deleteBtn = item.querySelector('.delete-attachment-btn, .btn-remove-file');
        if (deleteBtn) deleteBtn.disabled = true;
        
        fetch('delete_attachment.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ attachment_id: parseInt(attachmentId) })
        })
        .then(function(response) {
          if (!response.ok) throw new Error('HTTP ' + response.status);
          return response.json();
        })
        .then(function(data) {
          if (data.success) {
            item.classList.add('removing');
            setTimeout(function() {
              item.remove();
              updateButtonState();
              
              if (attachmentsList.querySelectorAll('.attachment-item').length === 0) {
                attachmentsList.innerHTML = '<div class="no-attachments" id="noAttachmentsMsg"><i class="bi bi-inbox"></i> No attachments yet</div>';
              }
            }, 300);
            
            Swal.fire({
              icon: 'success',
              title: 'Deleted!',
              text: 'Attachment has been deleted.',
              timer: 2000,
              showConfirmButton: false
            });
          } else {
            throw new Error(data.message || 'Failed to delete');
          }
        })
        .catch(function(error) {
          if (deleteBtn) deleteBtn.disabled = false;
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to delete attachment: ' + error.message,
            confirmButtonColor: '#c41e3a'
          });
        });
      });
    }

    function updateButtonState() {
      const existing = attachmentsList.querySelectorAll('.attachment-item[data-attachment-id]').length;
      const pending = attachmentsList.querySelectorAll('.attachment-item[data-new="true"]').length;
      const total = existing + pending;
      const canAdd = total < 5;
      
      addBtn.disabled = !canAdd;
    }

    function storeOriginalFormData() {
      originalFormData = {
        title: document.getElementById('edit-title')?.value.trim() || '',
        description: document.getElementById('edit-description')?.value.trim() || '',
        category: document.getElementById('edit-category')?.value.trim() || '',
        priority: document.getElementById('edit-priority')?.value || 'Medium'
      };
    }

    function hasFormChanged() {
      const currentData = {
        title: document.getElementById('edit-title')?.value.trim() || '',
        description: document.getElementById('edit-description')?.value.trim() || '',
        category: document.getElementById('edit-category')?.value.trim() || '',
        priority: document.getElementById('edit-priority')?.value || 'Medium'
      };

      const hasNewFile = fileInput.files.length > 0;
      
      return hasNewFile || 
             currentData.title !== originalFormData.title ||
             currentData.description !== originalFormData.description ||
             currentData.category !== originalFormData.category ||
             currentData.priority !== originalFormData.priority;
    }

    function submitForm(id, type) {
      const title = document.getElementById('edit-title').value.trim();
      const description = document.getElementById('edit-description').value.trim();
      const category = document.getElementById('edit-category').value.trim();
      const priority = document.getElementById('edit-priority').value || 'Medium';
      
      if (!title || !description) {
        Swal.fire({
          icon: 'error',
          title: 'Validation Error',
          text: 'Please fill in all required fields (Title and Description).',
          confirmButtonColor: '#c41e3a'
        });
        return;
      }
      
      if (description.length < 10) {
        Swal.fire({
          icon: 'error',
          title: 'Validation Error',
          text: 'Description must be at least 10 characters long.',
          confirmButtonColor: '#c41e3a'
        });
        return;
      }

      if (!hasFormChanged()) {
        Swal.fire({
          icon: 'info',
          title: 'No Changes Detected',
          text: 'You have not made any changes to the submission.',
          confirmButtonColor: '#c41e3a'
        });
        return;
      }
      
      const formData = new FormData();
      formData.append('id', id);
      formData.append('type', type);
      formData.append('title', title);
      formData.append('description', description);
      formData.append('category', category);
      formData.append('priority', priority);
      
      if (fileInput.files.length > 0) {
        formData.append('new_attachment', fileInput.files[0]);
      }
      
      const submitBtn = editForm.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';
      
      fetch('process_edit.php', {
        method: 'POST',
        body: formData
      })
      .then(function(response) {
        if (!response.ok) throw new Error('HTTP ' + response.status);
        return response.json();
      })
      .then(function(data) {
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Submission updated successfully!',
            confirmButtonColor: '#28a745',
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            location.reload();
          });
        } else {
          throw new Error(data.message || 'Failed to update');
        }
      })
      .catch(function(error) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to save changes: ' + error.message,
          confirmButtonColor: '#c41e3a'
        });
      });
    }

    updateButtonState();
  }, 100);
}