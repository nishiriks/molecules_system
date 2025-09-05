<?php
include 'insertUser.php';

class validate extends config {
	private function showAlert($message, $type = 'danger') {
    echo "
    <div class='alert alert-{$type} alert-dismissible fade show my-2 p-2' role='alert'>
        <div class='d-flex align-items-center justify-content-between'> 
            <div>{$message}</div>
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>
    </div>
    ";
}

    public function viewer($a) {
		$con = $this->con();
		$sql = $a;
		$data = $con->prepare($sql);
		$data->execute();
		$result = $data->fetchAll(PDO::FETCH_ASSOC);

		return $result;
	}

    public function valRegEmail($mail) {
		$sql = "SELECT * FROM `tbl_users` WHERE `email` = '$mail'";
		$check = $this->viewer($sql);
		
		if (!$check) {
			$mail = trim($mail);
			$mail = filter_var($mail, FILTER_SANITIZE_EMAIL);
			if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
				echo '
				<div class="alert alert-success alert-dismissible fade show my-3" role="alert">
					Invalid email
					<button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="close"></button>	
				</div>
				';
				return false;
			} else {
				return true;
			}
		} else {
			$this->showAlert("Email has already been used");
			return false;
		}
	}

    public function validLog($email, $upass) {		
		if (!empty($email) && !empty($upass)) {
			$sql = "SELECT * FROM `tbl_users` WHERE `email` = '$email'";
			$check = $this->viewer($sql);

			if ($check) {
				foreach ($check as $data) {
					$a = password_verify($upass, $data['password']);
					if ($a) {
						session_start();
						$_SESSION['first_name'] = $data['first_name'];
						$_SESSION['last_name'] = $data['last_name'];
						$_SESSION['user_email'] = $data['email'];
						$_SESSION['account_type'] = $data['account_type'];
						
						if ($data['account_type'] == 'Student' || $data['account_type'] == 'Faculty') {
							header('location:index.php');
							exit();
						} else{
							header('location:products.php');
							exit();
						}
						
					} elseif (!$a) {
						$this->showAlert("Invalid Password");
					} else {
						$this->showAlert("Invalid Email");
					}
				}
			} else {
				$this->showAlert("Invalid Email or Password");
			}
		} elseif (empty($email)) {
			$this->showAlert("Please enter your email");
		} elseif (empty($upass)) {
			$this->showAlert("Please enter your pasword");
		} else {
			$this->showAlert("Invalid information");
		}
	}
}
?>