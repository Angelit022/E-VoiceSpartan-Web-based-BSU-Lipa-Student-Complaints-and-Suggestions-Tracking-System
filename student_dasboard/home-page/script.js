// Sidebar toggle
const sidebar = document.getElementById("sidebar");
const menuToggle = document.getElementById("menu-toggle");

menuToggle.addEventListener("click", () => {
  sidebar.classList.toggle("open");
});

// Sidebar links and sub-links toggle
document.querySelectorAll(".sidebar a").forEach(link => {
  link.addEventListener("click", (e) => {
    const subLinks = link.nextElementSibling;
    if (subLinks && subLinks.classList.contains("sub-links")) {
      e.preventDefault();
      subLinks.style.display = subLinks.style.display === "block" ? "none" : "block";
    }
  });
});

// Modal functionality
const modal = document.getElementById("info-modal");
const closeModal = document.getElementById("close-modal");
const openButtons = document.querySelectorAll(".open-modal");

openButtons.forEach(button => {
  button.addEventListener("click", () => {
    modal.style.display = "flex";
  });
});

closeModal.addEventListener("click", () => {
  modal.style.display = "none";
});

window.addEventListener("click", (e) => {
  if (e.target === modal) {
    modal.style.display = "none";
  }
});

// Editable fields logic
const editButtons = document.querySelectorAll(".edit-btn");

editButtons.forEach(btn => {
  btn.addEventListener("click", () => {
    const container = btn.closest(".editable-field");
    const input = container.querySelector("input");
    input.removeAttribute("readonly");
    input.focus();
    btn.style.display = "none";
    container.querySelector(".save-btn").style.display = "inline-block";
    container.querySelector(".cancel-btn").style.display = "inline-block";
  });
});

const saveButtons = document.querySelectorAll(".save-btn");

saveButtons.forEach(btn => {
  btn.addEventListener("click", () => {
    const container = btn.closest(".editable-field");
    const input = container.querySelector("input");
    input.setAttribute("readonly", true);
    btn.style.display = "none";
    container.querySelector(".cancel-btn").style.display = "none";
    container.querySelector(".edit-btn").style.display = "inline-block";
  });
});

const cancelButtons = document.querySelectorAll(".cancel-btn");

cancelButtons.forEach(btn => {
  btn.addEventListener("click", () => {
    const container = btn.closest(".editable-field");
    const input = container.querySelector("input");
    input.setAttribute("readonly", true);
    btn.style.display = "none";
    container.querySelector(".save-btn").style.display = "none";
    container.querySelector(".edit-btn").style.display = "inline-block";
  });
});
