<!-- Bootstrap & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
  body {
    background-color: #f8fafc;
    font-family: "Poppins", sans-serif;
  }
  .nav-link.active {
    background-color: #0d6efd !important;
    color: white !important;
    border-radius: 8px;
  }
  .section { display: none; }
  .section.active { display: block; }
  .card {
    border-radius: 12px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
  }
</style>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm">
  <div class="container-fluid">
    <h4 class="fw-bold text-primary mb-0">E-VoiceSpartans Tracker</h4>
    <ul class="nav nav-pills ms-auto">
      <li class="nav-item"><a class="nav-link active" href="#" onclick="showSection('dashboard')"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
      <li class="nav-item"><a class="nav-link" href="#" onclick="showSection('responses')"><i class="bi bi-chat-dots me-1"></i> Responses</a></li>
      <li class="nav-item"><a class="nav-link" href="#" onclick="showSection('profile')"><i class="bi bi-person-circle me-1"></i> Profile</a></li>
      <li class="nav-item"><a class="nav-link" href="#" onclick="showSection('settings')"><i class="bi bi-gear me-1"></i> Settings</a></li>
    </ul>
  </div>
</nav>
