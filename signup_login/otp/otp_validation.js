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

      // This automatically handles folder names with spaces and URL encoding
      const sendSmsUrl = `otp/sms_otp/send_sms_otp.php`
      const verifySmsUrl = `otp/sms_otp/verify_sms_otp.php`
      const sendGmailUrl = `otp/gmail_otp/send_gmail_otp.php`
      const verifyGmailUrl = `otp/gmail_otp/verify_gmail_otp.php`

      console.log(
        "[v0] OTP URLs - SMS Send:",
        sendSmsUrl,
        "SMS Verify:",
        verifySmsUrl,
        "Gmail Send:",
        sendGmailUrl,
        "Gmail Verify:",
        verifyGmailUrl,
      )

      function validatePhone(phone) {
        return /^(\+63|0)?9\d{9}$/.test((phone || "").replace(/\s+/g, ""))
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

          Swal.fire({
            icon: "question",
            title: "Send OTP?",
            text: `An OTP will be sent to ${inputVal}.`,
            showCancelButton: true,
            confirmButtonText: "Send",
          }).then((r) => {
            if (!r.isConfirmed) return
            $btn.prop("disabled", true).text("Sending...")

            console.log("[v0] Sending SMS OTP request to:", sendSmsUrl)
            $.ajax({
              url: sendSmsUrl,
              method: "POST",
              data: { phone_number: inputVal },
              dataType: "json",
              success: (res) => {
                console.log("[v0] SMS OTP Response:", res)
                if (res.status) {
                  Swal.fire({ icon: "success", title: "OTP Sent", text: res.message })
                  form.find(".otp-placeholder").slideDown(200).show()
                  $btn.text("Sent")
                } else {
                  Swal.fire({ icon: "error", title: "Failed", text: res.message })
                  $btn.prop("disabled", false).text("Request OTP")
                }
              },
              error: (xhr, status, error) => {
                console.log("[v0] SMS AJAX Error:", xhr.status, xhr.responseText)
                Swal.fire({ icon: "error", title: "Error", text: "Server error sending OTP. Status: " + xhr.status })
                $btn.prop("disabled", false).text("Request OTP")
              },
            })
          })
        } else if (formType === "gmail") {
          if (!validateGmail(inputVal)) {
            Swal.fire({
              icon: "warning",
              title: "Invalid Email",
              text: "Please enter a valid Gmail or GSuite email address (e.g., user@gmail.com or user@company.com).",
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

            console.log("[v0] Sending Gmail OTP request to:", sendGmailUrl)
            $.ajax({
              url: sendGmailUrl,
              method: "POST",
              contentType: "application/json",
              data: JSON.stringify({ email: inputVal }),
              dataType: "json",
              success: (res) => {
                console.log("[v0] Gmail OTP Response:", res)
                if (res.status) {
                  Swal.fire({ icon: "success", title: "OTP Sent", text: res.message })
                  form.find(".otp-placeholder").slideDown(200).show()
                  $btn.text("Sent")
                } else {
                  Swal.fire({ icon: "error", title: "Failed", text: res.message })
                  $btn.prop("disabled", false).text("Request OTP")
                }
              },
              error: (xhr, status, error) => {
                console.log("[v0] Gmail AJAX Error:", xhr.status, xhr.responseText)
                Swal.fire({ icon: "error", title: "Error", text: "Server error sending OTP. Status: " + xhr.status })
                $btn.prop("disabled", false).text("Request OTP")
              },
            })
          })
        }
      })

      // ===== Verify OTP =====
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
          console.log("[v0] Verifying SMS OTP at:", verifySmsUrl)
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
          console.log("[v0] Verifying Gmail OTP at:", verifyGmailUrl)
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
    .catch((err) => console.error("[otp] init failed", err))
})()
