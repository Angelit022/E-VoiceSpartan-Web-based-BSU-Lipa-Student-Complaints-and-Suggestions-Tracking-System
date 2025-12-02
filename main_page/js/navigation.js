document.addEventListener("DOMContentLoaded", () => {
  const navLinks = document.querySelectorAll(".nav-link")
  navLinks.forEach((link) => {
    link.addEventListener("click", function () {
      navLinks.forEach((l) => l.classList.remove("active"))
      this.classList.add("active")
    })
  })

  const notificationBtn = document.querySelector(".notification-btn")
  const profileBtn = document.querySelector(".profile-btn")

  if (notificationBtn) {
    notificationBtn.addEventListener("click", () => {
      console.log("Notifications clicked")
    })
  }

  if (profileBtn) {
    profileBtn.addEventListener("click", () => {
      console.log("Profile clicked")
    })
  }
})





//check this file