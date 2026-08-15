<?php
/**
 * Reset Password View
 * Renders a form to set a new password after clicking the reset link.
 */
$view = 'auth/reset-password';
$resetEmail = $_SESSION['reset_email'] ?? '';
ob_start();
?>
<div class="auth-container">
    <div class="card">
        <h1>Reset Your Password</h1>
        <p>Enter a new password for your account.</p>
        <form method="post" action="<?=APP_URL?>/index.php?action=reset-password">
            <input type="hidden" name="csrf_token" value="<?=escapeOutput(generateCsrfToken())?>">
            <input type="hidden" name="email" value="<?=escapeOutput($resetEmail)?>">
            <div class="form-row">
                <div class="form-row-full">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-row-full">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
            </div>
            <button type="submit">Reset Password</button>
        </form>
        <div class="auth-footer">
            <a href="<?=APP_URL?>/index.php?action=login">Back to Login</a>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require APP_ROOT . '/views/app.php';
