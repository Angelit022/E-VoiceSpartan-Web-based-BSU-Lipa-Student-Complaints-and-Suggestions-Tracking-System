
window.initializeResponseInteractions = function(id, type) {

  console.log('Response viewer initialized for', type, id);
};

document.addEventListener('DOMContentLoaded', function() {
  checkAllResponseStatuses();

  const table = document.getElementById('submissionsTable');
  if (table) {
    $(table).on('page-change.bs.table', function() {
      setTimeout(checkAllResponseStatuses, 100);
    });
    
    $(table).on('search.bs.table', function() {
      setTimeout(checkAllResponseStatuses, 100);
    });
  }

  const tableBody = document.getElementById('submissions-body');
  if (tableBody) {
    const observer = new MutationObserver(function(mutations) {
      checkAllResponseStatuses();
    });
    
    observer.observe(tableBody, {
      childList: true,
      subtree: true
    });
  }
});

function checkAllResponseStatuses() {
  fetch('process_response.php?action=check_status')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        updateAllButtonStates(data.statuses);
      }
    })
    .catch(error => console.error('Error fetching response status:', error));
}

function updateAllButtonStates(statuses) {
  const allButtons = document.querySelectorAll('.btn-view-response');
  
  Object.keys(statuses).forEach(key => {
    const [type, id] = key.split('_');
    const hasResponses = statuses[key].has_responses;
    const hasViewed = statuses[key].has_viewed;
    
    allButtons.forEach(btn => {
      const onclickAttr = btn.getAttribute('onclick');
      if (onclickAttr && onclickAttr.includes(`viewResponses(${id}`) && onclickAttr.includes(`'${type}'`)) {
        if (hasResponses && !hasViewed) {
          btn.classList.add('unread');
          btn.classList.remove('read');
        } else if (hasResponses && hasViewed) {
          btn.classList.add('read');
          btn.classList.remove('unread');
        }
      }
    });
  });
}

window.initializeResponseInteractions = function(id, type) {

  console.log('Response viewer initialized for', type, id);
};