<?php
function logUserMsg() {
	if (isset($_POST['log-button'])) {
		$valid = new validate();
		$email = $_POST['user_email'];
		$upass = $_POST['user_password'];

		$valid->validLog($email,$upass);
	}
}
?>