<?php
require_once 'cartItems.php'; 

class CartController {
    private $pdo;
    private $userId;
    private $cart; 

    public function __construct($pdo, $userId) {
        $this->pdo = $pdo;
        $this->userId = $userId;
        $this->cart = new CartItems($this->pdo, $this->userId);
    }

    public function handleAction($postData) {
        if (!isset($postData['action'])) {
            return; 
        }

        $action = $postData['action'];

        switch ($action) {
            case 'add':
                if (isset($postData['product_id'], $postData['quantity'])) {
                    $product_id = $postData['product_id'];
                    $amount = (int)$postData['quantity'];
                    
                    // Validate amount
                    if ($amount < 1) {
                        $_SESSION['error'] = 'Amount must be at least 1.';
                        return;
                    }
                    
                    $success = $this->cart->addItem($product_id, $amount);
                    
                    if ($success) {
                        $_SESSION['message'] = 'Item added to cart successfully.';
                    } else {
                        // Get available stock for better error message
                        $available_stock = $this->getAvailableStock($product_id);
                        $_SESSION['error'] = "Cannot add item. The requested amount would exceed available stock of $available_stock units.";
                    }
                }
                break;
                
            case 'remove':
                if (isset($postData['item_id'])) {
                    $success = $this->cart->removeItem($postData['item_id']);
                    
                    if ($success) {
                        $_SESSION['message'] = 'Item removed from cart successfully.';
                    } else {
                        $_SESSION['error'] = 'Failed to remove item from cart.';
                    }
                }
                break;

            case 'update':
                if (isset($postData['item_id']) && isset($postData['amount'])) {
                    $item_id = $postData['item_id'];
                    $amount = (int)$postData['amount'];
                    
                    // Validate amount
                    if ($amount < 1) {
                        $_SESSION['error'] = 'Amount must be at least 1.';
                        return;
                    }
                    
                    $success = $this->cart->updateItemAmount($item_id, $amount);
                    
                    if ($success) {
                        $_SESSION['message'] = 'Cart updated successfully.';
                    } else {
                        $_SESSION['error'] = 'Failed to update cart. Please check available stock and try again.';
                    }
                }
                break;
        }
    }

    public function redirect($location) {
        header("Location: $location");
        exit();
    }
    
    private function getAvailableStock($product_id) {
        $stmt = $this->pdo->prepare("SELECT stock FROM tbl_inventory WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['stock'] : 0;
    }
}
?>