<?php
class Auth extends config {
    public function __construct() {
        parent::__construct(); 
        
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function query($sql, $params = []) {
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    
    if (str_starts_with(strtoupper(trim($sql)), 'SELECT')) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return true; 
}

    public function showAlert($message, $type = 'danger') {
    echo "
    <div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
        {$message}
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>
    ";
}

    public function register($first_name, $last_name, $email, $password, $confirm_password, $student_number) {
        $errors = [];
        $account_type = ''; 

        if (empty(trim($first_name))) $errors[] = "First Name is required.";
        if (empty(trim($last_name))) $errors[] = "Last Name is required.";
        if (empty(trim($email))) $errors[] = "Email is required.";
        if (empty(trim($password))) $errors[] = "Password is required.";

        if (!empty($student_number)) {
            $account_type = 'Student';
        } else {
            $account_type = 'Faculty';
        }

        $valid_ceu_domains = ['@ceu.edu.ph', '@malolos.ceu.edu.ph'];
        $user_email_domain = strtolower(substr($email, strpos($email, '@')));
        
        if (!in_array($user_email_domain, $valid_ceu_domains)) {
            $errors[] = "Registration is only for CEU members.";
        }
        
        $user = $this->query("SELECT user_id FROM tbl_users WHERE email = ?", [$email]);
        if (!empty($user)) {
            $errors[] = "This email has already been registered.";
        }
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        }

        if (empty($errors)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO tbl_users (first_name, last_name, email, password, account_type) VALUES (?, ?, ?, ?, ?)";
            $this->query($sql, [$first_name, $last_name, $email, $hashed_password, $account_type]);
        }
        return $errors;
    }

    public function login($email, $password) {
        $errors = []; 

        if (empty(trim($email))) { 
            $errors[] = "Please enter your email.";
        }
        if (empty(trim($password))) { 
            $errors[] = "Please enter your password.";
        }

        if (empty($errors)) {
            $user_data = $this->query("SELECT * FROM tbl_users WHERE email = ?", [$email]);

            if ($user_data && password_verify($password, $user_data[0]['password'])) {
                $user = $user_data[0];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['account_type'] = $user['account_type'];

                if ($user['account_type'] == 'Admin') {
                    header('Location: admin/products.php');
                } else {
                    header('Location: index.php');
                }
                exit();
            } else {
                $errors[] = "Invalid email or password.";
            }
        }
        
        return $errors; 
    }
}
?>