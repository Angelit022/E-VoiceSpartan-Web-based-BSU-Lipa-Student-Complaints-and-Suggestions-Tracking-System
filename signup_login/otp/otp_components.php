<?php
$base_url = trim(dirname($_SERVER['PHP_SELF']), '/');
if (strpos($base_url, '/signup_login') !== false) {
    $base_url = '/signup_login';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Select SMS or Gmail</title>
  <script>
    window.OTP_SMS_SEND_URL = '<?php echo $base_url; ?>/otp/sms_otp/send_sms_otp.php';
    window.OTP_SMS_VERIFY_URL = '<?php echo $base_url; ?>/otp/sms_otp/verify_sms_otp.php';
    window.OTP_GMAIL_SEND_URL = '<?php echo $base_url; ?>/otp/gmail_otp/send_gmail_otp.php';
    window.OTP_GMAIL_VERIFY_URL = '<?php echo $base_url; ?>/otp/gmail_otp/verify_gmail_otp.php';
    
    console.log('[v0] OTP URLs initialized:');
    console.log('  SMS Send:', window.OTP_SMS_SEND_URL);
    console.log('  SMS Verify:', window.OTP_SMS_VERIFY_URL);
    console.log('  Gmail Send:', window.OTP_GMAIL_SEND_URL);
    console.log('  Gmail Verify:', window.OTP_GMAIL_VERIFY_URL);
  </script>
</head>
<body>

<div id="otp-component-root">
  <div class="login-box otp-box">
    <h2>OTP Verification</h2>
    <p class="otp-note">Choose how you want to receive your One-Time Password (OTP)</p>

    <div class="d-flex gap-2 mb-3 otp-option-row">
      <button id="smsOption" class="btn btn-outline-primary flex-fill otp-option">
        <i class="bi bi-chat-dots"></i> Via SMS
      </button>
      <button id="gmailOption" class="btn btn-outline-danger flex-fill otp-option">
        <i class="bi bi-envelope"></i> Via Gmail
      </button>
    </div>

    <div id="otpFormContainer"></div>

    <p class="mt-3 text-center">
      <a href="login.php" class="back-login-link">← Back to Login</a>
    </p>
  </div>

  <template id="sms-template">
    <form class="otp-form" data-form-type="sms">
      <div class="input-group mb-2">
        <input type="tel" class="form-control" placeholder="Registered Mobile Number" required>
        <button type="button" class="btn request-btn btn-danger">Request</button>
      </div>
      <div class="otp-placeholder" style="display: none;">
        <div class="input-group mb-2">
          <input type="text" class="form-control" placeholder="Enter OTP" maxlength="6" required>
          <button type="button" class="btn send-btn btn-success">Verify</button>
        </div>
      </div>
    </form>
  </template>

  <template id="gmail-template">
    <form class="otp-form" data-form-type="gmail">
      <div class="input-group mb-2">
        <input type="email" class="form-control" placeholder="Registered Email" required>
        <button type="button" class="btn request-btn btn-danger">Request</button>
      </div>
      <div class="otp-placeholder" style="display: none;">
        <div class="input-group mb-2">
          <input type="text" class="form-control" placeholder="Enter OTP" maxlength="6" required>
          <button type="button" class="btn send-btn btn-success">Verify</button>
        </div>
      </div>
    </form>
  </template>
</div>

</body>
</html>
