
let originalAccountData = {};
let originalNotificationData = {};
let originalPreferencesData = {};

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    storeOriginalData();
    
    // Apply saved preferences on load
    const savedLanguage = localStorage.getItem('preferredLanguage') || 'en';
    const savedTheme = localStorage.getItem('preferredTheme') || 'light';
    
    // Update form selects to match stored values
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
});

function storeOriginalData() {
    // Store original account data
    const accountForm = document.getElementById('account-form');
    if (accountForm) {
        originalAccountData = {
            firstName: document.getElementById('firstName').value,
            lastName: document.getElementById('lastName').value,
            middleInitial: document.getElementById('middleInitial').value,
            email: document.getElementById('email').value,
            phoneNumber: document.getElementById('phoneNumber').value
        };
    }

    // Store original notification data
    const notificationForm = document.getElementById('notifications-form');
    if (notificationForm) {
        originalNotificationData = {
            viaEmail: document.getElementById('viaEmail').checked,
            viaSms: document.getElementById('viaSms').checked
        };
    }

    // Store original preferences data
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
    // Hide all panels
    document.querySelectorAll('.settings-panel').forEach(panel => {
        panel.classList.remove('active');
    });

    // Remove active class from all tabs
    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.classList.remove('active');
    });

    // Show selected panel
    document.getElementById(tabName).classList.add('active');

    // Add active class to clicked tab
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
        
        // Handle checkbox boolean values
        if (typeof originalValue === 'boolean') {
            if (formValue !== originalValue) {
                return true;
            }
        } else {
            // Handle string values
            if (String(formValue || '') !== String(originalValue || '')) {
                return true;
            }
        }
    }
    return false;
}

async function handleAccountSubmit(event) {
    event.preventDefault();
    
    // Get current form values
    const currentData = {
        firstName: document.getElementById('firstName').value,
        lastName: document.getElementById('lastName').value,
        middleInitial: document.getElementById('middleInitial').value,
        email: document.getElementById('email').value,
        phoneNumber: document.getElementById('phoneNumber').value
    };

    // Check if any changes were made
    if (!hasFormChanged(currentData, originalAccountData)) {
        const lang = localStorage.getItem('preferredLanguage') || 'en';
        const trans = window.languageManager.getTranslation('noChanges');
        const transMsg = window.languageManager.getTranslation('noChangesMessage');
        Swal.fire({
            icon: 'info',
            title: trans,
            text: transMsg,
            confirmButtonColor: '#c41e3a'
        });
        return;
    }

    const formData = new FormData(event.target);
    formData.append('action', 'update_account');

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
                confirmButtonColor: '#c41e3a'
            });
            // Update original data after successful save
            originalAccountData = currentData;
        } else {
            showMessage('account-error', result.message, false);
        }
    } catch (error) {
        showMessage('account-error', 'An error occurred', false);
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
                // Reload page to apply language changes throughout
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