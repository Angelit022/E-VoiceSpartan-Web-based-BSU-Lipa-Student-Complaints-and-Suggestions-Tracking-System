/**
 * ComplaintForm Class
 * Handles complaint form wizard with step navigation, validation, and submission
 */

class ComplaintForm {
  constructor() {
    this.currentStep = 1
    this.totalSteps = 5
    this.form = document.getElementById("complaintForm")
    this.prevBtn = document.getElementById("prevBtn")
    this.nextBtn = document.getElementById("nextBtn")
    this.submitBtn = document.getElementById("submitBtn")
    this.doneBtn = document.getElementById("doneBtn")
    this.agreeCheckbox = document.getElementById("agree")
    this.anonymousCheckbox = document.getElementById("anonymous")
    this.anonymousInfo = document.getElementById("anonymousInfo")
    this.Swal = window.Swal

    this.attachmentFiles = [] // Array to store multiple files
    this.fileUploadArea = document.getElementById("fileUploadArea")
    this.fileInput = document.getElementById("attachment")
    this.fileList = document.getElementById("fileList")
    this.MAX_FILES = 5
    this.MAX_TOTAL_SIZE = 10 * 1024 * 1024 // 10MB total

    this.init()
  }

  init() {
    this.setupEventListeners()
    this.setupFileHandling()
    this.showStep(1)
  }

  setupEventListeners() {
    this.prevBtn.addEventListener("click", () => this.previousStep())
    this.nextBtn.addEventListener("click", () => this.nextStep())
    this.submitBtn.addEventListener("click", (e) => this.handleSubmit(e))
    this.doneBtn.addEventListener("click", () => this.redirectToHome())
    this.anonymousCheckbox.addEventListener("change", () => this.toggleAnonymousInfo())
  }

  setupFileHandling() {
    if (!this.fileUploadArea || !this.fileInput) return

    // Click to upload
    this.fileUploadArea.addEventListener("click", () => this.fileInput.click())

    this.fileInput.addEventListener("change", (e) => {
      this.handleFileSelect(e.target.files)

    
    })

    // Drag and drop
    this.fileUploadArea.addEventListener("dragover", (e) => {
      e.preventDefault()
      this.fileUploadArea.classList.add("dragover")
    })

    this.fileUploadArea.addEventListener("dragleave", () => {
      this.fileUploadArea.classList.remove("dragover")
    })

    this.fileUploadArea.addEventListener("drop", (e) => {
      e.preventDefault()
      this.fileUploadArea.classList.remove("dragover")
      this.handleFileSelect(e.dataTransfer.files)
    })
  }

  handleFileSelect(files) {
    if (files.length === 0) return

    const allowedTypes = [
      "image/jpeg",
      "image/png",
      "video/mp4",
      "application/pdf",
      "application/msword",
      "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
    ]
    const maxFileSize = 10 * 1024 * 1024 // 10MB per file

    // Check how many files can still be added
    const remainingSlots = this.MAX_FILES - this.attachmentFiles.length
    if (remainingSlots <= 0) {
      this.Swal.fire({
        icon: "error",
        title: "Maximum files reached",
        text: `You can only upload up to ${this.MAX_FILES} files.`,
      })
      return
    }

    // Process each file
    let totalSize = this.attachmentFiles.reduce((sum, f) => sum + f.size, 0)
    let addedCount = 0

    for (let i = 0; i < files.length && addedCount < remainingSlots; i++) {
      const file = files[i]

      // Validate file type
      if (!allowedTypes.includes(file.type)) {
        this.Swal.fire({
          icon: "error",
          title: "Invalid file type",
          text: `${file.name} - Allowed: JPG, PNG, MP4, PDF, DOC, DOCX`,
        })
        continue
      }

      // Validate individual file size
      if (file.size > maxFileSize) {
        this.Swal.fire({
          icon: "error",
          title: "File too large",
          text: `${file.name} exceeds 10MB limit.`,
        })
        continue
      }

      // Check total size
      if (totalSize + file.size > this.MAX_TOTAL_SIZE) {
        this.Swal.fire({
          icon: "warning",
          title: "Total size limit reached",
          text: `Adding ${file.name} would exceed the 10MB total limit.`,
        })
        break
      }

      this.attachmentFiles.push(file)
      totalSize += file.size
      addedCount++
    }

    if (addedCount > 0) {
      this.displayFileList()
    }
  }

  displayFileList() {
    if (!this.fileList) return

    this.fileList.innerHTML = ""
    this.attachmentFiles.forEach((file, index) => {
      const fileSize = (file.size / 1024).toFixed(2)
      const li = document.createElement("li")
      li.innerHTML = `
        <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1;">
          <i class="bi bi-file-earmark-check" style="color: var(--color-green); font-size: 1.25rem;"></i>
          <div>
            <div style="font-weight: 500; color: var(--color-black);">${file.name}</div>
            <div style="font-size: 0.75rem; color: var(--color-gray);">${fileSize} KB</div>
          </div>
        </div>
        <button type="button" class="remove-file-btn" data-index="${index}" style="background: none; border: none; color: var(--color-red); cursor: pointer; font-size: 1.25rem;">
          <i class="bi bi-x"></i>
        </button>
      `
      this.fileList.appendChild(li)

      const removeBtn = li.querySelector(".remove-file-btn")
      removeBtn.addEventListener("click", (e) => {
        e.preventDefault()
        this.removeFile(index)
      })
    })
    this.updateReviewAttachment()
  }

  updateReviewAttachment() {
    const attachmentDisplay = document.getElementById("reviewAttachment")
    if (attachmentDisplay) {
      if (this.attachmentFiles.length > 0) {
        const fileNames = this.attachmentFiles.map((f) => f.name).join(", ")
        attachmentDisplay.textContent = fileNames
      } else {
        attachmentDisplay.textContent = "None"
      }
    }
  }

  removeFile(index) {
    this.attachmentFiles.splice(index, 1)
    this.displayFileList()
  }

  toggleAnonymousInfo() {
    if (this.anonymousCheckbox.checked) {
      this.anonymousInfo.style.display = "flex"
    } else {
      this.anonymousInfo.style.display = "none"
    }
  }

  updateStepIndicators() {
    for (let i = 1; i <= this.totalSteps; i++) {
      const indicator = document.getElementById(`step${i}-indicator`)
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

  updateButtonsVisibility() {
    this.submitBtn.style.display = "none"
    this.doneBtn.style.display = "none"
    this.nextBtn.style.display = "none"

    if (this.currentStep === 1) {
      this.prevBtn.style.display = "none"
      this.nextBtn.style.display = "inline-flex"
    } else if (this.currentStep === 4) {
      this.prevBtn.style.display = "inline-flex"
      this.submitBtn.style.display = "inline-flex"
    } else if (this.currentStep === 5) {
      this.prevBtn.style.display = "none"
      this.nextBtn.style.display = "none"
      this.doneBtn.style.display = "inline-flex"
    } else {
      this.prevBtn.style.display = "inline-flex"
      this.nextBtn.style.display = "inline-flex"
    }
  }

  validateCurrentStep() {
    if (this.currentStep === 1) {
      const category = document.getElementById("category").value
      if (!category) {
        this.Swal.fire({ icon: "warning", title: "Missing field", text: "Please select a complaint category." })
        return false
      }
    } else if (this.currentStep === 2) {
      const title = document.getElementById("title").value.trim()
      const description = document.getElementById("description").value.trim()
      const priority = document.getElementById("priority").value

      if (!title) {
        this.Swal.fire({ icon: "warning", title: "Missing field", text: "Please enter a complaint title." })
        return false
      }
      if (!description) {
        this.Swal.fire({ icon: "warning", title: "Missing field", text: "Please enter a detailed description." })
        return false
      }
      if (!priority) {
        this.Swal.fire({ icon: "warning", title: "Missing field", text: "Please select a priority level." })
        return false
      }

      const bannedWords = [
        "fuck",
        "shit",
        "bitch",
        "asshole",
        "idiot",
        "stupid",
        "offensive",
        "inappropriate",
        "vulgar",
      ]
      const descriptionLower = description.toLowerCase()
      for (const word of bannedWords) {
        if (descriptionLower.includes(word.toLowerCase())) {
          this.Swal.fire({
            icon: "warning",
            title: "Inappropriate Content",
            text: "Your description contains inappropriate words. Please revise before continuing.",
          })
          return false
        }
      }
    } else if (this.currentStep === 3) {
      return true
    } else if (this.currentStep === 4) {
      if (!this.agreeCheckbox.checked) {
        this.Swal.fire({
          icon: "warning",
          title: "Agreement required",
          text: "Please agree to the terms and conditions before submitting.",
        })
        return false
      }
      this.populateReview()
    }
    return true
  }

  populateReview() {
    document.getElementById("reviewCategory").textContent = document.getElementById("category").value || "-"
    document.getElementById("reviewTitle").textContent = document.getElementById("title").value || "-"
    document.getElementById("reviewPriority").textContent = document.getElementById("priority").value || "-"
    document.getElementById("reviewDescription").textContent = document.getElementById("description").value || "-"

    this.updateReviewAttachment()
  }

  showStep(step) {
    // Hide all steps
    for (let i = 1; i <= this.totalSteps; i++) {
      const stepPanel = document.getElementById(`step${i}`)
      if (stepPanel) {
        stepPanel.style.display = "none"
        stepPanel.classList.remove("active")
      }
    }

    // Show current step
    const currentStepPanel = document.getElementById(`step${step}`)
    if (currentStepPanel) {
      currentStepPanel.style.display = "block"
      currentStepPanel.classList.add("active")
    }

    // Update indicators
    this.updateStepIndicators()
    this.updateButtonsVisibility()

    if (step === 4) {
      this.populateReview()
      this.toggleAnonymousInfo()
    }

    window.scrollTo({ top: 0, behavior: "smooth" })
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

  handleSubmit(e) {
    e.preventDefault()

    if (!this.agreeCheckbox.checked) {
      this.Swal.fire({
        icon: "warning",
        title: "Agreement required",
        text: "Please agree to the terms and conditions before submitting.",
      })
      return
    }

    const Swal = window.Swal
    Swal.fire({
      icon: "info",
      title: "Submitting Complaint",
      html: "Your complaint is being submitted. Please wait...",
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading()
      },
    })

    setTimeout(() => {
      const formData = new FormData(this.form)

      this.attachmentFiles.forEach((file, index) => {
        formData.append(`attachment[]`, file)
      })

      fetch("process_complaint.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            Swal.close()
            Swal.fire({
              icon: "success",
              title: "Complaint Submitted Successfully!",
              confirmButtonText: "Continue",
              allowOutsideClick: false,
            }).then(() => {
              this.showSuccessStep(data.complaint_id, data.is_anonymous)
            })
          } else {
            Swal.fire({
              icon: "error",
              title: "Submission Failed",
              text: data.message || "An error occurred while submitting your complaint.",
            })
          }
        })
        .catch((error) => {
          console.error("[v0] Error:", error)
          Swal.fire({
            icon: "error",
            title: "Submission Failed",
            text: "An error occurred while submitting your complaint.",
          })
        })
    }, 500)
  }

  showSuccessStep(complaintId, isAnonymous = false) {
    this.currentStep = 5

    const referenceId = "CEVP-" + String(complaintId).padStart(6, "0")
    document.getElementById("referenceId").textContent = referenceId

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
      const step5AnonymousAlert = document.getElementById("step5AnonymousAlert")
      if (step5AnonymousAlert) {
        step5AnonymousAlert.style.display = "flex"
      }
    }

    // Update step indicators and buttons
    this.updateStepIndicators()
    this.updateButtonsVisibility()

    window.scrollTo({ top: 0, behavior: "smooth" })
  }

  redirectToHome() {
    window.location.href = "../homepage.php"
  }
}

document.addEventListener("DOMContentLoaded", () => {
  window.complaintForm = new ComplaintForm()
})
