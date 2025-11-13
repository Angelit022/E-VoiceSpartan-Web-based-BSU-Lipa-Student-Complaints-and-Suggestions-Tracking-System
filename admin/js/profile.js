//Handles admin CRUD with validation and SweetAlert notifications
document.addEventListener("DOMContentLoaded", () => {
  console.log("Profile page loaded")

  const urlParams = new URLSearchParams(window.location.search)
  const message = urlParams.get("message")
  const type = urlParams.get("type") 

  console.log("URL Parameters:", {
    message: message,
    type: type,
    fullURL: window.location.search
  })

  if (message) {
    // Determine icon based on type
    const icon = type === "success" ? "success" : "error"
    const title = type === "success" ? "Success!" : "Error"
    
    console.log("Showing SweetAlert:", { icon, title, type })
    
    window.Swal.fire({
      icon: icon,
      title: title,
      text: decodeURIComponent(message),
      confirmButtonColor: "#dc3545",
      timer: 3000,
      timerProgressBar: true,
    })
  }

  // Form validation
  const createForm = document.getElementById("createAdminForm")
  if (createForm) {
    createForm.addEventListener("submit", (e) => {
      if (!validateCreateForm()) {
        e.preventDefault()
      }
    })
  }

  const editForm = document.getElementById("editAdminForm")
  if (editForm) {
    editForm.addEventListener("submit", (e) => {
      if (!validateEditForm()) {
        e.preventDefault()
      }
    })
  }
})


//Validate create admin form
function validateCreateForm() {
  const errorDiv = document.getElementById("createErrors")
  const errors = []

  const name = document.getElementById("create_name").value.trim()
  const email = document.getElementById("create_email").value.trim()
  const phone = document.getElementById("create_phone").value.trim()
  const password = document.getElementById("create_password").value

  if (name.length < 2) {
    errors.push("Name must be at least 2 characters")
  }

  if (!isValidEmail(email)) {
    errors.push("Invalid email format")
  }

  if (!isValidPhone(phone)) {
    errors.push("Invalid phone format (use 09123456789 or +639123456789)")
  }

  if (password.length < 6) {
    errors.push("Password must be at least 6 characters")
  }

  if (errors.length > 0) {
    errorDiv.innerHTML = errors.join("<br>")
    errorDiv.classList.remove("d-none")
    return false
  }

  errorDiv.classList.add("d-none")
  return true
}

//Validate edit admin form
function validateEditForm() {
  const errorDiv = document.getElementById("editErrors")
  const errors = []

  const name = document.getElementById("edit_name").value.trim()
  const email = document.getElementById("edit_email").value.trim()
  const phone = document.getElementById("edit_phone").value.trim()

  if (name.length < 2) {
    errors.push("Name must be at least 2 characters")
  }

  if (!isValidEmail(email)) {
    errors.push("Invalid email format")
  }

  if (!isValidPhone(phone)) {
    errors.push("Invalid phone format (use 09123456789 or +639123456789)")
  }

  if (errors.length > 0) {
    errorDiv.innerHTML = errors.join("<br>")
    errorDiv.classList.remove("d-none")
    return false
  }

  errorDiv.classList.add("d-none")
  return true
}


 // Validate email format
function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}


// Validate phone format
function isValidPhone(phone) {
  const phoneRegex = /^(09|\+639)\d{9}$/
  return phoneRegex.test(phone)
}


 // Edit Admin
function editAdmin(admin) {
  console.log("Editing admin:", admin)
  document.getElementById("edit_admin_id").value = admin.admin_id
  document.getElementById("edit_name").value = admin.name
  document.getElementById("edit_email").value = admin.email
  document.getElementById("edit_phone").value = admin.phone_number
  document.getElementById("edit_role").value = admin.role
  document.getElementById("editErrors").classList.add("d-none")
  new window.bootstrap.Modal(document.getElementById("editAdminModal")).show()
}


function deleteAdmin(id) {
  window.Swal.fire({
    title: "Delete Admin?",
    text: "This action cannot be undone",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#dc3545",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Delete",
  }).then((result) => {
    if (result.isConfirmed) {
      const form = document.createElement("form")
      form.method = "POST"
      form.action = "./process/process_profile.php"

      const actionInput = document.createElement("input")
      actionInput.type = "hidden"
      actionInput.name = "action"
      actionInput.value = "delete"

      const idInput = document.createElement("input")
      idInput.type = "hidden"
      idInput.name = "admin_id"
      idInput.value = id

      form.appendChild(actionInput)
      form.appendChild(idInput)
      document.body.appendChild(form)
      form.submit()
    }
  })
}