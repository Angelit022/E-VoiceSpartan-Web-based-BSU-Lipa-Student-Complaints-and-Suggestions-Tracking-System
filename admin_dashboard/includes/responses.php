<section id="responses" class="section mt-4">
  <h4 class="fw-bold mb-3">Student Complaints & Suggestions</h4>

  <div class="table-responsive">
    <table class="table table-bordered align-middle">
      <thead class="table-primary">
        <tr>
          <th>ID</th>
          <th>Category</th>
          <th>Details</th>
          <th>Status</th>
          <th>Response</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody id="reportTable">
        <tr data-category="academic concerns">
          <td>1</td>
          <td>Academic Concerns</td>
          <td>Grades not updated</td>
          <td><span class="badge bg-warning">Pending</span></td>
          <td><textarea class="form-control" placeholder="Enter response..."></textarea></td>
          <td>
            <select class="form-select mb-2">
              <option>Pending</option>
              <option>In Progress</option>
              <option>Resolved</option>
              <option>Invalid</option>
            </select>
            <button class="btn btn-sm btn-primary w-100" onclick="saveResponse(this)">Save</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</section>
