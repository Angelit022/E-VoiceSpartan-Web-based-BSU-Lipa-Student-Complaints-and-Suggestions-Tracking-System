<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../../signup_login/login.php");
    exit();
}

require_once '../../db.php';
require_once '../classes/SettingsManager.php';
require_once '../classes/NotificationManager.php';

$settingsManager = new SettingsManager($_SESSION['user_id']);
$notificationManager = new NotificationManager($_SESSION['user_id']);

$student = $settingsManager->getStudentInfo();
$notificationPrefs = $notificationManager->getPreferences();
$userPreferences = $settingsManager->getPreferences();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - E-VoiceSpartan</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../css/global-theme.css">
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="../css/settings.css">
    <link rel="stylesheet" href="../css/navbar.css">
</head>
<body>
    <?php include '../components/navbar.php'; ?>

    <div class="settings-header">
        <div class="settings-container">
            <h1 data-translate="settings"><i class="bi bi-gear"></i> Settings</h1>
            <p data-translate="manageAccount">Manage your account, notifications, privacy, and preferences</p>
        </div>
    </div>

    <div class="settings-container">
        <div class="settings-tabs">
            <button class="settings-tab active" onclick="switchTab('account')">
                <i class="bi bi-person"></i> <span data-translate="account">Account</span>
            </button>
            <button class="settings-tab" onclick="switchTab('notifications')">
                <i class="bi bi-bell"></i> <span data-translate="notifications">Notifications</span>
            </button>
            <button class="settings-tab" onclick="switchTab('privacy')">
                <i class="bi bi-shield-lock"></i> <span data-translate="privacy">Privacy</span>
            </button>
            <button class="settings-tab" onclick="switchTab('preferences')">
                <i class="bi bi-sliders"></i> <span data-translate="preferences">Preferences</span>
            </button>
        </div>

        <div id="account" class="settings-panel active">
            <div class="panel-header">
                <h2><i class="bi bi-person-circle"></i> <span data-translate="accountInfo">Account Information</span></h2>
                <p data-translate="updatePersonal">Update your personal information</p>
            </div>
            <div class="success-message" id="account-success"></div>
            <div class="error-message" id="account-error"></div>
            <form id="account-form" onsubmit="handleAccountSubmit(event)">
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstName"><span data-translate="firstName">First Name</span> <span style="color: var(--color-red);">*</span></label>
                        <input type="text" id="firstName" name="firstName" value="<?= htmlspecialchars($student['first_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="lastName"><span data-translate="lastName">Last Name</span> <span style="color: var(--color-red);">*</span></label>
                        <input type="text" id="lastName" name="lastName" value="<?= htmlspecialchars($student['last_name'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="middleInitial" data-translate="middleInitial">Middle Initial</label>
                        <input type="text" id="middleInitial" name="middleInitial" maxlength="1" value="<?= htmlspecialchars($student['middle_initial'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="email"><span data-translate="email">Email Address</span> <span style="color: var(--color-red);">*</span></label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($student['email'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="phoneNumber" data-translate="phoneNumber">Phone Number</label>
                    <input type="tel" id="phoneNumber" name="phoneNumber" value="<?= htmlspecialchars($student['phone_number'] ?? '') ?>">
                </div>
                <button type="submit" class="btn-save">
                    <i class="bi bi-check-circle"></i> <span data-translate="saveChanges">Save Changes</span>
                </button>
            </form>
        </div>

        <div id="notifications" class="settings-panel">
            <div class="panel-header">
                <h2><i class="bi bi-bell"></i> <span data-translate="notificationPrefs">Notification Preferences</span></h2>
                <p data-translate="chooseUpdates">Choose how you want to receive updates</p>
            </div>
            <div class="success-message" id="notif-success"></div>
            <div class="error-message" id="notif-error"></div>
            <form id="notifications-form" onsubmit="handleNotificationsSubmit(event)">
                <div class="form-check">
                    <input type="checkbox" id="viaEmail" name="viaEmail" class="form-check-input" <?= $notificationPrefs['via_email'] ? 'checked' : '' ?>>
                    <label for="viaEmail" class="form-check-label" data-translate="emailNotifications">Email Notifications</label>
                </div>
                <p class="form-check-description" data-translate="receiveVia">Receive notifications and updates via email, sms, or both</p>

                <div class="form-check">
                    <input type="checkbox" id="viaSms" name="viaSms" class="form-check-input" <?= $notificationPrefs['via_sms'] ? 'checked' : '' ?>>
                    <label for="viaSms" class="form-check-label" data-translate="smsNotifications">SMS Notifications</label>
                </div>

                <button type="submit" class="btn-save">
                    <i class="bi bi-check-circle"></i> <span data-translate="savePreferences">Save Preferences</span>
                </button>
            </form>
        </div>

        <div id="privacy" class="settings-panel">
            <div class="panel-header">
                <h2><i class="bi bi-shield-lock"></i> <span data-translate="changePassword">Change Password</span></h2>
                <p data-translate="keepSecure">Update your password to keep your account secure</p>
            </div>
            <div class="success-message" id="password-success"></div>
            <div class="error-message" id="password-error"></div>
            <form id="password-form" onsubmit="handlePasswordSubmit(event)">
                <div class="form-group">
                    <label for="currentPassword"><span data-translate="currentPassword">Current Password</span> <span style="color: var(--color-red);">*</span></label>
                    <input type="password" id="currentPassword" name="currentPassword" required>
                </div>
                <div class="form-group">
                    <label for="newPassword"><span data-translate="newPassword">New Password</span> <span style="color: var(--color-red);">*</span></label>
                    <input type="password" id="newPassword" name="newPassword" required>
                </div>
                <div class="form-group">
                    <label for="confirmPassword"><span data-translate="confirmPassword">Confirm New Password</span> <span style="color: var(--color-red);">*</span></label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                </div>
                <button type="submit" class="btn-save">
                    <i class="bi bi-key"></i> <span data-translate="updatePassword">Update Password</span>
                </button>
            </form>
        </div>

        <div id="preferences" class="settings-panel">
            <div class="panel-header">
                <h2><i class="bi bi-sliders"></i> <span data-translate="appPreferences">App Preferences</span></h2>
                <p data-translate="customizeExperience">Customize your experience</p>
            </div>
            <div class="success-message" id="pref-success"></div>
            <div class="error-message" id="pref-error"></div>
            <form id="preferences-form" onsubmit="handlePreferencesSubmit(event)">
                <div class="form-group">
                    <label for="language" data-translate="language">Language</label>
                    <select id="language" name="language">
                        <option value="en" <?= $userPreferences['language'] === 'en' ? 'selected' : '' ?>>English</option>
                        <option value="tl" <?= $userPreferences['language'] === 'tl' ? 'selected' : '' ?>>Filipino (Tagalog)</option>
                        <option value="es" <?= $userPreferences['language'] === 'es' ? 'selected' : '' ?>>Español (Spanish)</option>
                        <option value="fr" <?= $userPreferences['language'] === 'fr' ? 'selected' : '' ?>>Français (French)</option>
                        <option value="de" <?= $userPreferences['language'] === 'de' ? 'selected' : '' ?>>Deutsch (German)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="theme" data-translate="theme">Theme</label>
                    <div class="theme-selector">
                        <div class="theme-option" data-theme="light">
                            <input type="radio" id="theme-light" name="theme" value="light" <?= $userPreferences['theme'] === 'light' ? 'checked' : '' ?>>
                            <label for="theme-light">
                                <i class="bi bi-sun-fill"></i>
                                <span data-translate="lightMode">Light Mode</span>
                            </label>
                        </div>
                        <div class="theme-option" data-theme="dark">
                            <input type="radio" id="theme-dark" name="theme" value="dark" <?= $userPreferences['theme'] === 'dark' ? 'checked' : '' ?>>
                            <label for="theme-dark">
                                <i class="bi bi-moon-stars-fill"></i>
                                <span data-translate="darkMode">Dark Mode</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn-save">
                    <i class="bi bi-check-circle"></i> <span data-translate="savePreferences">Save Preferences</span>
                </button>
            </form>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script src="../js/global-theme.js"></script>
    <script src="../js/settings.js"></script>
    <script src="../js/navbar.js"></script>
</body>
</html>