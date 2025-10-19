<?php
class CartItems {
    private $pdo;
    private $user_id;

    public function __construct($pdo, $user_id) {
        $this->pdo = $pdo;
        $this->user_id = $user_id;
    }

    public function addItem($product_id, $amount) {
        // Validate amount
        if ($amount < 1) {
            return false;
        }

        // Check stock availability
        $available_stock = $this->getAvailableStock($product_id);
        if ($available_stock < $amount) {
            return false;
        }

        $cart_id = $this->getActiveCartId(true); 

        $stmt_check = $this->pdo->prepare(
            "SELECT item_id, amount FROM tbl_cart_items WHERE cart_id = ? AND product_id = ?"
        );
        $stmt_check->execute([$cart_id, $product_id]);
        $existing_item = $stmt_check->fetch();

        $success = false;
        if ($existing_item) {
            $new_amount = $existing_item['amount'] + $amount;
            
            // Check stock again for the new total
            if ($available_stock < $new_amount) {
                return false;
            }
            
            $stmt_update = $this->pdo->prepare(
                "UPDATE tbl_cart_items SET amount = ? WHERE item_id = ?"
            );
            $success = $stmt_update->execute([$new_amount, $existing_item['item_id']]);
        } else {
            $stmt_insert = $this->pdo->prepare(
                "INSERT INTO tbl_cart_items (cart_id, product_id, amount) VALUES (?, ?, ?)"
            );
            $success = $stmt_insert->execute([$cart_id, $product_id, $amount]);
        }

        if ($success) {
            $stmt_stock = $this->pdo->prepare(
                "UPDATE tbl_inventory SET stock = stock - ? WHERE product_id = ?"
            );
            $stmt_stock->execute([$amount, $product_id]);
        }

        return $success;
    }

    public function getItems() {
        $cart_id = $this->getActiveCartId();
        if (!$cart_id) return [];

        $sql = "SELECT 
                    inv.*, 
                    items.amount, 
                    items.item_id
                FROM tbl_cart_items AS items
                JOIN tbl_inventory AS inv ON items.product_id = inv.product_id
                WHERE items.cart_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cart_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateItemAmount($item_id, $amount) {
        // Validate amount
        if ($amount < 1) {
            return false;
        }

        // Get product_id and current amount
        $current_data = $this->getItemData($item_id);
        if (!$current_data) {
            return false;
        }

        $product_id = $current_data['product_id'];
        $current_amount = $current_data['amount'];

        // Calculate the difference
        $difference = $amount - $current_amount;

        // Check if we have enough stock for the increase
        if ($difference > 0) {
            $available_stock = $this->getAvailableStock($product_id);
            if ($available_stock < $difference) {
                return false;
            }
        }

        $sql = "UPDATE tbl_cart_items SET amount = ? 
                WHERE item_id = ? AND cart_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $success = $stmt->execute([$amount, $item_id, $this->getActiveCartId()]);

        if ($success && $difference != 0) {
            // Update stock accordingly
            $stmt_stock = $this->pdo->prepare(
                "UPDATE tbl_inventory SET stock = stock - ? WHERE product_id = ?"
            );
            $stmt_stock->execute([$difference, $product_id]);
        }

        return $success;
    }

    public function removeItem($item_id) {
        // Get item data before removal to restore stock
        $item_data = $this->getItemData($item_id);
        
        $sql = "DELETE FROM tbl_cart_items WHERE item_id = ? AND cart_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $success = $stmt->execute([$item_id, $this->getActiveCartId()]);

        if ($success && $item_data) {
            // Restore stock
            $stmt_stock = $this->pdo->prepare(
                "UPDATE tbl_inventory SET stock = stock + ? WHERE product_id = ?"
            );
            $stmt_stock->execute([$item_data['amount'], $item_data['product_id']]);
        }

        return $success;
    }

    public function finalizeRequest($requestData) {
        $cart_id = $this->getActiveCartId();
        if (!$cart_id) {
            return false;
        }

        $items = $this->getItems();
        if (empty($items)) {
            return false; 
        }

        try {
            $this->pdo->beginTransaction();

            // Stock is already reduced when items are added to cart
            // So we don't need to reduce it again here

            $cart_status_stmt = $this->pdo->prepare(
                "UPDATE tbl_cart SET cart_status = 'Used' WHERE cart_id = ?"
            );
            $cart_status_stmt->execute([$cart_id]);

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
    
    public function getActiveCartId($create_if_not_exists = false) {
        $stmt = $this->pdo->prepare("SELECT cart_id FROM tbl_cart WHERE user_id = ? AND cart_status = 'active'");
        $stmt->execute([$this->user_id]);
        $cart = $stmt->fetch();

        if ($cart) {
            return $cart['cart_id'];
        } elseif ($create_if_not_exists) {
            $stmt = $this->pdo->prepare("INSERT INTO tbl_cart (user_id, cart_status) VALUES (?, 'active')");
            $stmt->execute([$this->user_id]);
            return $this->pdo->lastInsertId();
        }
        
        return null;
    }

    private function getAvailableStock($product_id) {
        $stmt = $this->pdo->prepare("SELECT stock FROM tbl_inventory WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['stock'] : 0;
    }

    private function getItemData($item_id) {
        $stmt = $this->pdo->prepare(
            "SELECT product_id, amount FROM tbl_cart_items WHERE item_id = ?"
        );
        $stmt->execute([$item_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>