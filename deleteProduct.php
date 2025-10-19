<?php
session_start();
require_once 'resource/php/init.php';

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'Admin') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    $config = new config();
    $pdo = $config->con();

    $sql = "DELETE FROM tbl_inventory WHERE product_id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$product_id])) {
        $_SESSION['success_message'] = "Product was deleted successfully.";
        exit();
    } else {
        $_SESSION['success_message'] = "Error: Could not delete the product.";
        exit();
    }
}

header('Location: a-search.php');
exit();
?>