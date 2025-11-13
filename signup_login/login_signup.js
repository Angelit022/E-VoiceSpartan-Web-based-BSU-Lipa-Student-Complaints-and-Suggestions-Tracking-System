const $ = window.jQuery
const Swal = window.Swal

let isSubmitting = false

$(document).ready(() => {
  // ===== SIGNUP VALIDATION =====
  if ($("#signupForm").length) {
    $("#signupForm").validate({
      rules: {
        first_name: { required: true, minlength: 2 },
        middle_initial: { maxlength: 1 },
        last_name: { required: true, minlength: 2 },
        email: { required: true, email: true },
        student_id: { required: true, minlength: 3 },
        phone_number: { required: true, minlength: 10 },
        password: { required: true, minlength: 5 },
        confirm_password: {
          required: true,
          minlength: 5,
          equalTo: "input[name='password']",
        },
      },
      messages: {
        first_name: { required: "First name is required", minlength: "At least 2 characters" },
        last_name: { required: "Last name is required", minlength: "At least 2 characters" },
        email: { required: "Email is required", email: "Please enter a valid email address" },
        student_id: { required: "Student ID is required", minlength: "At least 3 characters" },
        phone_number: { required: "Phone number is required", minlength: "At least 10 characters" },
        password: { required: "Password is required", minlength: "At least 5 characters" },
        confirm_password: {
          required: "Please confirm your password",
          minlength: "At least 5 characters",
          equalTo: "Passwords must match",
        },
      },
      submitHandler: (form) => {
        $.ajax({
          type: "POST",
          url: "signup_handler.php",
          data: $(form).serialize(),
          dataType: "json",
          success: (response) => {
            if (response.status) {
              Swal.fire({
                icon: "success",
                title: "Success!",
                text: response.message,
                allowOutsideClick: false,
                didClose: () => (window.location.href = "login.php"),
              })
            } else {
              Swal.fire({ icon: "error", title: "Signup Failed", text: response.message })
            }
          },
          error: () => {
            Swal.fire({
              icon: "error",
              title: "Error",
              text: "An error occurred. Please try again.",
            })
          },
        })
        return false
      },
    })
  }

  // ===== LOGIN VALIDATION =====
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
              console.error("[v0] Invalid response object")
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
                  console.error("[v0] Failed to load OTP component")
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
})
