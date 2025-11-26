<?php
session_start();
require_once 'resource/php/init.php';
require_once 'resource/php/class/Auth.php'; 
require_once 'resource/php/class/inventoryManager.php'; 

Auth::requireAccountType(['Admin', 'Super Admin']);

if (!isset($_POST['action'])) {
    header('Location: a-search.php'); 
    exit();
}

$config = new config();
$pdo = $config->con();
$inventoryManager = new InventoryManager($pdo);
$action = $_POST['action'];

switch ($action) {
    case 'update_item':
        $product_id = $_POST['product_id'] ?? null;
        $name = $_POST['name'] ?? null;
        $stock = $_POST['stock'] ?? null;
        $measure_unit = $_POST['measure_unit'] ?? null;

        if ($product_id && $name && $stock !== null && $measure_unit !== null) {
            $success = $inventoryManager->updateItem($product_id, $name, $stock, $measure_unit);
            $_SESSION['success_message'] = $success ? "Item updated successfully!" : "Failed to update item.";
        } else {
            $_SESSION['error_message'] = "Failed to update item: Missing data.";
        }
        break;

    case 'soft_delete_item':
        if (isset($_POST['product_id'])) {
            $success = $inventoryManager->softDeleteItem($_POST['product_id']);
            $_SESSION['success_message'] = $success ? "Item hidden successfully!" : "Failed to hide item.";
        } else {
            $_SESSION['error_message'] = "Failed to hide item: Missing ID.";
        }
        break;

}

header('Location: a-search.php');
exit();