<?php
class config {
	private $user = 'root';
	private $pass = '';
	public $pdo = null;

	public function con() {
		try {
			$this->pdo = new PDO('mysql:host=127.0.0.1:3307;dbname=molecules_db',$this->user, $this->pass);
		} catch (PDOException $e) {
			die($e->getMessage());
		} return $this->pdo;
	}
}
?>