<?php
class requestForm extends config {
    public function __construct(public $name,
            public $signature,
            public $subject,
            public $date_from,
            public $date_to,
            public $time_from,
            public $time_to,
            public $room,
            public $tech_name,
            public $status
            )
    {
        parent::__construct();
    }

    private function query($sql, $params = []) {
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    
    if (str_starts_with(strtoupper(trim($sql)), 'SELECT')) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    return true; 
}

    public function reqOrder($cart_id) {
        $sql = 'INSERT INTO tbl_requests (`cart_id`, `prof_name`,`prof_signature`, `subject`,`date_from`,`date_to`,`time_from`,`time_to`,`room`,`tech_name`,`status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        
        $params = [
            $cart_id,
            $this->name,
            $this->signature,
            $this->subject,
            $this->date_from,
            $this->date_to,
            $this->time_from,
            $this->time_to,
            $this->room,
            $this->tech_name,
            $this->status
        ];

        return $this->query($sql, $params);
    }
}

?>