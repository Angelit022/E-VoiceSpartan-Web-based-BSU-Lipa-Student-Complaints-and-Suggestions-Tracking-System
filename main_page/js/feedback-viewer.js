let selectedRating = 0;
window.initializeFeedbackInteractions = function(id, type) {
  const stars = document.querySelectorAll('.star');
  const labels = document.querySelectorAll('.rating-label');
  const submitBtn = document.getElementById('submitFeedbackBtn');
  
  if (!stars.length || !submitBtn) return;

  selectedRating = 0;

  stars.forEach(star => {
    star.addEventListener('mouseenter', function() {
      const rating = parseInt(this.dataset.rating);
      highlightStars(rating);
      showLabel(rating);
    });

    star.addEventListener('click', function() {
      selectedRating = parseInt(this.dataset.rating);
      highlightStars(selectedRating);
      showLabel(selectedRating);
      submitBtn.disabled = false;
      
      this.style.animation = 'none';
      setTimeout(() => {
        this.style.animation = 'starPop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
      }, 10);
    });
  });

  const starRating = document.getElementById('starRating');
  if (starRating) {
    starRating.addEventListener('mouseleave', function() {
      if (selectedRating > 0) {
        highlightStars(selectedRating);
        showLabel(selectedRating);
      } else {
        highlightStars(0);
        hideAllLabels();
      }
    });
  }

  submitBtn.addEventListener('click', function() {
    if (selectedRating > 0) {
      const feedbackId = this.dataset.id;
      const feedbackType = this.dataset.type;
      submitFeedback(feedbackId, feedbackType, selectedRating);
    }
  });
};

function highlightStars(rating) {
  const stars = document.querySelectorAll('.star');
  stars.forEach((star, index) => {
    if (index < rating) {
      star.classList.add('active');
    } else {
      star.classList.remove('active');
    }
  });
}

function showLabel(rating) {
  const labels = document.querySelectorAll('.rating-label');
  labels.forEach(label => {
    if (parseInt(label.dataset.label) === rating) {
      label.classList.add('active');
    } else {
      label.classList.remove('active');
    }
  });
}

function hideAllLabels() {
  const labels = document.querySelectorAll('.rating-label');
  labels.forEach(label => {
    label.classList.remove('active');
  });
}

function submitFeedback(id, type, rating) {
  Swal.fire({
    title: '<i class="bi bi-hourglass-split me-2"></i>Submitting Review',
    html: '<div class="spinner-border text-warning mt-3" role="status"><span class="visually-hidden">Loading...</span></div>',
    showConfirmButton: false,
    allowOutsideClick: false,
    customClass: {
      popup: 'swal-loading-popup',
      title: 'swal-loading-title'
    }
  });

  fetch('process_quick_review.php?action=submit_feedback', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ id: id, type: type, rating: rating })
  })
  .then(response => {
    if (!response.ok) throw new Error('HTTP ' + response.status);
    return response.json();
  })
  .then(data => {
    closeFeedbackModal();
    
    if (data.success) {
      const ratingLabels = {
        1: 'Poor',
        2: 'Fair',
        3: 'Good',
        4: 'Very Good',
        5: 'Excellent'
      };
      
      Swal.fire({
        icon: 'success',
        title: '<i class="bi bi-check-circle-fill me-2"></i>Thank You!',
        html: `
          <p>Your review has been submitted successfully.</p>
          <div class="mt-3 p-3" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 12px;">
            <div class="d-flex align-items-center justify-content-center gap-2" style="font-size: 1.5rem;">
              ${Array(rating).fill('<i class="bi bi-star-fill" style="color: #fbbf24;"></i>').join('')}
              ${Array(5 - rating).fill('<i class="bi bi-star-fill" style="color: #e5e7eb;"></i>').join('')}
              <span class="ms-2" style="font-weight: 700; color: #1a1a2e;">${ratingLabels[rating]}</span>
            </div>
          </div>
        `,
        confirmButtonColor: '#fbbf24',
        confirmButtonText: 'Great!',
        allowOutsideClick: false
      }).then(() => {
        location.reload();
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: data.message || 'Failed to submit feedback. Please try again.',
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'OK'
      });
    }
  })
  .catch(error => {
    closeFeedbackModal();
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.message || 'Failed to submit feedback. Please try again.',
      confirmButtonColor: '#ef4444',
      confirmButtonText: 'OK'
    });
  });
}