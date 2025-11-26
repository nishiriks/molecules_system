<?php
class InventoryManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Updates an existing item in the inventory.
     * @param int $product_id
     * @param string $name
     * @param int $stock
     * @param string $measure_unit
     * @return bool True on success, false on failure.
     */
    public function updateItem($product_id, $name, $stock, $measure_unit) {
        try {
            $sql = "UPDATE tbl_inventory 
                    SET name = ?, stock = ?, measure_unit = ? 
                    WHERE product_id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$name, $stock, $measure_unit, $product_id]);
        } catch (PDOException $e) {
            error_log("Inventory Update Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Sets the is_deleted flag to 1 for an item.
     * @param int $product_id
     * @return bool True on success, false on failure.
     */
    public function softDeleteItem($product_id) {
        try {
            $sql = "UPDATE tbl_inventory SET is_deleted = 1 WHERE product_id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$product_id]);
        } catch (PDOException $e) {
            error_log("Inventory Soft Delete Error: " . $e->getMessage());
            return false;
        }
    }

    // You can add methods here for adding new items, permanently deleting, etc.
}
?>