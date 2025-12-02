;(() => {
  const MAX_WAIT_MS = 3000
  const CHECK_INTERVAL_MS = 50

  function waitFor(libChecker, timeoutMs) {
    const start = Date.now()
    return new Promise((resolve, reject) => {
      ;(function tick() {
        if (libChecker()) return resolve()
        if (Date.now() - start >= timeoutMs) return reject(new Error("timeout"))
        setTimeout(tick, CHECK_INTERVAL_MS)
      })()
    })
  }

  waitFor(() => typeof window.jQuery !== "undefined" && typeof window.Swal !== "undefined", MAX_WAIT_MS)
    .then(() => {
      const $ = window.jQuery
      const Swal = window.Swal

      const sendSmsUrl = `otp/sms_otp/send_sms_otp.php`
      const verifySmsUrl = `otp/sms_otp/verify_sms_otp.php`
      const sendGmailUrl = `otp/gmail_otp/send_gmail_otp.php`
      const verifyGmailUrl = `otp/gmail_otp/verify_gmail_otp.php`
      const validateUrl = `otp/validate_contact.php`

      function validatePhone(phone) {
        return /^(0|(\+63))?9\d{9}$/.test((phone || "").replace(/\s+/g, ""))
      }
      function validateGmail(email) {
        const trimmedEmail = (email || "").trim()
        const isGmail = /^[a-zA-Z0-9._%-]+@gmail\.com$/.test(trimmedEmail)
        const isBatStateU = /^[a-zA-Z0-9._%-]+@g\.batstate-u\.edu\.ph$/.test(trimmedEmail)
        return isGmail || isBatStateU
      }
      function validateOTP(otp) {
        return /^\d{6}$/.test((otp || "").trim())
      }

      // ===== Option Selection =====
      $(document).on("click", "#smsOption", () => {
        const tpl = document.querySelector("#sms-template")
        if (!tpl) return Swal.fire({ icon: "error", title: "Error", text: "SMS template missing." })
        $("#otpFormContainer").html(tpl.content.cloneNode(true))
      })

      $(document).on("click", "#gmailOption", () => {
        const tpl = document.querySelector("#gmail-template")
        if (!tpl) return Swal.fire({ icon: "error", title: "Error", text: "Gmail template missing." })
        $("#otpFormContainer").html(tpl.content.cloneNode(true))
      })

      // ===== Request OTP =====
      $(document).on("click", ".request-btn", function () {
        const $btn = $(this)
        const form = $btn.closest(".otp-form")
        const inputVal = (form.find("input.form-control").first().val() || "").trim()
        const formType = (form.attr("data-form-type") || "").toLowerCase()

        if (formType === "sms") {
          if (!validatePhone(inputVal)) {
            Swal.fire({
              icon: "warning",
              title: "Invalid Number",
              text: "Please enter a valid PH mobile number (e.g. 09123456789).",
            })
            return
          }

          $.ajax({
            url: validateUrl,
            method: "POST",
            contentType: "application/json",
            data: JSON.stringify({ contact_type: "phone", contact_value: inputVal }),
            dataType: "json",
            success: (validation) => {
              if (!validation.status) {
                Swal.fire({
                  icon: "warning",
                  title: "Invalid Phone",
                  text: validation.message,
                })
                return
              }

              Swal.fire({
                icon: "question",
                title: "Send OTP?",
                text: `An OTP will be sent to ${inputVal}.`,
                showCancelButton: true,
                confirmButtonText: "Send",
              }).then((r) => {
                if (!r.isConfirmed) return
                $btn.prop("disabled", true).text("Sending...")

                $.ajax({
                  url: sendSmsUrl,
                  method: "POST",
                  data: { phone_number: inputVal },
                  dataType: "json",
                  success: (res) => {
                    if (res.status) {
                      Swal.fire({ icon: "success", title: "OTP Sent", text: res.message })
                      form.find(".otp-placeholder").slideDown(200).show()
                      $btn.text("Sent")
                    } else {
                      Swal.fire({ icon: "error", title: "Failed", text: res.message })
                      $btn.prop("disabled", false).text("Request OTP")
                    }
                  },
                  error: (xhr) => {
                    Swal.fire({
                      icon: "error",
                      title: "Error",
                      text: "Server error sending OTP. Status: " + xhr.status,
                    })
                    $btn.prop("disabled", false).text("Request OTP")
                  },
                })
              })
            },
            error: () => {
              Swal.fire({
                icon: "error",
                title: "Error",
                text: "Validation failed. Please check your information and try again.",
              })
            },
          })
        } else if (formType === "gmail") {
          if (!validateGmail(inputVal)) {
            Swal.fire({
              icon: "warning",
              title: "Invalid Email",
              text: "Please enter a valid Gsuite Account.",
            })
            return
          }

          $.ajax({
            url: validateUrl,
            method: "POST",
            contentType: "application/json",
            data: JSON.stringify({ contact_type: "email", contact_value: inputVal }),
            dataType: "json",
            success: (validation) => {
              if (!validation.status) {
                Swal.fire({
                  icon: "warning",
                  title: "Invalid Email",
                  text: validation.message,
                })
                return
              }

              Swal.fire({
                icon: "question",
                title: "Send OTP?",
                text: `An OTP will be sent to ${inputVal}.`,
                showCancelButton: true,
                confirmButtonText: "Send",
              }).then((r) => {
                if (!r.isConfirmed) return
                $btn.prop("disabled", true).text("Sending...")

                $.ajax({
                  url: sendGmailUrl,
                  method: "POST",
                  contentType: "application/json",
                  data: JSON.stringify({ email: inputVal }),
                  dataType: "json",
                  success: (res) => {
                    if (res.status) {
                      Swal.fire({ icon: "success", title: "OTP Sent", text: res.message })
                      form.find(".otp-placeholder").slideDown(200).show()
                      $btn.text("Sent")
                    } else {
                      Swal.fire({ icon: "error", title: "Failed", text: res.message })
                      $btn.prop("disabled", false).text("Request OTP")
                    }
                  },
                  error: (xhr) => {
                    Swal.fire({
                      icon: "error",
                      title: "Error",
                      text: "Server error sending OTP. Status: " + xhr.status,
                    })
                    $btn.prop("disabled", false).text("Request OTP")
                  },
                })
              })
            },
            error: () => {
              Swal.fire({
                icon: "error",
                title: "Error",
                text: "Validation failed. Please check your information and try again.",
              })
            },
          })
        }
      })

      $(document).on("click", ".send-btn", function () {
        const $btn = $(this)
        const form = $btn.closest(".otp-form")
        const otpVal = (form.find('input[placeholder="Enter OTP"]').val() || "").trim()
        const formType = (form.attr("data-form-type") || "").toLowerCase()

        if (!validateOTP(otpVal)) {
          Swal.fire({ icon: "warning", title: "Invalid OTP", text: "OTP must be 6 digits." })
          return
        }

        if (formType === "sms") {
          $btn.prop("disabled", true).text("Verifying...")
          $.ajax({
            url: verifySmsUrl,
            method: "POST",
            data: { otp: otpVal },
            dataType: "json",
            success: (res) => {
              if (res.status) {
                Swal.fire({
                  icon: "success",
                  title: "Login Successful",
                  text: res.message,
                  allowOutsideClick: false,
                }).then(() => {
                  window.location.href = res.redirect
                })
              } else {
                Swal.fire({ icon: "error", title: "Verification Failed", text: res.message })
                $btn.prop("disabled", false).text("Verify OTP")
              }
            },
            error: () => {
              Swal.fire({ icon: "error", title: "Error", text: "Verification failed. Try again." })
              $btn.prop("disabled", false).text("Verify OTP")
            },
          })
        } else if (formType === "gmail") {
          $btn.prop("disabled", true).text("Verifying...")
          $.ajax({
            url: verifyGmailUrl,
            method: "POST",
            contentType: "application/json",
            data: JSON.stringify({ otp: otpVal }),
            dataType: "json",
            success: (res) => {
              if (res.status) {
                Swal.fire({
                  icon: "success",
                  title: "Login Successful",
                  text: res.message,
                  allowOutsideClick: false,
                }).then(() => {
                  window.location.href = res.redirect
                })
              } else {
                Swal.fire({ icon: "error", title: "Verification Failed", text: res.message })
                $btn.prop("disabled", false).text("Verify OTP")
              }
            },
            error: () => {
              Swal.fire({ icon: "error", title: "Error", text: "Verification failed. Try again." })
              $btn.prop("disabled", false).text("Verify OTP")
            },
          })
        }
      })
    })
    .catch((err) => console.error("[OTP] Failed to initialize:", err))
})()