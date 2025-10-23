<?php
session_start();
require_once 'resource/php/init.php';
require_once 'resource/php/class/logging.php';
require_once 'resource/php/class/Auth.php';
Auth::requireAccountType('Admin');

// Check if the form was submitted using POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get the submitted data
    $productId = $_POST['product_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $stock = $_POST['stock'] ?? '';
    $measureUnit = $_POST['measure_unit'] ?? '';

    if (!empty($productId) && !empty($name) && isset($stock)) {
        try {
            $config = new config();
            $pdo = $config->con();

            // Get current product details for logging
            $sql_select = "SELECT name, product_type, stock, measure_unit FROM tbl_inventory WHERE product_id = ?";
            $stmt_select = $pdo->prepare($sql_select);
            $stmt_select->execute([$productId]);
            $old_product = $stmt_select->fetch(PDO::FETCH_ASSOC);

            if (!$old_product) {
                $_SESSION['error_message'] = "Product not found.";
                header("Location: a-search.php");
                exit();
            }

            // Update the product
            $sql = "UPDATE tbl_inventory SET name = ?, stock = ?, measure_unit = ? WHERE product_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $stock, $measureUnit, $productId]);
            
            // Log the action
            $user_id = $_SESSION['user_id'] ?? 0;
            $log_action = "UPDATE_ITEM: Updated product #$productId - ";
            $log_action .= "Name: '{$old_product['name']}' → '$name', ";
            $log_action .= "Stock: {$old_product['stock']} {$old_product['measure_unit']} → $stock $measureUnit";
            logAdminAction($pdo, $user_id, $log_action);
            
            $_SESSION['success_message'] = "Product updated successfully!";

        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error updating product: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "Missing data. Please fill out all fields.";
    }

    header("Location: a-search.php");
    exit();
    
} else {
    $_SESSION['error_message'] = "Invalid request method.";
    header("Location: a-search.php");
    exit();
}
?>