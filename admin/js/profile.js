document.addEventListener("DOMContentLoaded", () => {
  console.log("Profile page loaded")

  const urlParams = new URLSearchParams(window.location.search)
  const message = urlParams.get("message")
  const type = urlParams.get("type")

  if (message) {
    const icon = type === "success" ? "success" : "error"
    const title = type === "success" ? "Success!" : "Error"

    window.Swal.fire({
      icon: icon,
      title: title,
      text: decodeURIComponent(message),
      confirmButtonColor: "#dc3545",
      timer: 3000,
      timerProgressBar: true,
    }).then(() => {
      if (window.history.replaceState) {
        const cleanUrl = window.location.pathname + "?page=profile"
        window.history.replaceState({}, document.title, cleanUrl)
      }
    })
  }

  // Add jQuery validation rules
  if (typeof window.$ !== "undefined" && typeof window.$.validator !== "undefined") {
    window.$.validator.addMethod(
      "gsuiteEmail",
      function (value, element) {
        return this.optional(element) || /^[a-zA-Z0-9._%-]+@g\.batstate-u\.edu\.ph$/i.test(value)
      },
      "SSC Admins must use BSU G-Suite account (@g.batstate-u.edu.ph)",
    )
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

  if (name.length > 100) {
    errors.push("Name must not exceed 100 characters")
  }

  if (!isGSuiteEmail(email)) {
    errors.push("SSC Admins must use BSU G-Suite account (@g.batstate-u.edu.ph)")
  }

  if (!isValidPhone(phone)) {
    errors.push("Invalid phone format (use 09123456789 or +639123456789)")
  }

  if (password.length < 6) {
    errors.push("Password must be at least 6 characters")
  }

  if (password.length > 50) {
    errors.push("Password must not exceed 50 characters")
  }

  if (errors.length > 0) {
    errorDiv.innerHTML = "<strong>Please fix the following errors:</strong><br>" + errors.join("<br>")
    errorDiv.classList.remove("d-none")
    errorDiv.scrollIntoView({ behavior: "smooth", block: "nearest" })
    return false
  }

  errorDiv.classList.add("d-none")
  return true
}

function validateEditForm() {
  const errorDiv = document.getElementById("editErrors")
  const errors = []

  const name = document.getElementById("edit_name").value.trim()
  const email = document.getElementById("edit_email").value.trim()
  const phone = document.getElementById("edit_phone").value.trim()

  if (name.length < 2) {
    errors.push("Name must be at least 2 characters")
  }

  if (name.length > 100) {
    errors.push("Name must not exceed 100 characters")
  }

  if (!isGSuiteEmail(email)) {
    errors.push("SSC Admins must use BSU G-Suite account (@g.batstate-u.edu.ph)")
  }

  if (!isValidPhone(phone)) {
    errors.push("Invalid phone format (use 09123456789 or +639123456789)")
  }

  if (errors.length > 0) {
    errorDiv.innerHTML = "<strong>Please fix the following errors:</strong><br>" + errors.join("<br>")
    errorDiv.classList.remove("d-none")
    errorDiv.scrollIntoView({ behavior: "smooth", block: "nearest" })
    return false
  }

  errorDiv.classList.add("d-none")
  return true
}

function isGSuiteEmail(email) {
  email = email.trim().toLowerCase()
  const gsuiteRegex = /^[a-zA-Z0-9._%-]+@g\.batstate-u\.edu\.ph$/i
  return gsuiteRegex.test(email)
}

function isValidPhone(phone) {
  const cleanPhone = phone.replace(/\s+/g, "")
  const phoneRegex = /^(09|\+639)\d{9}$/
  return phoneRegex.test(cleanPhone)
}

function editAdmin(admin) {
  console.log("Editing admin:", admin)

  const errorDiv = document.getElementById("editErrors")
  if (errorDiv) {
    errorDiv.classList.add("d-none")
  }

  const form = document.getElementById("editAdminForm")
  if (form) {
    form.reset()
  }

  document.getElementById("edit_admin_id").value = admin.admin_id
  document.getElementById("edit_name").value = admin.name || ""
  document.getElementById("edit_email").value = admin.email || ""
  document.getElementById("edit_phone").value = admin.phone_number || ""
  document.getElementById("edit_is_active").value = admin.is_active !== undefined ? admin.is_active : 1

  const editModal = new window.bootstrap.Modal(document.getElementById("editAdminModal"), {
    backdrop: "static",
    keyboard: true,
  })
  editModal.show()
}

function deleteAdmin(id, name) {
  window.Swal.fire({
    title: "Delete Admin?",
    html: `Are you sure you want to delete <strong>${escapeHtml(name)}</strong>?<br><br>
           <small class="text-muted">Note: If this admin has responses in the system, the account will be deactivated instead of deleted.</small>`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#dc3545",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Delete",
    cancelButtonText: "Cancel",
    reverseButtons: true,
    allowOutsideClick: false,
  }).then((result) => {
    if (result.isConfirmed) {
      window.Swal.fire({
        title: "Processing...",
        text: "Please wait while we process your request",
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          window.Swal.showLoading()
        },
      })

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

      setTimeout(() => {
        form.submit()
      }, 500)
    }
  })
}

function escapeHtml(text) {
  const map = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
  }
  return text.replace(/[&<>"']/g, (m) => map[m])
}

document.addEventListener("DOMContentLoaded", () => {
  const createModal = document.getElementById("createAdminModal")
  const editModal = document.getElementById("editAdminModal")

  if (createModal) {
    createModal.addEventListener("hidden.bs.modal", () => {
      const form = document.getElementById("createAdminForm")
      const errorDiv = document.getElementById("createErrors")
      if (form) form.reset()
      if (errorDiv) errorDiv.classList.add("d-none")

      form.querySelectorAll(".is-valid, .is-invalid").forEach((el) => {
        el.classList.remove("is-valid", "is-invalid")
      })

      document.querySelectorAll(".modal-backdrop").forEach((backdrop) => backdrop.remove())
    })
  }

  if (editModal) {
    editModal.addEventListener("hidden.bs.modal", () => {
      const form = document.getElementById("editAdminForm")
      const errorDiv = document.getElementById("editErrors")
      if (form) form.reset()
      if (errorDiv) errorDiv.classList.add("d-none")

      form.querySelectorAll(".is-valid, .is-invalid").forEach((el) => {
        el.classList.remove("is-valid", "is-invalid")
      })

      document.querySelectorAll(".modal-backdrop").forEach((backdrop) => backdrop.remove())
    })
  }
})

document.addEventListener("DOMContentLoaded", () => {
  const createName = document.getElementById("create_name")
  const createEmail = document.getElementById("create_email")
  const createPhone = document.getElementById("create_phone")
  const createPassword = document.getElementById("create_password")

  if (createName) {
    createName.addEventListener("blur", () => {
      if (createName.value.trim().length < 2) {
        createName.classList.add("is-invalid")
        createName.classList.remove("is-valid")
      } else {
        createName.classList.remove("is-invalid")
        createName.classList.add("is-valid")
      }
    })
  }

  if (createEmail) {
    createEmail.addEventListener("blur", () => {
      if (!isGSuiteEmail(createEmail.value.trim())) {
        createEmail.classList.add("is-invalid")
        createEmail.classList.remove("is-valid")

        let feedback = createEmail.nextElementSibling
        if (!feedback || !feedback.classList.contains("invalid-feedback")) {
          feedback = document.createElement("div")
          feedback.className = "invalid-feedback"
          createEmail.parentNode.insertBefore(feedback, createEmail.nextSibling)
        }
        feedback.textContent = "Must use BSU G-Suite account (@g.batstate-u.edu.ph)"
        feedback.style.display = "block"
      } else {
        createEmail.classList.remove("is-invalid")
        createEmail.classList.add("is-valid")
        const feedback = createEmail.nextElementSibling
        if (feedback && feedback.classList.contains("invalid-feedback")) {
          feedback.style.display = "none"
        }
      }
    })

    createEmail.addEventListener("keyup", () => {
      const email = createEmail.value.trim().toLowerCase()
      if (email.length > 0) {
        if (isGSuiteEmail(email)) {
          createEmail.classList.remove("is-invalid")
          createEmail.classList.add("is-valid")
        } else {
          createEmail.classList.add("is-invalid")
          createEmail.classList.remove("is-valid")
        }
      }
    })
  }

  if (createPhone) {
    createPhone.addEventListener("blur", () => {
      if (!isValidPhone(createPhone.value.trim())) {
        createPhone.classList.add("is-invalid")
        createPhone.classList.remove("is-valid")
      } else {
        createPhone.classList.remove("is-invalid")
        createPhone.classList.add("is-valid")
      }
    })
  }

  if (createPassword) {
    createPassword.addEventListener("blur", () => {
      if (createPassword.value.length < 6) {
        createPassword.classList.add("is-invalid")
        createPassword.classList.remove("is-valid")
      } else {
        createPassword.classList.remove("is-invalid")
        createPassword.classList.add("is-valid")
      }
    })
  }

  const editName = document.getElementById("edit_name")
  const editEmail = document.getElementById("edit_email")
  const editPhone = document.getElementById("edit_phone")

  if (editName) {
    editName.addEventListener("blur", () => {
      if (editName.value.trim().length < 2) {
        editName.classList.add("is-invalid")
        editName.classList.remove("is-valid")
      } else {
        editName.classList.remove("is-invalid")
        editName.classList.add("is-valid")
      }
    })
  }

  if (editEmail) {
    editEmail.addEventListener("blur", () => {
      if (!isGSuiteEmail(editEmail.value.trim())) {
        editEmail.classList.add("is-invalid")
        editEmail.classList.remove("is-valid")

        let feedback = editEmail.nextElementSibling
        if (!feedback || !feedback.classList.contains("invalid-feedback")) {
          feedback = document.createElement("div")
          feedback.className = "invalid-feedback"
          editEmail.parentNode.insertBefore(feedback, editEmail.nextSibling)
        }
        feedback.textContent = "Must use BSU G-Suite account (@g.batstate-u.edu.ph)"
        feedback.style.display = "block"
      } else {
        editEmail.classList.remove("is-invalid")
        editEmail.classList.add("is-valid")
        const feedback = editEmail.nextElementSibling
        if (feedback && feedback.classList.contains("invalid-feedback")) {
          feedback.style.display = "none"
        }
      }
    })

    editEmail.addEventListener("keyup", () => {
      const email = editEmail.value.trim().toLowerCase()
      if (email.length > 0) {
        if (isGSuiteEmail(email)) {
          editEmail.classList.remove("is-invalid")
          editEmail.classList.add("is-valid")
        } else {
          editEmail.classList.add("is-invalid")
          editEmail.classList.remove("is-valid")
        }
      }
    })
  }

  if (editPhone) {
    editPhone.addEventListener("blur", () => {
      if (!isValidPhone(editPhone.value.trim())) {
        editPhone.classList.add("is-invalid")
        editPhone.classList.remove("is-valid")
      } else {
        editPhone.classList.remove("is-invalid")
        editPhone.classList.add("is-valid")
      }
    })
  }
})
