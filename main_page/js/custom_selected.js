/**
 * Custom Select Dropdown Component
 */

class CustomSelect {
  constructor(selector) {
    this.wrapper = document.querySelector(selector)
    if (!this.wrapper) return

    this.trigger = this.wrapper.querySelector(".select-trigger")
    this.options = this.wrapper.querySelectorAll(".select-option")
    this.dropdown = this.wrapper.querySelector(".select-options")
    this.isOpen = false

    this.init()
  }

  init() {
    this.setupEventListeners()
  }

  setupEventListeners() {
    // Trigger click
    this.trigger.addEventListener("click", () => this.toggle())

    // Option clicks
    this.options.forEach((option) => {
      option.addEventListener("click", () => this.selectOption(option))
    })

    // Close on outside click
    document.addEventListener("click", (e) => {
      if (!this.wrapper.contains(e.target)) {
        this.close()
      }
    })

    // Keyboard navigation
    this.trigger.addEventListener("keydown", (e) => {
      if (e.key === "ArrowDown" || e.key === "Enter") {
        e.preventDefault()
        this.open()
      }
    })

    this.dropdown.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        this.close()
        this.trigger.focus()
      }
    })
  }

  toggle() {
    this.isOpen ? this.close() : this.open()
  }

  open() {
    this.isOpen = true
    this.dropdown.classList.add("active")
    this.trigger.setAttribute("aria-expanded", "true")
    // Focus first option
    this.options[0]?.focus()
  }

  close() {
    this.isOpen = false
    this.dropdown.classList.remove("active")
    this.trigger.setAttribute("aria-expanded", "false")
  }

  selectOption(option) {
    const value = option.getAttribute("data-value") || option.textContent
    this.trigger.textContent = option.textContent
    this.trigger.setAttribute("data-value", value)

    // Update hidden input if exists
    const input = this.wrapper.querySelector('input[type="hidden"]')
    if (input) {
      input.value = value
    }

    // Update selected styling
    this.options.forEach((opt) => opt.classList.remove("selected"))
    option.classList.add("selected")

    this.close()
  }
}
