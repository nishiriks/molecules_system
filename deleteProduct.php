<?php
session_start();
require_once 'resource/php/init.php';

// This should be an admin-only action, so we'll check the session.
if (!isset($_SESSION['user_id'])) { 
    header('Location: login.php');
    exit();
}

// Check if a product_id was sent via POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    $config = new config();
    $pdo = $config->con();

    // Prepare and execute the DELETE statement
    $sql = "DELETE FROM tbl_inventory WHERE product_id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$product_id])) {
        $_SESSION['success_message'] = "Product was deleted successfully.";
    } else {
        // Use the same session variable for errors for simplicity
        $_SESSION['success_message'] = "Error: Could not delete the product.";
    }
}

// Redirect back to the admin search page
header('Location: admin-search.php');
exit();
?>