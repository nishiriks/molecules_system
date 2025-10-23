<?php
session_start();
require_once 'resource/php/init.php';
require_once 'resource/php/class/logging.php';
require_once 'resource/php/class/Auth.php';
Auth::requireAccountType('Admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    $config = new config();
    $pdo = $config->con();

    try {
        // Get product details for logging before deletion
        $sql_select = "SELECT name, product_type, stock, measure_unit FROM tbl_inventory WHERE product_id = ?";
        $stmt_select = $pdo->prepare($sql_select);
        $stmt_select->execute([$product_id]);
        $product = $stmt_select->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            $_SESSION['error_message'] = "Product not found.";
            header('Location: a-search.php');
            exit();
        }
        
        // Check if product is used in any active carts or requests
        $sql_check_usage = "SELECT COUNT(*) as usage_count FROM tbl_cart_items WHERE product_id = ?";
        $stmt_check_usage = $pdo->prepare($sql_check_usage);
        $stmt_check_usage->execute([$product_id]);
        $usage_count = $stmt_check_usage->fetch(PDO::FETCH_ASSOC)['usage_count'];
        
        if ($usage_count > 0) {
            $_SESSION['error_message'] = "Cannot delete product '{$product['name']}'. It is currently used in existing orders.";
            header('Location: a-search.php');
            exit();
        }
        
        // Delete product
        $sql = "DELETE FROM tbl_inventory WHERE product_id = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$product_id])) {
            // Log the action
            $user_id = $_SESSION['user_id'] ?? 0;
            $log_action = "DELETE_ITEM: Deleted product #$product_id - ";
            $log_action .= "Name: '{$product['name']}', ";
            $log_action .= "Type: {$product['product_type']}, ";
            $log_action .= "Stock: {$product['stock']} {$product['measure_unit']}";
            logAdminAction($pdo, $user_id, $log_action);
            
            $_SESSION['success_message'] = "Product '{$product['name']}' was deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Error: Could not delete the product.";
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }
}

header('Location: a-search.php');
exit();
?>