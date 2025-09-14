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
    // Use the null coalescing operator (??) in case the unit isn't set (for equipment)
    $measureUnit = $_POST['measure_unit'] ?? null;

    // Make sure we have the necessary data
    if (!empty($productId) && !empty($name) && isset($stock)) {
        try {
            $config = new config();
            $pdo = $config->con();

            // Prepare the SQL UPDATE statement to prevent SQL injection
            $sql = "UPDATE tbl_inventory SET name = ?, stock = ?, measure_unit = ? WHERE product_id = ?";
            $stmt = $pdo->prepare($sql);
            
            // Execute the statement with the form data
            $stmt->execute([$name, $stock, $measureUnit, $productId]);
            
            // Optional: Set a success message
            $_SESSION['success_message'] = "Product updated successfully!";

        } catch (PDOException $e) {
            // Optional: Set an error message for debugging
            $_SESSION['error_message'] = "Error updating product: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "Missing data. Please fill out all fields.";
    }

    // Redirect the user back to the admin search page
    header("Location: admin-search.php");
    exit();
    
} else {
    // If someone tries to access this page directly without submitting a form
    echo "Invalid request method.";
    header("Location: admin-search.php");
    exit();
}