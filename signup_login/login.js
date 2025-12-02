const $ = window.jQuery
const Swal = window.Swal

let isSubmitting = false

$(document).ready(() => {
  $("#toggle-login-password").on("click", function() {
    const $passwordInput = $("#login-password")
    const type = $passwordInput.attr("type") === "password" ? "text" : "password"
    $passwordInput.attr("type", type)

    const $icon = $(this).find("i")
    if (type === "text") {
      $icon.removeClass("bi-eye").addClass("bi-eye-slash")
    } else {
      $icon.removeClass("bi-eye-slash").addClass("bi-eye")
    }
  })

  if ($("#loginForm").length) {
    $("#loginForm").validate({
      rules: {
        student_id: { required: true, minlength: 3 },
        password: { required: true, minlength: 5 },
      },
      messages: {
        student_id: { required: "Student ID is required", minlength: "At least 3 characters" },
        password: { required: "Password is required", minlength: "At least 5 characters" },
      },
      submitHandler: (form) => {
        if (isSubmitting) return false
        isSubmitting = true

        const $submitBtn = $(form).find('button[type="submit"]')
        $submitBtn.prop("disabled", true).text("Logging in...")

        const formData = $(form).serialize()

        $.ajax({
          type: "POST",
          url: "login_handler.php",
          data: formData,
          dataType: "json",
          success: (res) => {
            if (!res || typeof res !== "object") {
              console.error("[login] Invalid response object")
              Swal.fire({
                icon: "error",
                title: "Login Failed",
              })
              isSubmitting = false
              $submitBtn.prop("disabled", false).text("Login")
              return
            }

            if (res.status === true) {
              Swal.fire({
                icon: "success",
                title: "Login Successful!",
                showConfirmButton: false,
                timer: 1000,
              }).then(() => {
                $.get("otp/otp_components.php", (fragment) => {
                  $("#login-section").html(fragment)
                  $.getScript("otp/otp_validation.js")
                }).fail(() => {
                  console.error("[login] Failed to load OTP component")
                  Swal.fire({
                    icon: "error",
                    title: "Login Failed",
                  })
                  isSubmitting = false
                  $submitBtn.prop("disabled", false).text("Login")
                })
              })
            } else {
              Swal.fire({
                icon: "error",
                title: "Login Failed",
                text: res.message || "Invalid credentials",
              })
              isSubmitting = false
              $submitBtn.prop("disabled", false).text("Login")
            }
          },
          error: (xhr, status, error) => {
            Swal.fire({
              icon: "error",
              title: "Login Failed",
            })
            isSubmitting = false
            $submitBtn.prop("disabled", false).text("Login")
          },
        })
        return false
      },
    })
  }

  if (window.location.search.includes('error=')) {
    const urlParams = new URLSearchParams(window.location.search);
    const errorMsg = urlParams.get('error');
    if (errorMsg) {
      Swal.fire({
        icon: 'error',
        title: 'Sign Up Error',
        text: decodeURIComponent(errorMsg)
      });
      window.history.replaceState({}, document.title, window.location.pathname);
    }
  }
})