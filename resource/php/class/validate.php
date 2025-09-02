<?php
include 'insertUser.php';

class validate extends config {
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
			echo '
			<div class="alert alert-danger alert-dismissible fade show my-3" role="alert">
				Email has already been used
				<button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="close"></button>	
			</div>
			';
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
						echo '
						<div class="alert alert-danger alert-dismissible fade show my-3" role="alert">
							Invalid password
							<button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="close"></button>	
						</div>
						';
					} else {
						echo '
						<div class="alert alert-danger alert-dismissible fade show my-3" role="alert">
							Invalid email
							<button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="close"></button>	
						</div>
						';
					}
				}
			} else {
				echo '
				<div class="alert alert-danger alert-dismissible fade show my-3" role="alert">
					Invalid email or password
					<button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="close"></button>	
				</div>
				';
			}
		} elseif (empty($email)) {
			echo '
			<div class="alert alert-danger alert-dismissible fade show my-3" role="alert">
				Please enter your email
				<button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="close"></button>	
			</div>
			';
		} elseif (empty($upass)) {
			echo '
			<div class="alert alert-danger alert-dismissible fade show my-3" role="alert">
				Please enter your password
				<button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="close"></button>	
			</div>
			';
		} else {
			echo '
			<div class="alert alert-danger alert-dismissible fade show my-3" role="alert">
				Invalid information
				<button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="close"></button>	
			</div>
			';
		}
	}
}
?>