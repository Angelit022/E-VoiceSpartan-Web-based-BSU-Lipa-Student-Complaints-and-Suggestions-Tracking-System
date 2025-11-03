<main class="container py-4">
<section id="dashboard" class="section active">
  <h5 class="fw-bold mb-3">Dashboard Overview</h5>
  <div class="row g-3 mb-4 text-center">
    <div class="col-md-3"><div class="card p-3 border-danger"><h6>Total Reports</h6><h3 class="text-danger">128</h3></div></div>
    <div class="col-md-3"><div class="card p-3 border-warning"><h6>Pending</h6><h3 class="text-warning">22</h3></div></div>
    <div class="col-md-3"><div class="card p-3 border-success"><h6>Resolved</h6><h3 class="text-success">90</h3></div></div>
    <div class="col-md-3"><div class="card p-3 border-info"><h6>Suggestions</h6><h3 class="text-info">40</h3></div></div>
  </div>

  <div class="row g-4">
    <div class="col-md-6">
      <div class="card p-3">
        <h6>Monthly Trend</h6>
        <canvas id="trendChart" height="150"></canvas>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card p-3">
        <h6>Category Breakdown</h6>
        <canvas id="catChart" height="150"></canvas>
      </div>
    </div>
  </div>
</section>
