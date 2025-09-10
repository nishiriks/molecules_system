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

        $stmt = $this->pdo->prepare(
            "INSERT INTO tbl_cart_items (cart_id, product_id, amount) VALUES (?, ?, ?)"
        );
        return $stmt->execute([$cart_id, $product_id, $amount]);
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
        $sql = "UPDATE tbl_cart_items SET amount = ? 
                WHERE item_id = ? AND cart_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$amount, $item_id, $this->getActiveCartId()]);
    }

    public function removeItem($item_id) {
        $sql = "DELETE FROM tbl_cart_items WHERE item_id = ? AND cart_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$item_id, $this->getActiveCartId()]);
    }
    
    private function getActiveCartId($create_if_not_exists = false) {
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
}