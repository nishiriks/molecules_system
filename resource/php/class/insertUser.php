<?php
class insertUsr extends config {
	public $first_name;
	public $last_name;
	public $email;
	public $upass;
	public $acct_type;

	public function __construct($first_name,$last_name,$email,$upass,$acct_type) {
		$this->first_name = $first_name;
		$this->last_name = $last_name;
		$this->email = $email;
		$this->upass = $upass;
		$this->acct_type = $acct_type;
	}
	
	public function addUsr() {
		$con = $this->con();
		$sql = "INSERT INTO `tbl_users` (`first_name`,`last_name`,`email`,`password`,`account_type`) VALUES ('$this->first_name','$this->last_name','$this->email','$this->upass','$this->acct_type')";
		$data = $con->prepare($sql);

		if ($data->execute()) {
			return true;
		} else {
			return false;
		}
	}
}
?>