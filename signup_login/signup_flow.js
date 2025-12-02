const $ = window.jQuery;
const Swal = window.Swal;

// SHA-256 hash function
async function sha256(message) {
  const msgBuffer = new TextEncoder().encode(message);
  const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
  const hashArray = Array.from(new Uint8Array(hashBuffer));
  const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
  return hashHex;
}

$(document).ready(() => {
  
  // STEP 1: Mobile Number Validation
  if ($('#mobileForm').length) {
    const $phoneInput = $('#phone_number');
    const $validation = $('#mobile-validation');
    const $submitBtn = $('#mobile-submit');
    
    $phoneInput.on('keydown', function(e) {
      if ([8, 9, 27, 13, 46, 35, 36, 37, 39].indexOf(e.keyCode) !== -1 ||
          (e.keyCode === 65 && e.ctrlKey) ||
          (e.keyCode === 67 && e.ctrlKey) ||
          (e.keyCode === 86 && e.ctrlKey) ||
          (e.keyCode === 88 && e.ctrlKey)) {
        return;
      }
      
      if ((e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105)) {
        e.preventDefault();
      }
      
      if (this.value.length >= 11 && [8, 46, 37, 39].indexOf(e.keyCode) === -1) {
        e.preventDefault();
      }
    });
    
    $phoneInput.on('paste', function(e) {
      e.preventDefault();
      const pastedText = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
      const numericOnly = pastedText.replace(/\D/g, '').substring(0, 11);
      this.value = numericOnly;
      validatePhone();
    });
    
    $phoneInput.on('input', validatePhone);
    
    function validatePhone() {
      const value = $phoneInput.val();
      
      if (value.length === 0) {
        $validation.html('').removeClass('valid invalid');
        $phoneInput.removeClass('valid invalid');
        $submitBtn.prop('disabled', true);
        return;
      }
      
      if (value.length < 11) {
        $validation.html('Must be exactly 11 digits').removeClass('valid').addClass('invalid');
        $phoneInput.removeClass('valid').addClass('invalid');
        $submitBtn.prop('disabled', true);
        return;
      }
      
      if (!value.startsWith('09')) {
        $validation.html('Must start with 09').removeClass('valid').addClass('invalid');
        $phoneInput.removeClass('valid').addClass('invalid');
        $submitBtn.prop('disabled', true);
        return;
      }
      
      if (!/^09\d{9}$/.test(value)) {
        $validation.html('Invalid phone number format').removeClass('valid').addClass('invalid');
        $phoneInput.removeClass('valid').addClass('invalid');
        $submitBtn.prop('disabled', true);
        return;
      }
      
      $validation.html('Valid phone number').removeClass('invalid').addClass('valid');
      $phoneInput.removeClass('invalid').addClass('valid');
      $submitBtn.prop('disabled', false);
    }
    
    $('#mobileForm').on('submit', function(e) {
      e.preventDefault();
      
      const phoneNumber = $phoneInput.val();
      
      if (!/^09\d{9}$/.test(phoneNumber)) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid Phone Number',
          text: 'Please enter a valid 11-digit phone number starting with 09'
        });
        return;
      }
      
      $submitBtn.prop('disabled', true).text('Validating...');
      
      $.ajax({
        type: 'POST',
        url: 'signup_handler.php',
        data: { step: 'mobile', phone_number: phoneNumber },
        dataType: 'json',
        success: (response) => {
          if (response.status) {
            window.location.reload();
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Validation Failed',
              text: response.message
            });
            $submitBtn.prop('disabled', false).text('Continue');
          }
        },
        error: () => {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred. Please try again.'
          });
          $submitBtn.prop('disabled', false).text('Continue');
        }
      });
    });
  }
  
  // STEP 2: Password Validation
  if ($('#passwordForm').length) {
    const $passwordInput = $('#password');
    const $validation = $('#password-validation');
    const $submitBtn = $('#password-submit');
    const requirements = {
      length: { regex: /.{8,}/, element: $('#req-length') },
      uppercase: { regex: /[A-Z]/, element: $('#req-uppercase') },
      lowercase: { regex: /[a-z]/, element: $('#req-lowercase') },
      number: { regex: /\d/, element: $('#req-number') },
      special: { regex: /[@$!%*?&]/, element: $('#req-special') }
    };
    
    $passwordInput.on('input', validatePassword);
    
    function validatePassword() {
      const value = $passwordInput.val();
      let allValid = true;
      
      Object.keys(requirements).forEach(key => {
        const req = requirements[key];
        if (req.regex.test(value)) {
          req.element.removeClass('invalid').addClass('valid');
        } else {
          req.element.removeClass('valid').addClass('invalid');
          allValid = false;
        }
      });
      
      if (value.length === 0) {
        $validation.html('').removeClass('valid invalid');
        $passwordInput.removeClass('valid invalid');
      } else if (allValid) {
        $validation.html('Strong password').removeClass('invalid').addClass('valid');
        $passwordInput.removeClass('invalid').addClass('valid');
      } else {
        $validation.html('Password does not meet requirements').removeClass('valid').addClass('invalid');
        $passwordInput.removeClass('valid').addClass('invalid');
      }
      
      $submitBtn.prop('disabled', !allValid);
    }
    
    $('#passwordForm').on('submit', async function(e) {
      e.preventDefault();
      
      const password = $passwordInput.val();
      
      if (password.length < 8) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid Password',
          text: 'Password must be at least 8 characters'
        });
        return;
      }
      
      if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/.test(password)) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid Password',
          text: 'Password does not meet all requirements'
        });
        return;
      }
      
      $submitBtn.prop('disabled', true).text('Creating Account...');
      
      // Hash the password with SHA-256 before sending
      const hashedPassword = await sha256(password);
      
      $.ajax({
        type: 'POST',
        url: 'signup_handler.php',
        data: { step: 'password', password: hashedPassword },
        dataType: 'json',
        success: (response) => {
          if (response.status) {
            Swal.fire({
              icon: 'success',
              title: 'Success!',
              text: response.message,
              allowOutsideClick: false,
              didClose: () => {
                window.location.href = 'login.php';
              }
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Registration Failed',
              text: response.message
            });
            $submitBtn.prop('disabled', false).text('Complete Registration');
          }
        },
        error: () => {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred. Please try again.'
          });
          $submitBtn.prop('disabled', false).text('Complete Registration');
        }
      });
    });
  }
  
  if (window.location.search.includes('reset=1')) {
    $.ajax({
      type: 'POST',
      url: 'reset_signup.php',
      success: () => {
        window.location.href = 'signup.php';
      }
    });
  }
});