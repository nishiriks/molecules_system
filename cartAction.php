<?php
session_start();
require_once 'resource/php/init.php';
require_once 'resource/php/class/cartHandler.php'; 

if (!isset($_SESSION['user_id']) || !isset($_POST['action'])) {
    header('Location: login.php');
    exit();
}

$config = new config();
$pdo = $config->con();
$cartController = new CartController($pdo, $_SESSION['user_id']);

$cartController->handleAction($_POST);

if ($_POST['action'] === 'add') {
    header('Location: u-search.php');
} else if ($_POST['action'] === 'cancel_request') {
    header('Location: u-search.php');
} else {
    header('Location: u-cart.php');
}
exit();
?>