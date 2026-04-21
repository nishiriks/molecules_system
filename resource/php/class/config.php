<?php
class config {
    private $host = "127.0.0.1";
    private $dbname = "molecules_db";
    private $user = "root";
    private $pass = "aki123";
    protected $pdo; 
    private $GROQ_API_KEY = '';

    public function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";

        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    public function con() {
        return $this->pdo;
    }
    public function getGroqKey() {
        return $this->GROQ_API_KEY;
    }
}
?>