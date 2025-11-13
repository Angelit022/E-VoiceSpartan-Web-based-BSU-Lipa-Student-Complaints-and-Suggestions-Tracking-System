document.addEventListener("DOMContentLoaded", () => {
  // Sidebar toggle
  const sidebar = document.getElementById("sidebarMenu")
  const overlay = document.getElementById("overlay")
  const menuToggleBtn = document.getElementById("menuToggleBtn")
  const closeBtn = document.querySelector(".close-btn")

  // Right menu toggle
  const rightMenuBar = document.getElementById("rightMenuBar")
  const rightOverlay = document.getElementById("rightOverlay")
  const dotsMenuBtn = document.getElementById("dotsMenuBtn")
  const closeRightBtn = document.querySelector(".close-btn-right")

  // Sidebar events
  if (menuToggleBtn) {
    menuToggleBtn.addEventListener("click", () => {
      sidebar.classList.add("active")
      overlay.classList.add("active")
    })
  }

  if (closeBtn) {
    closeBtn.addEventListener("click", () => {
      sidebar.classList.remove("active")
      overlay.classList.remove("active")
    })
  }

  if (overlay) {
    overlay.addEventListener("click", () => {
      sidebar.classList.remove("active")
      overlay.classList.remove("active")
    })
  }

  // Close sidebar when a link is clicked
  document.querySelectorAll(".sidebar-item").forEach((item) => {
    item.addEventListener("click", () => {
      sidebar.classList.remove("active")
      overlay.classList.remove("active")
    })
  })

  // Right menu events
  if (dotsMenuBtn) {
    dotsMenuBtn.addEventListener("click", () => {
      rightMenuBar.classList.add("active")
      rightOverlay.classList.add("active")
    })
  }

  if (closeRightBtn) {
    closeRightBtn.addEventListener("click", () => {
      rightMenuBar.classList.remove("active")
      rightOverlay.classList.remove("active")
    })
  }

  if (rightOverlay) {
    rightOverlay.addEventListener("click", () => {
      rightMenuBar.classList.remove("active")
      rightOverlay.classList.remove("active")
    })
  }

  // Close right menu when a link is clicked
  document.querySelectorAll(".right-menu-item").forEach((item) => {
    item.addEventListener("click", () => {
      rightMenuBar.classList.remove("active")
      rightOverlay.classList.remove("active")
    })
  })
})
