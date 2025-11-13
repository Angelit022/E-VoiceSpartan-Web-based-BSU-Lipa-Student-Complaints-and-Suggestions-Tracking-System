class ComplaintForm {
  constructor() {
    this.currentStep = 1
    this.totalSteps = 5
    this.form = document.getElementById("complaintForm")
    this.prevBtn = document.getElementById("prevBtn")
    this.nextBtn = document.getElementById("nextBtn")
    this.submitBtn = document.getElementById("submitBtn")
    this.doneBtn = document.getElementById("doneBtn")
    this.anonymousCheckbox = document.getElementById("anonymous")
    this.anonymousInfo = document.getElementById("anonymousInfo")
    this.agreeCheckbox = document.getElementById("agree")
    this.Swal = window.Swal
    this.hasUnsavedChanges = false
    this.uploadedFileName = null

    this.init()
  }

  init() {
    this.setupEventListeners()
    this.setupFileUpload()
    this.showStep(1)
  }

  setupEventListeners() {
    if (this.prevBtn) {
      this.prevBtn.addEventListener("click", (e) => {
        e.preventDefault()
        this.previousStep()
      })
    }
    if (this.nextBtn) {
      this.nextBtn.addEventListener("click", (e) => {
        e.preventDefault()
        this.nextStep()
      })
    }
    if (this.submitBtn) {
      this.submitBtn.addEventListener("click", (e) => {
        e.preventDefault()
        this.submitForm()
      })
    }
    if (this.anonymousCheckbox) {
      this.anonymousCheckbox.addEventListener("change", () => this.toggleAnonymousInfo())
    }
    if (this.form) {
      this.form.addEventListener("submit", (e) => this.handleSubmit(e))
    }

    this.setupNavigationValidation()
  }

  setupNavigationValidation() {
    const navLinks = document.querySelectorAll("a.nav-link, .navbar-brand")
    navLinks.forEach((link) => {
      link.addEventListener("click", (e) => {
        if (this.currentStep >= 2) {
          e.preventDefault()
          this.Swal.fire({
            title: "Leave Form?",
            text:
              "You are currently on Step " +
              this.currentStep +
              ". Are you sure you want to exit without completing your complaint?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#837779ff",
            cancelButtonColor: "#3269d5ff",
            confirmButtonText: "Yes, Exit",
            cancelButtonText: "Continue",
          }).then((result) => {
            if (result.isConfirmed) {
              window.location.href = link.href
            }
          })
        }
      })
    })
  }

  setupFileUpload() {
    const fileUploadArea = document.getElementById("fileUploadArea")
    const fileInput = document.getElementById("attachment")
    if (!fileUploadArea || !fileInput) return

    fileUploadArea.addEventListener("click", () => fileInput.click())

    fileUploadArea.addEventListener("dragover", (e) => {
      e.preventDefault()
      fileUploadArea.style.backgroundColor = "var(--color-gray-light)"
    })

    fileUploadArea.addEventListener("dragleave", () => {
      fileUploadArea.style.backgroundColor = "transparent"
    })

    fileUploadArea.addEventListener("drop", (e) => {
      e.preventDefault()
      fileUploadArea.style.backgroundColor = "transparent"
      if (e.dataTransfer.files.length > 0) {
        fileInput.files = e.dataTransfer.files
        this.handleFileSelect()
      }
    })

    fileInput.addEventListener("change", () => this.handleFileSelect())
  }

  handleFileSelect() {
    const fileInput = document.getElementById("attachment")
    const fileList = document.getElementById("fileList")
    if (!fileInput.files || fileInput.files.length === 0) {
      fileList.innerHTML = ""
      this.uploadedFileName = null
      return
    }

    const file = fileInput.files[0]
    const maxSize = 10 * 1024 * 1024

    if (file.size > maxSize) {
      this.Swal.fire("File Too Large", "Maximum file size is 10MB.", "warning")
      fileInput.value = ""
      fileList.innerHTML = ""
      this.uploadedFileName = null
      return
    }

    const validExtensions = ["jpg", "jpeg", "png", "mp4", "pdf", "doc", "docx"]
    const fileExtension = file.name.split(".").pop().toLowerCase()

    if (!validExtensions.includes(fileExtension)) {
      this.Swal.fire("Invalid File Type", "Please upload a supported file type.", "warning")
      fileInput.value = ""
      fileList.innerHTML = ""
      this.uploadedFileName = null
      return
    }

    this.uploadedFileName = file.name
    fileList.innerHTML = `
      <div class="file-item" style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; background: var(--color-gray-light); border-radius: 0.5rem; margin-top: 0.5rem; border: 1px solid #ddd;">
        <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1;">
          <i class="bi bi-check-circle" style="color: var(--color-green); font-size: 1.2rem;"></i>
          <span style="color: var(--color-black); font-weight: 500;">${file.name}</span>
        </div>
        <button type="button" class="btn-remove-file" style="background: none; border: none; color: #dc3545; cursor: pointer; font-size: 1.5rem; padding: 0 0.5rem; display: flex; align-items: center; transition: color 0.2s;" title="Remove file">
          <i class="bi bi-x-circle-fill"></i>
        </button>
      </div>
    `
    
    // Add event listener to remove button
    const removeBtn = fileList.querySelector('.btn-remove-file')
    if (removeBtn) {
      removeBtn.addEventListener('click', () => this.removeFile())
      // Add hover effect
      removeBtn.addEventListener('mouseenter', (e) => {
        e.currentTarget.style.color = '#bd2130'
      })
      removeBtn.addEventListener('mouseleave', (e) => {
        e.currentTarget.style.color = '#dc3545'
      })
    }
  }

  removeFile() {
    const fileInput = document.getElementById("attachment")
    const fileList = document.getElementById("fileList")
    
    fileInput.value = ""
    fileList.innerHTML = ""
    this.uploadedFileName = null
  }

  toggleAnonymousInfo() {
    if (this.anonymousInfo) {
      this.anonymousInfo.style.display = this.anonymousCheckbox.checked ? "flex" : "none"
    }
  }

  updateStepIndicators() {
    for (let i = 1; i <= this.totalSteps; i++) {
      const indicator = document.getElementById(`step${i}-indicator`)
      if (!indicator) continue

      if (i < this.currentStep) {
        indicator.classList.remove("active")
        indicator.classList.add("completed")
      } else if (i === this.currentStep) {
        indicator.classList.remove("completed")
        indicator.classList.add("active")
      } else {
        indicator.classList.remove("active", "completed")
      }
    }
  }

  validateCurrentStep() {
    if (this.currentStep === 1) {
      const category = document.getElementById("category").value
      if (!category) {
        this.Swal.fire("Missing Field", "Please select a complaint category.", "warning")
        return false
      }
    } else if (this.currentStep === 2) {
      const title = document.getElementById("title").value.trim()
      const description = document.getElementById("description").value.trim()
      const priority = document.getElementById("priority").value

      if (!title) {
        this.Swal.fire("Missing Field", "Please enter a complaint title.", "warning")
        return false
      }
      if (!description) {
        this.Swal.fire("Missing Field", "Please enter a detailed description.", "warning")
        return false
      }
      if (!priority) {
        this.Swal.fire("Missing Field", "Please select a priority level.", "warning")
        return false
      }

      if (description.length < 10) {
        this.Swal.fire("Too Short", "Description must be at least 10 characters.", "warning")
        return false
      }

      const bannedWords = ["fuck", "shit", "bitch", "asshole", "idiot", "stupid"]
      for (const word of bannedWords) {
        if (description.toLowerCase().includes(word)) {
          this.Swal.fire("Inappropriate Content", "Please revise your description before continuing.", "warning")
          return false
        }
      }
    } else if (this.currentStep === 4) {
      if (!this.agreeCheckbox.checked) {
        this.Swal.fire("Agreement Required", "Please agree to the terms and conditions.", "warning")
        return false
      }
      this.populateReview()
    }
    return true
  }

  populateReview() {
    const reviewCategory = document.getElementById("reviewCategory")
    const reviewPriority = document.getElementById("reviewPriority")
    const reviewTitle = document.getElementById("reviewTitle")
    const reviewDescription = document.getElementById("reviewDescription")
    const reviewAttachment = document.getElementById("reviewAttachment")

    if (reviewCategory) reviewCategory.textContent = document.getElementById("category").value || "-"
    if (reviewPriority) reviewPriority.textContent = document.getElementById("priority").value || "-"
    if (reviewTitle) reviewTitle.textContent = document.getElementById("title").value || "-"
    if (reviewDescription) reviewDescription.textContent = document.getElementById("description").value || "-"
    if (reviewAttachment) reviewAttachment.textContent = this.uploadedFileName || "None"
  }

  showStep(step) {
    for (let i = 1; i <= this.totalSteps; i++) {
      const stepPanel = document.getElementById(`step${i}`)
      if (stepPanel) {
        stepPanel.style.display = "none"
        stepPanel.classList.remove("active")
      }
    }

    const currentStepPanel = document.getElementById(`step${step}`)
    if (currentStepPanel) {
      currentStepPanel.style.display = "block"
      currentStepPanel.classList.add("active")
    }

    this.updateStepIndicators()
    this.updateButtonsVisibility()

    if (step === 4) {
      this.populateReview()
      this.toggleAnonymousInfo()
    }

    window.scrollTo({ top: 0, behavior: "smooth" })
  }

  updateButtonsVisibility() {
    if (this.submitBtn) this.submitBtn.style.display = "none"
    if (this.nextBtn) this.nextBtn.style.display = "none"
    if (this.doneBtn) this.doneBtn.style.display = "none"
    if (this.prevBtn) this.prevBtn.style.display = "none"

    if (this.currentStep === 1) {
      if (this.nextBtn) this.nextBtn.style.display = "inline-flex"
    } else if (this.currentStep === 2 || this.currentStep === 3) {
      if (this.prevBtn) this.prevBtn.style.display = "inline-flex"
      if (this.nextBtn) this.nextBtn.style.display = "inline-flex"
    } else if (this.currentStep === 4) {
      if (this.prevBtn) this.prevBtn.style.display = "inline-flex"
      if (this.submitBtn) this.submitBtn.style.display = "inline-flex"
    } else if (this.currentStep === 5) {
      if (this.doneBtn) this.doneBtn.style.display = "inline-flex"
    }
  }

  previousStep() {
    if (this.currentStep > 1) {
      this.currentStep--
      this.showStep(this.currentStep)
    }
  }

  nextStep() {
    if (this.validateCurrentStep() && this.currentStep < this.totalSteps) {
      this.currentStep++
      this.showStep(this.currentStep)
    }
  }

  submitForm() {
    if (!this.agreeCheckbox.checked) {
      this.Swal.fire("Agreement Required", "Please agree to the terms and conditions.", "warning")
      return
    }

    this.Swal.fire({
      icon: "info",
      title: "Submitting Complaint",
      html: "Your complaint is being submitted. Please wait...",
      allowOutsideClick: false,
      didOpen: () => {
        this.Swal.showLoading()
      },
    })

    const formData = new FormData()
    formData.append("category", document.getElementById("category").value)
    formData.append("title", document.getElementById("title").value)
    formData.append("description", document.getElementById("description").value)
    formData.append("priority", document.getElementById("priority").value)
    formData.append("anonymous", this.anonymousCheckbox.checked ? "1" : "0")

    const fileInput = document.getElementById("attachment")
    if (fileInput.files && fileInput.files.length > 0) {
      formData.append("attachment", fileInput.files[0])
    }

    fetch("process_complaint.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`)
        }
        return response.text()
      })
      .then((text) => {
        let data
        try {
          data = JSON.parse(text)
        } catch (e) {
          throw new Error("Invalid response from server. Please try again.")
        }

        if (data.success) {
          this.Swal.fire({
            icon: "success",
            title: "Complaint Submitted Successfully!",
            html: "<p>Your complaint has been recorded and will be reviewed by our team.</p>",
            confirmButtonText: "View Confirmation",
            allowOutsideClick: false,
            didClose: () => {
              this.showSuccessStep(data.complaint_id, data.is_anonymous)
            },
          })
        } else {
          this.Swal.fire("Submission Failed", data.message || "An error occurred.", "error")
        }
      })
      .catch((error) => {
        this.Swal.fire(
          "Submission Failed",
          "An error occurred while submitting your complaint. Please try again.",
          "error",
        )
      })
  }

  handleSubmit(e) {
    e.preventDefault()
    this.submitForm()
  }

  showSuccessStep(complaintId, isAnonymous) {
    this.currentStep = 5
    const referenceId = "CEVS-" + String(complaintId).padStart(5, "0")
    const referenceIdEl = document.getElementById("referenceId")

    if (referenceIdEl) {
      referenceIdEl.textContent = referenceId
    }

    for (let i = 1; i <= 4; i++) {
      const stepPanel = document.getElementById(`step${i}`)
      if (stepPanel) {
        stepPanel.style.display = "none"
        stepPanel.classList.remove("active")
      }
    }

    const step5 = document.getElementById("step5")
    if (step5) {
      step5.style.display = "block"
      step5.classList.add("active")
    }

    if (isAnonymous) {
      const anonymousAlert = document.getElementById("step5AnonymousAlert")
      if (anonymousAlert) {
        anonymousAlert.style.display = "flex"
      }
    }

    this.updateStepIndicators()
    this.updateButtonsVisibility()

    if (this.doneBtn) {
      this.doneBtn.onclick = () => {
        window.location.href = "../homepage.php"
      }
    }

    window.scrollTo({ top: 0, behavior: "smooth" })
  }
}

document.addEventListener("DOMContentLoaded", () => {
  window.complaintForm = new ComplaintForm()
})