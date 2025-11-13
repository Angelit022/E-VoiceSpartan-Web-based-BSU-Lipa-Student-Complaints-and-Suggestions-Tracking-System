function resetSettings() {
  Swal.fire({
    title: "Reset Settings?",
    text: "This will restore all settings to default",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#dc3545",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Reset",
  }).then((result) => {
    if (result.isConfirmed) {
      // Reset dark mode toggle
      const darkModeToggle = document.getElementById("darkModeToggle")
      if (darkModeToggle) {
        darkModeToggle.checked = false
      }

      Swal.fire({
        icon: "success",
        title: "Done!",
        text: "Settings have been reset",
        confirmButtonColor: "#dc3545",
      })
    }
  })
}

// Initialize on page load
document.addEventListener("DOMContentLoaded", function () {
  // Future: Add dark mode toggle functionality here
  const darkModeToggle = document.getElementById("darkModeToggle")
  if (darkModeToggle) {
    darkModeToggle.addEventListener("change", function () {
      // This will be implemented when dark mode is ready
      console.log("Dark mode toggle:", this.checked)
    })
  }
})