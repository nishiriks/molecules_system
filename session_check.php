<?php
// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkSessionTimeout() {
    $timeout_duration = 1800; // 30 minutes in seconds
    
    if (isset($_SESSION['last_activity'])) {
        $current_time = time();
        $elapsed_time = $current_time - $_SESSION['last_activity'];
        
        if ($elapsed_time > $timeout_duration) {
            // Session has expired
            session_unset();
            session_destroy();
            return false;
        }
    } else {
        // No last activity time set
        return false;
    }
    
    // Update the last activity time
    $_SESSION['last_activity'] = time();
    return true;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check session timeout
if (!checkSessionTimeout()) {
    header('Location: login.php?expired=1');
    exit();
}
?>