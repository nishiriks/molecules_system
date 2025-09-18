<?php
// session_start();
require_once 'resource/php/init.php';

// // Protect this script: only admins should be able to update products
// if (!isset($_SESSION['admin_id'])) {
//     // Redirect non-admins to the login page
//     header('Location: login.php');
//     exit();
// }

// Check if the form was submitted using POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get the submitted data
    $productId = $_POST['product_id'];
    $name = $_POST['name'];
    $stock = $_POST['stock'];
    $measureUnit = $_POST['measure_unit'] ?? null;

    if (!empty($productId) && !empty($name) && isset($stock)) {
        try {
            $config = new config();
            $pdo = $config->con();

            $sql = "UPDATE tbl_inventory SET name = ?, stock = ?, measure_unit = ? WHERE product_id = ?";
            $stmt = $pdo->prepare($sql);
        
            $stmt->execute([$name, $stock, $measureUnit, $productId]);
            
            $_SESSION['success_message'] = "Product updated successfully!";

        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error updating product: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "Missing data. Please fill out all fields.";
    }

    header("Location: admin-search.php");
    exit();
    
} else {
    echo "Invalid request method.";
    header("Location: admin-search.php");
    exit();
}