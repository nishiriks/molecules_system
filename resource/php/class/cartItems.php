<?php
class CartItems {
    private $pdo;
    private $user_id;

    public function __construct($pdo, $user_id) {
        $this->pdo = $pdo;
        $this->user_id = $user_id;
    }

    public function addItem($product_id, $amount) {
        $cart_id = $this->getActiveCartId(true); 

        $stmt_check = $this->pdo->prepare(
            "SELECT item_id, amount FROM tbl_cart_items WHERE cart_id = ? AND product_id = ?"
        );
        $stmt_check->execute([$cart_id, $product_id]);
        $existing_item = $stmt_check->fetch();

        if ($existing_item) {
            $new_amount = $existing_item['amount'] + $amount;
            $stmt_update = $this->pdo->prepare(
                "UPDATE tbl_cart_items SET amount = ? WHERE item_id = ?"
            );
            return $stmt_update->execute([$new_amount, $existing_item['item_id']]);
        } else {
            $stmt_insert = $this->pdo->prepare(
                "INSERT INTO tbl_cart_items (cart_id, product_id, amount) VALUES (?, ?, ?)"
            );
            return $stmt_insert->execute([$cart_id, $product_id, $amount]);
        }
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

        // If amount is being increased, check if we have enough stock
        if ($difference > 0) {
            $available_stock = $this->getAvailableStock($product_id);
            if ($available_stock < $difference) {
                return false; // Not enough stock for the increase
            }
        }

        // Simply update the cart item amount WITHOUT modifying stock
        $sql = "UPDATE tbl_cart_items SET amount = ? 
                WHERE item_id = ? AND cart_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$amount, $item_id, $this->getActiveCartId()]);
    }

    public function removeItem($item_id) {
        $cart_id = $this->getActiveCartId();
        if (!$cart_id) return false;

        $sql = "DELETE FROM tbl_cart_items WHERE item_id = ? AND cart_id = ?";
        $stmt_delete = $this->pdo->prepare($sql);
        return $stmt_delete->execute([$item_id, $cart_id]);
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