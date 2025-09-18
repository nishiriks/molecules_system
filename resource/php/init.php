<?php
require_once 'functions.php';

spl_autoload_register(function($class) {
    require_once $_SERVER['DOCUMENT_ROOT'].'/molecules_system/resource/php/class/'.$class.'.php';
});
?>

