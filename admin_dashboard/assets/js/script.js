// Section switching
function showSection(id) {
  document.querySelectorAll('.section').forEach(sec => sec.classList.remove('active'));
  document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
  document.getElementById(id).classList.add('active');
  event.target.classList.add('active');
}

// Save response
function saveResponse(btn) {
  const row = btn.closest("tr");
  const status = row.querySelector("select").value;
  const response = row.querySelector("textarea").value;
  const badge = row.querySelector(".badge");

  if (status === "Resolved") badge.className = "badge bg-success";
  else if (status === "In Progress") badge.className = "badge bg-info";
  else if (status === "Invalid") badge.className = "badge bg-danger";
  else badge.className = "badge bg-warning";

  badge.textContent = status;
  alert(`Response saved:\nStatus: ${status}\nResponse: ${response}`);
}

// Filter by category
function filterCategory() {
  const filter = document.getElementById("categoryFilter").value;
  document.querySelectorAll("#reportTable tr").forEach(tr => {
    tr.style.display = (filter === "all" || tr.dataset.category === filter) ? "" : "none";
  });
}

// Dark mode toggle
document.addEventListener("DOMContentLoaded", () => {
  const darkModeToggle = document.getElementById("darkModeToggle");
  if (darkModeToggle) {
    darkModeToggle.addEventListener("change", e => {
      document.body.style.background = e.target.checked ? "#212529" : "#f8fafc";
      document.body.style.color = e.target.checked ? "#f8f9fa" : "#000";
    });
  }
});

// Reset settings
function resetSettings() {
  document.getElementById('notifToggle').checked = true;
  document.getElementById('darkModeToggle').checked = false;
  document.body.style.background = '#f8fafc';
  document.body.style.color = '#000';
  alert('Settings reset to default.');
}

// Charts
document.addEventListener("DOMContentLoaded", () => {
  const trendCtx = document.getElementById('trendChart');
  if (trendCtx) {
    new Chart(trendCtx, {
      type: 'line',
      data: {
        labels: ['May','Jun','Jul','Aug','Sep','Oct'],
        datasets: [
          { label: 'Complaints', data: [12,18,9,14,10,8], borderColor: '#0d6efd', fill:false },
          { label: 'Suggestions', data: [6,5,8,4,7,10], borderColor: '#20c997', fill:false }
        ]
      }
    });
  }

  const catCtx = document.getElementById('catChart');
  if (catCtx) {
    new Chart(catCtx, {
      type: 'doughnut',
      data: {
        labels: [
          'Academic Concerns',
          'Campus Cleanliness',
          'Facilities',
          'Admin Services',
          'Academic Services',
          'Student Services',
          'Staff/Faculty',
          'Others'
        ],
        datasets: [{
          data: [24,18,15,8,10,6,12,5],
          backgroundColor: [
            '#0d6efd','#20c997','#ffc107','#dc3545',
            '#6f42c1','#198754','#fd7e14','#0dcaf0'
          ]
        }]
      }
    });
  }
});
