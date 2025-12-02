let originalAccountData = {};
let originalNotificationData = {};
let originalPreferencesData = {};

document.addEventListener('DOMContentLoaded', function() {

    setTimeout(function() {
        storeOriginalData();
    }, 100);

    const savedLanguage = localStorage.getItem('preferredLanguage') || 'en';
    const savedTheme = localStorage.getItem('preferredTheme') || 'light';

    const languageSelect = document.getElementById('language');
    const themeRadios = document.querySelectorAll('input[name="theme"]');
    
    if (languageSelect) {
        languageSelect.value = savedLanguage;
    }
    
    themeRadios.forEach(radio => {
        if (radio.value === savedTheme) {
            radio.checked = true;
        }
    });

    setupPhoneValidation();
});

function setupPhoneValidation() {
    const phoneInput = document.getElementById('phoneNumber');
    
    if (phoneInput) {
        // Prevent non-numeric input on keypress
        phoneInput.addEventListener('keypress', function(e) {
            // Only allow numbers (0-9)
            const charCode = e.which ? e.which : e.keyCode;
            if (charCode < 48 || charCode > 57) {
                e.preventDefault();
                return false;
            }
        });

        // Prevent non-numeric input on input event (for copy-paste and other inputs)
        phoneInput.addEventListener('input', function(e) {
            // Store cursor position
            const cursorPos = this.selectionStart;
            const oldLength = this.value.length;
            
            // Remove any non-digit characters
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Limit to 11 digits
            if (this.value.length > 11) {
                this.value = this.value.slice(0, 11);
            }
            
            // Restore cursor position
            const newLength = this.value.length;
            const newCursorPos = cursorPos - (oldLength - newLength);
            this.setSelectionRange(newCursorPos, newCursorPos);
        });

        // Prevent paste of non-numeric content
        phoneInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const numericOnly = pastedText.replace(/[^0-9]/g, '').slice(0, 11);
            
            // Insert at cursor position
            const start = this.selectionStart;
            const end = this.selectionEnd;
            const currentValue = this.value;
            const newValue = currentValue.substring(0, start) + numericOnly + currentValue.substring(end);
            
            this.value = newValue.slice(0, 11);
            
            // Set cursor position after pasted content
            const newCursorPos = Math.min(start + numericOnly.length, 11);
            this.setSelectionRange(newCursorPos, newCursorPos);
        });

        // Prevent drag and drop
        phoneInput.addEventListener('drop', function(e) {
            e.preventDefault();
        });

        // Validate on blur
        phoneInput.addEventListener('blur', function() {
            validatePhoneNumber(this);
        });
    }
}

function validatePhoneNumber(input) {
    const phoneNumber = input.value.trim();
    
    const existingError = input.parentElement.querySelector('.phone-error');
    if (existingError) {
        existingError.remove();
    }
    
    input.classList.remove('input-error');
    
    if (phoneNumber === '') {
        return true;
    }
    
    // Check if it's exactly 11 digits
    if (phoneNumber.length !== 11) {
        showPhoneError(input, 'Phone number must be exactly 11 digits');
        return false;
    }
    
    // Check if it starts with 09
    if (!phoneNumber.startsWith('09')) {
        showPhoneError(input, 'Phone number must start with 09');
        return false;
    }
    
    return true;
}

function showPhoneError(input, message) {
    input.classList.add('input-error');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'phone-error';
    errorDiv.style.color = '#dc2626';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    errorDiv.textContent = message;
    
    input.parentElement.appendChild(errorDiv);
}

function storeOriginalData() {
    const phoneInput = document.getElementById('phoneNumber');
    if (phoneInput) {
        originalAccountData = {
            phoneNumber: phoneInput.value.trim()
        };
        console.log('Stored original phone:', originalAccountData.phoneNumber); // Debug
    }

    const notificationForm = document.getElementById('notifications-form');
    if (notificationForm) {
        originalNotificationData = {
            viaEmail: document.getElementById('viaEmail').checked,
            viaSms: document.getElementById('viaSms').checked
        };
    }
    const preferencesForm = document.getElementById('preferences-form');
    if (preferencesForm) {
        const checkedTheme = document.querySelector('input[name="theme"]:checked');
        originalPreferencesData = {
            language: document.getElementById('language').value,
            theme: checkedTheme ? checkedTheme.value : 'light'
        };
    }
}

function switchTab(tabName) {
    document.querySelectorAll('.settings-panel').forEach(panel => {
        panel.classList.remove('active');
    });

    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.classList.remove('active');
    });

    document.getElementById(tabName).classList.add('active');

    event.target.closest('.settings-tab').classList.add('active');
}

function showMessage(elementId, message, isSuccess = true) {
    const element = document.getElementById(elementId);
    element.textContent = message;
    element.style.display = 'block';
    element.classList.remove(isSuccess ? 'error-message' : 'success-message');
    element.classList.add(isSuccess ? 'success-message' : 'error-message');

    setTimeout(() => {
        element.style.display = 'none';
    }, 4000);
}

function hasFormChanged(formData, originalData) {
    for (let key in originalData) {
        const formValue = formData[key];
        const originalValue = originalData[key];

        if (typeof originalValue === 'boolean') {
            if (formValue !== originalValue) {
                return true;
            }
        } else {
            if (String(formValue || '') !== String(originalValue || '')) {
                return true;
            }
        }
    }
    return false;
}

async function handleAccountSubmit(event) {
    event.preventDefault();

    const phoneInput = document.getElementById('phoneNumber');
    if (!validatePhoneNumber(phoneInput)) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Phone Number',
            text: 'Please enter a valid 11-digit phone number starting with 09',
            confirmButtonColor: '#c41e3a'
        });
        return;
    }
    
    const currentPhoneNumber = phoneInput.value.trim();
    
    console.log('Current phone:', currentPhoneNumber); 
    console.log('Original phone:', originalAccountData.phoneNumber); 

    if (currentPhoneNumber === originalAccountData.phoneNumber) {
        Swal.fire({
            icon: 'info',
            title: 'No Changes',
            text: 'Mobile number has not been changed.',
            confirmButtonColor: '#c41e3a'
        });
        return;
    }

    const formData = new FormData();
    formData.append('action', 'update_account');
    formData.append('phoneNumber', currentPhoneNumber);

    try {
        const response = await fetch('./process_settings.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showMessage('account-success', result.message, true);
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: result.message,
                confirmButtonColor: '#28a745'
            });
            originalAccountData.phoneNumber = currentPhoneNumber;
        } else {
            showMessage('account-error', result.message, false);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: result.message,
                confirmButtonColor: '#c41e3a'
            });
        }
    } catch (error) {
        showMessage('account-error', 'An error occurred', false);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An error occurred while updating mobile number',
            confirmButtonColor: '#c41e3a'
        });
    }
}

async function handleNotificationsSubmit(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    formData.append('action', 'update_notifications');

    try {
        const response = await fetch('./process_settings.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: result.message,
                confirmButtonColor: '#c41e3a'
            });
            // Update original data
            originalNotificationData = {
                viaEmail: document.getElementById('viaEmail').checked,
                viaSms: document.getElementById('viaSms').checked
            };
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: result.message,
                confirmButtonColor: '#c41e3a'
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An error occurred',
            confirmButtonColor: '#c41e3a'
        });
    }
}

async function handlePasswordSubmit(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    formData.append('action', 'update_password');

    try {
        const response = await fetch('./process_settings.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: result.message,
                confirmButtonColor: '#c41e3a'
            });
            event.target.reset();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: result.message,
                confirmButtonColor: '#c41e3a'
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An error occurred',
            confirmButtonColor: '#c41e3a'
        });
    }
}

async function handlePreferencesSubmit(event) {
    event.preventDefault();
    
    const language = document.getElementById('language').value;
    const themeRadio = document.querySelector('input[name="theme"]:checked');
    const theme = themeRadio ? themeRadio.value : 'light';

    const formData = new FormData(event.target);
    formData.append('action', 'update_preferences');

    try {
        const response = await fetch('./process_settings.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            // Apply changes using global managers
            window.languageManager.setLanguage(language);
            window.themeManager.setTheme(theme);
            
            // Update original data
            originalPreferencesData = {
                language: language,
                theme: theme
            };

            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: result.message,
                confirmButtonColor: '#c41e3a'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: result.message,
                confirmButtonColor: '#c41e3a'
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An error occurred',
            confirmButtonColor: '#c41e3a'
        });
    }
}