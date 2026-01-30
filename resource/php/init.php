<?php
function safe_session_start() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.gc_maxlifetime', 1800);
        session_set_cookie_params([
            'lifetime' => 1800,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        session_start();
    }
}

safe_session_start();

require_once 'functions.php';

spl_autoload_register(function($class) {
    require_once $_SERVER['DOCUMENT_ROOT'].'/molecules_system/resource/php/class/'.$class.'.php';
});
?>