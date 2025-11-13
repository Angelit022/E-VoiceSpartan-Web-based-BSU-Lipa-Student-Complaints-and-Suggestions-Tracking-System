document.addEventListener("DOMContentLoaded", () => {
  // Mobile menu toggle (if needed)
  const navLinks = document.querySelectorAll(".nav-link")
  navLinks.forEach((link) => {
    link.addEventListener("click", function () {
      // Update active state
      navLinks.forEach((l) => l.classList.remove("active"))
      this.classList.add("active")
    })
  })

  // Icon button interactions
  const notificationBtn = document.querySelector(".notification-btn")
  const profileBtn = document.querySelector(".profile-btn")

  if (notificationBtn) {
    notificationBtn.addEventListener("click", () => {
      console.log("Notifications clicked")
      // Add notification menu logic here
    })
  }

  if (profileBtn) {
    profileBtn.addEventListener("click", () => {
      console.log("Profile clicked")
      // Add profile menu logic here
    })
  }
})
