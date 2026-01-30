<?php
// Function to safely start session with configuration
function safe_session_start() {
    if (session_status() === PHP_SESSION_NONE) {
        // Configure session settings BEFORE starting
        ini_set('session.gc_maxlifetime', 1800); // 30 minutes in seconds
        session_set_cookie_params([
            'lifetime' => 1800,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']), // Auto-detect HTTPS
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        session_start();
    }
}

// Always start session safely in init.php
safe_session_start();

require_once 'functions.php';

spl_autoload_register(function($class) {
    require_once $_SERVER['DOCUMENT_ROOT'].'/molecules_system/resource/php/class/'.$class.'.php';
});
?>