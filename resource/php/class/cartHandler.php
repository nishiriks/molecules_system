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
                    
                    // Check stock availability
                    $available_stock = $this->getAvailableStock($product_id);
                    if ($available_stock < $amount) {
                        $_SESSION['error'] = "Cannot add item. Only $available_stock units available in stock.";
                        return;
                    }
                    
                    $success = $this->cart->addItem($product_id, $amount);
                    
                    if ($success) {
                        $_SESSION['message'] = 'Item added to cart successfully.';
                    } else {
                        $_SESSION['error'] = 'Failed to add item to cart. Please try again.';
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
                    
                    // Get product_id from cart item to check stock
                    $product_id = $this->getProductIdFromCartItem($item_id);
                    if (!$product_id) {
                        $_SESSION['error'] = 'Item not found in cart.';
                        return;
                    }

                    // Check available stock (including what's already in cart)
                    $available_stock = $this->getAvailableStockForUpdate($product_id, $item_id);
                    if ($available_stock < $amount) {
                        $_SESSION['error'] = "Cannot update. Only $available_stock units available in stock.";
                        return;
                    }

                    $success = $this->cart->updateItemAmount($item_id, $amount);
                    
                    if ($success) {
                        $_SESSION['message'] = 'Cart updated successfully.';
                    } else {
                        $_SESSION['error'] = 'Failed to update cart. Please try again.';
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

    private function getAvailableStockForUpdate($product_id, $item_id) {
        // Get current stock + current amount in cart (since we're updating)
        $current_cart_amount = $this->getCurrentCartAmount($item_id);
        $current_stock = $this->getAvailableStock($product_id);
        
        return $current_stock + $current_cart_amount;
    }

    private function getCurrentCartAmount($item_id) {
        $stmt = $this->pdo->prepare(
            "SELECT amount FROM tbl_cart_items WHERE item_id = ?"
        );
        $stmt->execute([$item_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['amount'] : 0;
    }

    private function getProductIdFromCartItem($item_id) {
        $stmt = $this->pdo->prepare(
            "SELECT product_id FROM tbl_cart_items WHERE item_id = ?"
        );
        $stmt->execute([$item_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['product_id'] : null;
    }
}
?>