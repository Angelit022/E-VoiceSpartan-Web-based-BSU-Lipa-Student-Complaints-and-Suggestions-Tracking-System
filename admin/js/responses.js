let detailsModal, responseModal, statusModal
let currentPage = 1
let rowsPerPage = 10
let sortOrder = "desc"
let allRows = []
let filteredRows = []

const bootstrap = window.bootstrap
const Swal = window.Swal

const STATUS_FLOW = {
  1: [2],
  2: [3, 4],
  3: [],
  4: [],
}

const STATUS_NAMES = {
  1: "Pending",
  2: "In Progress",
  3: "Resolved",
  4: "Rejected",
}

document.addEventListener("DOMContentLoaded", () => {
  initializeModals()
  initializeTable()
  initializeEventListeners()

  if (document.getElementById("reportTableBody")?.querySelectorAll("tr").length > 0) {
    displayPage(1)
  }
})

function initializeModals() {
  try {
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove())
    
    const detailsModalEl = document.getElementById("detailsModal")
    const responseModalEl = document.getElementById("responseModal")
    const statusModalEl = document.getElementById("statusModal")

    if (detailsModalEl && bootstrap) {
      detailsModal = new bootstrap.Modal(detailsModalEl, {
        backdrop: true,
        keyboard: true,
        focus: true
      })
      
      detailsModalEl.addEventListener('shown.bs.modal', function() {
        detailsModalEl.focus()
        const backdrop = document.querySelector('.modal-backdrop')
        if (backdrop) {
          backdrop.style.zIndex = '1040'
        }
        detailsModalEl.style.zIndex = '1050'
      })
    }
    
    if (responseModalEl && bootstrap) {
      responseModal = new bootstrap.Modal(responseModalEl, {
        backdrop: true,
        keyboard: true,
        focus: true
      })
      
      responseModalEl.addEventListener('shown.bs.modal', function() {
        responseModalEl.focus()
        const backdrop = document.querySelector('.modal-backdrop')
        if (backdrop) {
          backdrop.style.zIndex = '1040'
        }
        responseModalEl.style.zIndex = '1050'
      })
    }
    
    if (statusModalEl && bootstrap) {
      statusModal = new bootstrap.Modal(statusModalEl, {
        backdrop: true,
        keyboard: true,
        focus: true
      })
      
      statusModalEl.addEventListener('shown.bs.modal', function() {
        statusModalEl.focus()
        const backdrop = document.querySelector('.modal-backdrop')
        if (backdrop) {
          backdrop.style.zIndex = '1040'
        }
        statusModalEl.style.zIndex = '1050'
      })
    }
  } catch (error) {
    showAlert("error", "Error", "Error initializing modals")
  }
}

function initializeTable() {
  const tableBody = document.getElementById("reportTableBody")
  if (tableBody) {
    allRows = Array.from(tableBody.querySelectorAll("tr"))
    filteredRows = [...allRows]
  }
}

function initializeEventListeners() {
  const responseMessage = document.getElementById("responseMessage")
  if (responseMessage) {
    responseMessage.addEventListener("input", function () {
      const charCountEl = document.getElementById("charCount")
      if (charCountEl) {
        charCountEl.textContent = this.value.length
      }
    })
  }

  const searchInput = document.getElementById("searchInput")
  const typeFilter = document.getElementById("typeFilter")
  const statusFilter = document.getElementById("statusFilter")

  if (searchInput) searchInput.addEventListener("keyup", filterAndPaginate)
  if (typeFilter) typeFilter.addEventListener("change", filterAndPaginate)
  if (statusFilter) statusFilter.addEventListener("change", filterAndPaginate)
}

function filterAndPaginate() {
  const searchTerm = document.getElementById("searchInput")?.value.toLowerCase() || ""
  const typeFilter = document.getElementById("typeFilter")?.value || ""
  const statusFilter = document.getElementById("statusFilter")?.value || ""

  filteredRows = allRows.filter((row) => {
    const type = row.getAttribute("data-type")
    const status = row.getAttribute("data-status")
    const text = row.textContent.toLowerCase()

    let visible = true
    if (searchTerm && !text.includes(searchTerm)) visible = false
    if (typeFilter && type !== typeFilter) visible = false
    if (statusFilter && status !== statusFilter) visible = false

    return visible
  })

  currentPage = 1
  displayPage(1)
}

function sortTable(order) {
  sortOrder = order

  const sortAsc = document.getElementById("sortAsc")
  const sortDesc = document.getElementById("sortDesc")

  if (sortAsc) {
    sortAsc.classList.remove("active")
    if (order === "asc") sortAsc.classList.add("active")
  }
  if (sortDesc) {
    sortDesc.classList.remove("active")
    if (order === "desc") sortDesc.classList.add("active")
  }

  filteredRows.sort((a, b) => {
    const dateA = Number.parseInt(a.getAttribute("data-date")) || 0
    const dateB = Number.parseInt(b.getAttribute("data-date")) || 0
    return order === "asc" ? dateA - dateB : dateB - dateA
  })

  currentPage = 1
  displayPage(1)
}

function displayPage(pageNum) {
  currentPage = pageNum
  const tableBody = document.getElementById("reportTableBody")

  if (!tableBody) return

  tableBody.innerHTML = ""

  const totalPages = Math.ceil(filteredRows.length / rowsPerPage)
  const start = (pageNum - 1) * rowsPerPage
  const end = Math.min(start + rowsPerPage, filteredRows.length)

  for (let i = start; i < end; i++) {
    tableBody.appendChild(filteredRows[i].cloneNode(true))
  }

  const startRowEl = document.getElementById("startRow")
  const endRowEl = document.getElementById("endRow")
  const totalRowsEl = document.getElementById("totalRows")

  if (startRowEl) startRowEl.textContent = filteredRows.length === 0 ? 0 : start + 1
  if (endRowEl) endRowEl.textContent = end
  if (totalRowsEl) totalRowsEl.textContent = filteredRows.length

  generatePaginationButtons(totalPages, pageNum)
}

function generatePaginationButtons(totalPages, currentPage) {
  const paginationEl = document.getElementById("pagination")
  if (!paginationEl) return

  paginationEl.innerHTML = ""

  const prevBtn = document.createElement("li")
  prevBtn.className = `page-item ${currentPage === 1 ? "disabled" : ""}`
  prevBtn.innerHTML = `<a class="page-link" href="#" onclick="displayPage(${currentPage - 1}); return false;"><i class="bi bi-chevron-left"></i></a>`
  paginationEl.appendChild(prevBtn)

  const maxButtons = 5
  let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2))
  const endPage = Math.min(totalPages, startPage + maxButtons - 1)

  if (endPage - startPage < maxButtons - 1) {
    startPage = Math.max(1, endPage - maxButtons + 1)
  }

  if (startPage > 1) {
    const firstBtn = document.createElement("li")
    firstBtn.className = "page-item"
    firstBtn.innerHTML = `<a class="page-link" href="#" onclick="displayPage(1); return false;">1</a>`
    paginationEl.appendChild(firstBtn)

    if (startPage > 2) {
      const dots = document.createElement("li")
      dots.className = "page-item disabled"
      dots.innerHTML = `<span class="page-link">...</span>`
      paginationEl.appendChild(dots)
    }
  }

  for (let i = startPage; i <= endPage; i++) {
    const btn = document.createElement("li")
    btn.className = `page-item ${i === currentPage ? "active" : ""}`
    btn.innerHTML = `<a class="page-link" href="#" onclick="displayPage(${i}); return false;">${i}</a>`
    paginationEl.appendChild(btn)
  }

  if (endPage < totalPages) {
    if (endPage < totalPages - 1) {
      const dots = document.createElement("li")
      dots.className = "page-item disabled"
      dots.innerHTML = `<span class="page-link">...</span>`
      paginationEl.appendChild(dots)
    }

    const lastBtn = document.createElement("li")
    lastBtn.className = "page-item"
    lastBtn.innerHTML = `<a class="page-link" href="#" onclick="displayPage(${totalPages}); return false;">${totalPages}</a>`
    paginationEl.appendChild(lastBtn)
  }

  const nextBtn = document.createElement("li")
  nextBtn.className = `page-item ${currentPage === totalPages ? "disabled" : ""}`
  nextBtn.innerHTML = `<a class="page-link" href="#" onclick="displayPage(${currentPage + 1}); return false;"><i class="bi bi-chevron-right"></i></a>`
  paginationEl.appendChild(nextBtn)
}

function changePagination() {
  const select = document.getElementById("rowsPerPage")
  if (select) {
    rowsPerPage = Number.parseInt(select.value)
    currentPage = 1
    displayPage(1)
  }
}

function resetFilters() {
  const searchInput = document.getElementById("searchInput")
  const typeFilter = document.getElementById("typeFilter")
  const statusFilter = document.getElementById("statusFilter")

  if (searchInput) searchInput.value = ""
  if (typeFilter) typeFilter.value = ""
  if (statusFilter) statusFilter.value = ""

  filterAndPaginate()
}

function parseAttachments(attachmentsStr) {
  if (!attachmentsStr) return []

  const attachments = []
  const parts = attachmentsStr.split(";;")

  for (const part of parts) {
    const fields = part.split("|")
    if (fields.length === 3) {
      attachments.push({
        id: fields[0],
        path: fields[1],
        type: fields[2],
        filename: fields[1].split("/").pop(),
      })
    }
  }

  return attachments
}

function getFileIcon(fileType) {
  if (fileType.includes("image")) return "bi-file-image"
  if (fileType.includes("pdf")) return "bi-file-pdf"
  if (fileType.includes("word") || fileType.includes("document")) return "bi-file-word"
  if (fileType.includes("excel") || fileType.includes("spreadsheet")) return "bi-file-excel"
  return "bi-file-earmark"
}

function getStatusClass(statusName) {
  const statusMap = {
    'Pending': 'status-pending',
    'In Progress': 'status-in-progress',
    'Resolved': 'status-resolved',
    'Rejected': 'status-rejected'
  }
  return statusMap[statusName] || ''
}

function getPriorityClass(priority) {
  const priorityMap = {
    'High': 'priority-high',
    'Medium': 'priority-medium',
    'Low': 'priority-low'
  }
  return priorityMap[priority] || ''
}

function viewDetails(submission) {
  try {
    if (!submission) {
      showAlert("error", "Error", "Invalid submission data")
      return
    }

    const isAnonymous = submission.is_anonymous == 1 || submission.is_anonymous === true || submission.is_anonymous === "1"
    
    const modalBody = document.querySelector('#detailsModal .modal-body')
    if (!modalBody) {
      showAlert("error", "Error", "Modal body not found")
      return
    }

    modalBody.innerHTML = ''

    // Student Information Section
    if (isAnonymous) {
      modalBody.innerHTML += `
        <div class="detail-section">
          <div class="section-header">
            <i class="bi bi-person-circle"></i>
            <h6>Student Information</h6>
          </div>
          <div class="anonymous-badge">
            <i class="bi bi-incognito"></i>
            <span>Anonymous Submission</span>
          </div>
        </div>
      `
    } else {
      const studentName = `${submission.first_name || ""} ${submission.last_name || ""}`.trim()
      const email = submission.email || "N/A"
      
      modalBody.innerHTML += `
        <div class="detail-section">
          <div class="section-header">
            <i class="bi bi-person-circle"></i>
            <h6>Student Information</h6>
          </div>
          <div class="detail-item">
            <span class="detail-label">Student Name</span>
            <div class="detail-value">${escapeHtml(studentName)}</div>
          </div>
          <div class="detail-item">
            <span class="detail-label">Email</span>
            <div class="detail-value">${escapeHtml(email)}</div>
          </div>
        </div>
      `
    }

    // Submission Details Section
    const title = submission.title || 'N/A'
    const category = submission.category || 'N/A'
    const description = submission.description || 'No description provided'
    
    modalBody.innerHTML += `
      <div class="detail-section">
        <div class="section-header">
          <i class="bi bi-file-text"></i>
          <h6>Submission Details</h6>
        </div>
        <div class="detail-item">
          <span class="detail-label">Title</span>
          <div class="detail-value">${escapeHtml(title)}</div>
        </div>
        <div class="detail-item">
          <span class="detail-label">Category</span>
          <div class="detail-value">${escapeHtml(category)}</div>
        </div>
        <div class="detail-item">
          <span class="detail-label">Description</span>
          <div class="detail-value long-text">${escapeHtml(description)}</div>
        </div>
      </div>
    `

    // Status and Priority Section
    const statusName = submission.status_name || 'Unknown'
    const priority = submission.priority || 'N/A'
    
    modalBody.innerHTML += `
      <div class="detail-section">
        <div class="section-header">
          <i class="bi bi-info-circle"></i>
          <h6>Status & Priority</h6>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <div class="detail-item">
              <span class="detail-label">Current Status</span>
              <div class="detail-value">
                <span class="status-badge-large ${getStatusClass(statusName)}">
                  <i class="bi bi-circle-fill"></i>
                  ${escapeHtml(statusName)}
                </span>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="detail-item">
              <span class="detail-label">Priority Level</span>
              <div class="detail-value">
                <span class="priority-badge-large ${getPriorityClass(priority)}">
                  <i class="bi bi-flag-fill"></i>
                  ${escapeHtml(priority)}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    `

    // Attachments Section
    const attachments = parseAttachments(submission.attachments)
    if (attachments.length > 0) {
      let attachmentsHTML = `
        <div class="detail-section">
          <div class="section-header">
            <i class="bi bi-paperclip"></i>
            <h6>Attachments (${attachments.length})</h6>
          </div>
          <div class="attachments-grid">
      `
      
      attachments.forEach(att => {
        attachmentsHTML += `
          <div class="attachment-card">
            <i class="bi ${getFileIcon(att.type)} attachment-icon"></i>
            <div class="attachment-info">
              <div class="attachment-name">${escapeHtml(att.filename)}</div>
              <div class="attachment-type">${escapeHtml(att.type)}</div>
            </div>
            <a href="${escapeHtml(att.path)}" target="_blank" class="attachment-download">
              <i class="bi bi-download"></i>
            </a>
          </div>
        `
      })
      
      attachmentsHTML += `
          </div>
        </div>
      `
      
      modalBody.innerHTML += attachmentsHTML
    }

    if (detailsModal) {
      detailsModal.show()
    }
  } catch (error) {
    showAlert("error", "Error", "Error opening details")
  }
}

function openResponseModal(submission) {
  try {
    if (submission.status_name !== "In Progress") {
      showAlert(
        "warning",
        "Cannot Respond",
        'You can only respond to submissions with "In Progress" status. Please update the status first.',
      )
      return
    }

    const isAnonymous = submission.is_anonymous == 1 || submission.is_anonymous === true

    const responseSubmissionId = document.getElementById("responseSubmissionId")
    const responseSubmissionType = document.getElementById("responseSubmissionType")
    const responseStudentId = document.getElementById("responseStudentId")
    const responseStudentName = document.getElementById("responseStudentName")
    const responseStudentEmail = document.getElementById("responseStudentEmail")
    const responseSubject = document.getElementById("responseSubject")
    const responseMessage = document.getElementById("responseMessage")
    const charCount = document.getElementById("charCount")
    const alertDiv = document.querySelector("#responseModal .alert")

    if (!responseSubmissionId || !responseSubmissionType || !responseStudentId) {
      showAlert("error", "Error", "Modal not properly initialized. Please refresh the page.")
      return
    }

    responseSubmissionId.value = submission.id
    responseSubmissionType.value = submission.type
    responseStudentId.value = submission.student_id

    if (isAnonymous) {
      if (responseStudentName) responseStudentName.textContent = "Anonymous Student"
      if (responseStudentEmail) responseStudentEmail.value = "(Anonymous - No email)"
      if (responseSubject) responseSubject.value = "Re: " + submission.title + " (Anonymous)"

      if (alertDiv) {
        alertDiv.innerHTML =
          '<i class="bi bi-exclamation-triangle me-2"></i><strong>Note:</strong> This is an anonymous submission. The response will be logged but notifications may be limited.'
        alertDiv.className = "alert alert-warning"
      }
    } else {
      if (responseStudentName) responseStudentName.textContent = `${submission.first_name} ${submission.last_name}`
      if (responseStudentEmail) responseStudentEmail.value = submission.email
      if (responseSubject) responseSubject.value = "Re: " + submission.title

      if (alertDiv) {
        alertDiv.innerHTML = `<i class="bi bi-info-circle me-2"></i><strong>Responding to:</strong> ${submission.first_name} ${submission.last_name}`
        alertDiv.className = "alert alert-info"
      }
    }

    if (responseMessage) responseMessage.value = ""
    if (charCount) charCount.textContent = "0"

    if (responseModal) {
      responseModal.show()
    }
  } catch (error) {
    showAlert("error", "Error", "Error opening response modal")
  }
}

function updateStatus(submissionId, type, currentStatusId) {
  try {
    const currentStatus = Number.parseInt(currentStatusId)

    if (currentStatus === 3 || currentStatus === 4) {
      showAlert(
        "info",
        "Status Cannot Be Changed",
        `This submission is already ${STATUS_NAMES[currentStatus]}. Final statuses cannot be changed.`,
      )
      return
    }

    const statusSubmissionId = document.getElementById("statusSubmissionId")
    const statusSubmissionType = document.getElementById("statusSubmissionType")
    const statusCurrentStatusId = document.getElementById("statusCurrentStatusId")
    const currentStatusDisplay = document.getElementById("currentStatusDisplay")
    const newStatusSelect = document.getElementById("newStatus")

    if (!statusSubmissionId || !statusSubmissionType || !statusCurrentStatusId || !newStatusSelect) {
      showAlert("error", "Error", "Status modal not properly initialized. Please refresh the page.")
      return
    }

    statusSubmissionId.value = submissionId
    statusSubmissionType.value = type
    statusCurrentStatusId.value = currentStatusId

    if (currentStatusDisplay) {
      currentStatusDisplay.value = STATUS_NAMES[currentStatus] || "Unknown"
    }

    const allowedStatuses = STATUS_FLOW[currentStatus] || []
    let optionsHTML = '<option value="">-- Select Status --</option>'

    allowedStatuses.forEach((statusId) => {
      optionsHTML += `<option value="${statusId}">${STATUS_NAMES[statusId]}</option>`
    })

    newStatusSelect.innerHTML = optionsHTML
    newStatusSelect.value = ""

    const statusAlertDiv = document.querySelector("#statusModal .alert")
    if (statusAlertDiv) {
      if (currentStatus === 1) {
        statusAlertDiv.innerHTML =
          '<i class="bi bi-arrow-right-circle me-2"></i>Status can only progress from <strong>Pending</strong> to <strong>In Progress</strong>.'
        statusAlertDiv.className = "alert alert-info"
      } else if (currentStatus === 2) {
        statusAlertDiv.innerHTML =
          '<i class="bi bi-arrow-right-circle me-2"></i>Status can progress from <strong>In Progress</strong> to <strong>Resolved</strong> or <strong>Rejected</strong>.'
        statusAlertDiv.className = "alert alert-info"
      }
    }

    if (statusModal) {
      statusModal.show()
    }
  } catch (error) {
    showAlert("error", "Error", "Error opening status modal")
  }
}

function getApiUrl() {
  const path = window.location.pathname
  const pathArray = path.split("/")
  const adminIndex = pathArray.indexOf("admin")

  if (adminIndex !== -1) {
    const baseUrl = window.location.origin + pathArray.slice(0, adminIndex + 1).join("/")
    return baseUrl + "/notif/process_response.php"
  }

  return "../notif/process_response.php"
}

function submitResponse(event) {
  event.preventDefault()

  const submissionId = document.getElementById("responseSubmissionId")?.value
  const submissionType = document.getElementById("responseSubmissionType")?.value
  const studentId = document.getElementById("responseStudentId")?.value
  const message = document.getElementById("responseMessage")?.value

  if (!submissionId || !submissionType || !message) {
    showAlert("error", "Missing Information", "Please fill in all required fields")
    return
  }

  const formData = new FormData()
  formData.append("action", "send_response")
  formData.append("submissionId", submissionId)
  formData.append("submissionType", submissionType)
  formData.append("studentId", studentId)
  formData.append("message", message)

  showLoadingAlert("Sending Response...", "Please wait while we send your response.")

  fetch(getApiUrl(), {
    method: "POST",
    body: formData,
    credentials: "same-origin",
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      return response.json()
    })
    .then((result) => {
      if (result.success) {
        showAlert("success", "Success!", result.message).then(() => {
          if (responseModal) responseModal.hide()
          location.reload()
        })
      } else {
        showAlert("error", "Error!", result.message || "Failed to send response.")
      }
    })
    .catch((error) => {
      showAlert("error", "Connection Error!", "Failed to connect to server")
    })
}

function confirmStatusUpdate(event) {
  event.preventDefault()

  const submissionId = document.getElementById("statusSubmissionId")?.value
  const submissionType = document.getElementById("statusSubmissionType")?.value
  const currentStatusId = document.getElementById("statusCurrentStatusId")?.value
  const newStatusId = document.getElementById("newStatus")?.value

  if (!newStatusId) {
    showAlert("warning", "Warning!", "Please select a status")
    return
  }

  const currentStatus = Number.parseInt(currentStatusId)
  const newStatus = Number.parseInt(newStatusId)

  const allowedStatuses = STATUS_FLOW[currentStatus] || []
  if (!allowedStatuses.includes(newStatus)) {
    showAlert(
      "error",
      "Invalid Status Change",
      `Cannot change status from ${STATUS_NAMES[currentStatus]} to ${STATUS_NAMES[newStatus]}. Please follow the proper status flow.`,
    )
    return
  }

  if (currentStatus === newStatus) {
    showAlert("info", "No Change", "The selected status is the same as the current status.")
    return
  }

  const formData = new FormData()
  formData.append("action", "update_status")
  formData.append("submissionId", submissionId)
  formData.append("submissionType", submissionType)
  formData.append("statusId", newStatusId)

  showLoadingAlert("Updating Status...", "Please wait while we update the status.")

  fetch(getApiUrl(), {
    method: "POST",
    body: formData,
    credentials: "same-origin",
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      return response.json()
    })
    .then((result) => {
      if (result.success) {
        showAlert("success", "Success!", result.message).then(() => {
          if (statusModal) statusModal.hide()
          location.reload()
        })
      } else {
        showAlert("error", "Error!", result.message || "Failed to update status.")
      }
    })
    .catch((error) => {
      showAlert("error", "Connection Error!", "Failed to connect to server")
    })
}

function escapeHtml(text) {
  if (!text) return ''
  const map = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
  }
  return String(text).replace(/[&<>"']/g, (m) => map[m])
}

function showAlert(icon, title, text) {
  if (Swal) {
    return Swal.fire({
      icon: icon,
      title: title,
      text: text,
      confirmButtonColor: "#c41e3a",
    })
  } else {
    alert(text || title)
    return Promise.resolve()
  }
}

function showLoadingAlert(title, text) {
  if (Swal) {
    Swal.fire({
      title: title,
      html: text,
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading()
      },
    })
  }
}