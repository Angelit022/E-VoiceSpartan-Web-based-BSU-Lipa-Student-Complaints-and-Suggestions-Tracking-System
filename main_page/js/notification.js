document.addEventListener('DOMContentLoaded', function() {
  checkResponseStatuses();
});

function checkResponseStatuses() {
  // Use correct relative path from notification folder to profile folder
  fetch('../profile/process_response.php?action=check_status')
    .then(response => {
      if (!response.ok) {
        throw new Error('HTTP ' + response.status);
      }
      return response.json();
    })
    .then(data => {
      if (data.success) {
        updateNotificationIcons(data.statuses);
      }
    })
    .catch(error => console.error('Error fetching response status:', error));
}

function updateNotificationIcons(statuses) {
  // Update all response notification icons
  const responseNotifications = document.querySelectorAll('.notification-item[data-type="response"]');
  
  responseNotifications.forEach(notif => {
    const complaintId = notif.getAttribute('data-complaint-id');
    const suggestionId = notif.getAttribute('data-suggestion-id');
    const isUnread = notif.classList.contains('unread');
    
    // Get the key for this notification
    let key = null;
    if (complaintId) {
      key = `complaint_${complaintId}`;
    } else if (suggestionId) {
      key = `suggestion_${suggestionId}`;
    }
    
    // Get the icon element
    const icon = notif.querySelector('.view-response-icon');
    if (!icon) return;
    
    // Check if there are responses and if viewed
    const hasResponses = key && statuses[key] && statuses[key].has_responses;
    const hasViewed = key && statuses[key] && statuses[key].has_viewed;
    
    if (!hasResponses) {
      // No responses yet - hide icon completely
      icon.style.display = 'none';
    } else if (hasResponses && !hasViewed && isUnread) {
      // Has responses but not viewed AND notification is unread - show with animation
      icon.style.display = 'flex';
      notif.classList.add('unread');
    } else if (hasResponses && (hasViewed || !isUnread)) {
      // Has responses and viewed OR notification is read - show without animation
      icon.style.display = 'flex';
      notif.classList.remove('unread');
      icon.style.background = 'none';
      icon.style.border = 'none';
      icon.style.boxShadow = 'none';
      icon.style.width = 'auto';
      icon.style.height = 'auto';
      icon.style.padding = '0.5rem';
      icon.style.color = 'rgba(16, 185, 129, 0.5)';
      icon.style.animation = 'none';
    }
  });
}

function markAsRead(notificationId) {
    fetch('process_notification.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=mark_as_read&id=${notificationId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to mark notification as read',
                confirmButtonColor: '#c41e3a'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred',
            confirmButtonColor: '#c41e3a'
        });
    });
}

function markAllAsRead() {
    Swal.fire({
        title: 'Mark All as Read?',
        text: 'This will mark all notifications as read.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, mark all',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('process_notification.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=mark_all_as_read'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'All notifications marked as read',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to mark notifications as read',
                        confirmButtonColor: '#c41e3a'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred',
                    confirmButtonColor: '#c41e3a'
                });
            });
        }
    });
}

function deleteNotification(notificationId) {
    Swal.fire({
        title: 'Delete Notification?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('process_notification.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete&id=${notificationId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Notification has been deleted',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to delete notification',
                        confirmButtonColor: '#c41e3a'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred',
                    confirmButtonColor: '#c41e3a'
                });
            });
        }
    });
}

// View response function - redirect to profile page with modal trigger
function viewResponse(id, type) {
    // Redirect to profile page and trigger response viewer
    window.location.href = `../profile/profile.php?view_response=1&id=${id}&type=${type}`;
}

// Make viewResponse globally available
window.viewResponse = viewResponse;