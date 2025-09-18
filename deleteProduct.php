<?php
session_start();
require_once 'resource/php/init.php';

if (!isset($_SESSION['user_id'])) { 
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
    } else {
        $_SESSION['success_message'] = "Error: Could not delete the product.";
    }
}

header('Location: admin-search.php');
exit();
?>