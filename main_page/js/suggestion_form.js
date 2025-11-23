class SuggestionForm {
  constructor() {
    this.currentStep = 1
    this.totalSteps = 4
    this.form = document.getElementById("suggestionForm")
    this.prevBtn = document.getElementById("prevBtn")
    this.nextBtn = document.getElementById("nextBtn")
    this.submitBtn = document.getElementById("submitBtn")
    this.doneBtn = document.getElementById("doneBtn")
    this.anonymousCheckbox = document.getElementById("anonymous")
    this.anonymousInfo = document.getElementById("anonymousInfo")
    this.agreeCheckbox = document.getElementById("agree")
    this.Swal = window.Swal
    this.hasUnsavedChanges = false

    this.init()
  }

  init() {
    this.setupEventListeners()
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
              ". Are you sure you want to exit without completing your suggestion?",
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
      const area = document.querySelector('input[name="area"]:checked')
      if (!area) {
        this.Swal.fire("Missing Field", "Please select an improvement area.", "warning")
        return false
      }
    } else if (this.currentStep === 2) {
      const title = document.getElementById("title").value.trim()
      const priority = document.getElementById("priority").value
      const description = document.getElementById("description").value.trim()

      if (!title) {
        this.Swal.fire("Missing Field", "Please enter a suggestion title.", "warning")
        return false
      }

      if (!priority) {
        this.Swal.fire("Missing Field", "Please select a priority level.", "warning")
        return false
      }

      if (!description) {
        this.Swal.fire("Missing Field", "Please enter a detailed description.", "warning")
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
    } else if (this.currentStep === 3) {
      if (!this.agreeCheckbox.checked) {
        this.Swal.fire("Agreement Required", "Please agree to the terms and conditions.", "warning")
        return false
      }
      this.populateReview()
    }
    return true
  }

  populateReview() {
    const selectedArea = document.querySelector('input[name="area"]:checked')
    const reviewArea = document.getElementById("reviewArea")
    const reviewTitle = document.getElementById("reviewTitle")
    const reviewPriority = document.getElementById("reviewPriority")
    const reviewDescription = document.getElementById("reviewDescription")

    if (reviewArea) reviewArea.textContent = selectedArea ? selectedArea.value : "-"
    if (reviewTitle) reviewTitle.textContent = document.getElementById("title").value || "-"
    if (reviewPriority) reviewPriority.textContent = document.getElementById("priority").value || "-"
    if (reviewDescription) reviewDescription.textContent = document.getElementById("description").value || "-"
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

    if (step === 3) {
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
    } else if (this.currentStep === 2) {
      if (this.prevBtn) this.prevBtn.style.display = "inline-flex"
      if (this.nextBtn) this.nextBtn.style.display = "inline-flex"
    } else if (this.currentStep === 3) {
      if (this.prevBtn) this.prevBtn.style.display = "inline-flex"
      if (this.submitBtn) this.submitBtn.style.display = "inline-flex"
    } else if (this.currentStep === 4) {
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
      title: "Submitting Suggestion",
      html: "Your suggestion is being submitted. Please wait...",
      allowOutsideClick: false,
      didOpen: () => {
        this.Swal.showLoading()
      },
    })

    const formData = new FormData()
    const selectedArea = document.querySelector('input[name="area"]:checked')

    if (selectedArea) {
      formData.append("area", selectedArea.value)
    }
    formData.append("title", document.getElementById("title").value)
    formData.append("priority", document.getElementById("priority").value)
    formData.append("description", document.getElementById("description").value)
    formData.append("anonymous", this.anonymousCheckbox.checked ? "1" : "0")

    fetch("process_suggestion.php", {
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
        const data = JSON.parse(text)

        if (data.success) {
          this.Swal.fire({
            icon: "success",
            title: "Suggestion Submitted Successfully!",
            html: "<p>Your suggestion has been recorded and will be reviewed by our team.</p>",
            confirmButtonText: "View Confirmation",
            allowOutsideClick: false,
            didClose: () => {
              this.showSuccessStep(data.suggestion_id, data.is_anonymous)
            },
          })
        } else {
          this.Swal.fire("Submission Failed", data.message || "An error occurred.", "error")
        }
      })
      .catch((error) => {
        console.error("Error:", error.message)
        this.Swal.fire(
          "Submission Failed",
          "An error occurred while submitting your suggestion. Please try again.",
          "error",
        )
      })
  }

  handleSubmit(e) {
    e.preventDefault()
    this.submitForm()
  }

  showSuccessStep(suggestionId, isAnonymous) {
    this.currentStep = 4
    const referenceId = "SEVS-" + String(suggestionId).padStart(5, "0")
    const referenceIdEl = document.getElementById("referenceId")

    if (referenceIdEl) {
      referenceIdEl.textContent = referenceId
    }

    for (let i = 1; i <= 3; i++) {
      const stepPanel = document.getElementById(`step${i}`)
      if (stepPanel) {
        stepPanel.style.display = "none"
        stepPanel.classList.remove("active")
      }
    }

    const step4 = document.getElementById("step4")
    if (step4) {
      step4.style.display = "block"
      step4.classList.add("active")
    }

    if (isAnonymous) {
      const anonymousAlert = document.getElementById("step4AnonymousAlert")
      if (anonymousAlert) {
        anonymousAlert.style.display = "flex"
      }
    }

    this.updateStepIndicators()
    this.updateButtonsVisibility()

    if (this.doneBtn) {
      this.doneBtn.onclick = () => {
        window.location.href = "../profile/profile.php"
      }
    }

    window.scrollTo({ top: 0, behavior: "smooth" })
  }
}

document.addEventListener("DOMContentLoaded", () => {
  window.suggestionForm = new SuggestionForm()
})