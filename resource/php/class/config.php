<?php
class config {
    private $host = "127.0.0.1";
    private $dbname = "molecules_db";
    private $user = "root";
    private $pass = "";
 
    protected $pdo; 

    public function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname}";

        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
}
?>