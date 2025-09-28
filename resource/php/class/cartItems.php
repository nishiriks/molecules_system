<?php
class CartItems {
    private $pdo;
    private $user_id;

    public function __construct($pdo, $user_id) {
        $this->pdo = $pdo;
        $this->user_id = $user_id;
    }

    // Get the cart id for this user
    private function getCartId() {
        $stmt = $this->pdo->prepare("SELECT cart_id FROM tbl_cart WHERE user_id = ?");
        $stmt->execute([$this->user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $row['cart_id'];
        } else {
            // if cart does not exist yet, create one
            $stmt = $this->pdo->prepare("INSERT INTO tbl_cart (user_id) VALUES (?)");
            $stmt->execute([$this->user_id]);
            return $this->pdo->lastInsertId();
        }
    }

    public function getItems() {
        $cart_id = $this->getCartId();

        // IMPORTANT: Adjust this to your actual column names in tbl_inventory
        // I used 'unit_measure' because your earlier message showed it.
        // If your column is called 'measure_unit', rename below accordingly.
        $sql = "
            SELECT 
                inv.name,
                inv.product_type,
                inv.image_path,
                inv.measure_unit,
                ci.amount,
                ci.item_id
            FROM tbl_cart_items AS ci
            INNER JOIN tbl_inventory AS inv ON ci.product_id = inv.product_id
            WHERE ci.cart_id = ?
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cart_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addItem($product_id, $amount) {
        $cart_id = $this->getCartId();
        $stmt = $this->pdo->prepare("INSERT INTO tbl_cart_items (cart_id, product_id, amount) VALUES (?, ?, ?)");
        return $stmt->execute([$cart_id, $product_id, $amount]);
    }

    public function updateItem($item_id, $amount) {
        $stmt = $this->pdo->prepare("UPDATE tbl_cart_items SET amount = ? WHERE item_id = ?");
        return $stmt->execute([$amount, $item_id]);
    }

    public function removeItem($item_id) {
        $stmt = $this->pdo->prepare("DELETE FROM tbl_cart_items WHERE item_id = ?");
        return $stmt->execute([$item_id]);
    }
}
