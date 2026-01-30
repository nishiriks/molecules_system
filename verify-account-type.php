<?php
require_once 'resource/php/init.php';
require_once 'resource/php/class/Auth.php';
Auth::requireAccountType('Super Admin');
require_once 'session_check.php';

// Check if there's a pending change
if (!isset($_SESSION['pending_account_change'])) {
    header('Location: s-account-management.php');
    exit();
}

$pending = $_SESSION['pending_account_change'];

// Check if expired
if (time() > $pending['expires']) {
    unset($_SESSION['pending_account_change']);
    $_SESSION['error_message'] = "Verification code has expired. Please start over.";
    header('Location: s-account-management.php');
    exit();
}

// Auto-fill code from URL parameter
$autoCode = $_GET['code'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Account Type Change</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
        }
        .verification-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 500px;
            margin: 0 auto;
        }
        .verification-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 25px;
            text-align: center;
        }
        .verification-body {
            padding: 30px;
        }
        .change-details {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 5px 5px 0;
        }
        .verification-input {
            font-size: 24px;
            letter-spacing: 5px;
            text-align: center;
            padding: 15px;
            border: 2px solid #007bff;
            border-radius: 10px;
            margin: 20px 0;
        }
        .timer {
            font-size: 14px;
            color: #6c757d;
        }
        .expires-soon {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="verification-card">
            <div class="verification-header">
                <h1><i class="fas fa-shield-alt fa-2x mb-3"></i></h1>
                <h2>Verify Account Type Change</h2>
                <p class="mb-0">Enter Verification Code</p>
            </div>
            
            <div class="verification-body">
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['error_message'] ?>
                        <?php unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>
                
                <div class="change-details">
                    <h5><i class="fas fa-user me-2"></i>User Details</h5>
                    <p class="mb-1"><strong>User:</strong> <?= htmlspecialchars($pending['user_name']) ?></p>
                    <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($pending['user_email']) ?></p>
                    <p class="mb-1"><strong>Current Type:</strong> <span class="badge bg-secondary"><?= htmlspecialchars($pending['old_type']) ?></span></p>
                    <p class="mb-0"><strong>New Type:</strong> <span class="badge bg-primary"><?= htmlspecialchars($pending['new_type']) ?></span></p>
                </div>
                
                <p>A verification code has been sent to your email address. Please enter the code below.</p>
                
                <form method="POST" action="s-account-management.php">
                    <div class="mb-3">
                        <label for="verification_code" class="form-label">Enter 6-digit Verification Code:</label>
                        <input type="text" 
                               id="verification_code" 
                               name="verification_code" 
                               class="form-control verification-input" 
                               maxlength="6" 
                               pattern="[0-9]{6}" 
                               placeholder="000000" 
                               required
                               autofocus
                               value="<?= htmlspecialchars($autoCode) ?>">
                        <div class="form-text">Enter the 6-digit code exactly as shown in the email, or click the link in the email.</div>
                    </div>
                    
                    <div class="timer text-center mb-3 <?= (time() > ($pending['expires'] - 300)) ? 'expires-soon' : '' ?>">
                        <i class="fas fa-clock me-1"></i>
                        Code expires in: <span id="countdown"><?= ceil(($pending['expires'] - time()) / 60) ?> minutes</span>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="verify_and_change" class="btn btn-primary btn-lg">
                            <i class="fas fa-check-circle me-2"></i>Verify & Change Account Type
                        </button>
                        <a href="s-account-management.php" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </form>
                
                <div class="mt-4 alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> If you didn't receive the email, please check your spam folder or contact the administrator.
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/6563a04357.js" crossorigin="anonymous"></script>
    <script>
        // Countdown timer
        const expires = <?= $pending['expires'] ?>;
        
        function updateCountdown() {
            const now = Math.floor(Date.now() / 1000);
            const remaining = expires - now;
            
            if (remaining <= 0) {
                document.getElementById('countdown').textContent = 'EXPIRED';
                document.getElementById('countdown').className = 'expires-soon';
                return;
            }
            
            const minutes = Math.floor(remaining / 60);
            const seconds = remaining % 60;
            
            let text = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            if (remaining <= 300) { // 5 minutes
                text += ' (expiring soon)';
                document.getElementById('countdown').className = 'expires-soon';
            }
            
            document.getElementById('countdown').textContent = text;
        }
        
        // Update every second
        setInterval(updateCountdown, 1000);
        updateCountdown(); // Initial call
        
        // Auto-submit if code is pre-filled from URL
        document.addEventListener('DOMContentLoaded', function() {
            const codeInput = document.getElementById('verification_code');
            if (codeInput.value.length === 6) {
                // Auto-validate after 1 second
                setTimeout(function() {
                    const form = codeInput.closest('form');
                    const submitBtn = form.querySelector('[name="verify_and_change"]');
                    submitBtn.click();
                }, 1000);
            }
        });
    </script>
</body>
</html>
