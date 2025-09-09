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

    /**
     * Handles the incoming action from a form.
     * @param array $postData The $_POST data from the form.
     */
    public function handleAction($postData) {
        if (!isset($postData['action'])) {
            return; 
        }

        $action = $postData['action'];

        switch ($action) {
            case 'add':
                if (isset($postData['product_id'])) {
                    $this->cart->addItem($postData['product_id'], 1);
                }
                break;
            case 'remove':
                if (isset($postData['item_id'])) {
                    $this->cart->removeItem($postData['item_id']);
                }
                break;

            case 'update':
                if (isset($postData['item_id']) && isset($postData['amount'])) {
                    $this->cart->updateItemAmount($postData['item_id'], $postData['amount']);
                }
                break;

            
        }
    }

    /**
     * Redirects the user to a specified location.
     * @param string $location The URL to redirect to.
     */
    public function redirect($location) {
        header("Location: $location");
        exit();
    }
}