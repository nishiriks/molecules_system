<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkSessionTimeout() {
    $timeout_duration = 1800;
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
        return false;
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!checkSessionTimeout()) {
    header('Location: login.php?expired=1');
    exit();
}
?>