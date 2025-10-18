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

        $valid_ceu_domains = ['@ceu.edu.ph', '@mls.ceu.edu.ph'];
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
            // Set is_active = 1 for new registrations
            $sql = "INSERT INTO tbl_users (first_name, last_name, email, password, account_type, is_active) VALUES (?, ?, ?, ?, ?, 1)";
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
            // Check for active user with this email
            $user_data = $this->query("SELECT * FROM tbl_users WHERE email = ? AND is_active = 1", [$email]);

            if ($user_data && password_verify($password, $user_data[0]['password'])) {
                $user = $user_data[0];
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['account_type'] = $user['account_type'];

                // Add login log entry
                $this->addLoginLog($user['user_id'], $user['account_type']);

                if ($user['account_type'] == 'Super Admin') {
                header('Location: account-management.php');
                } else if ($user['account_type'] == 'Admin') {
                    header('Location: home-admin.php');
                } else {
                    header('Location: index.php');
                }
                exit();
            } else {
                // Check if the user exists but is inactive
                $inactive_user = $this->query("SELECT user_id FROM tbl_users WHERE email = ? AND is_active = 0", [$email]);
                if (!empty($inactive_user)) {
                    $errors[] = "This account has been deactivated. Please contact an administrator.";
                } else {
                    $errors[] = "Invalid email or password.";
                }
            }
        }
        
        return $errors; 
    }

    // Add login log entry based on account type

    private function addLoginLog($user_id, $account_type) {
        try {
            $current_date = date('Y-m-d H:i:s');
            
            if ($account_type === 'Admin' || $account_type === 'Super Admin') {
                // Insert into admin log table with 'Login' action
                $stmt = $this->pdo->prepare("INSERT INTO tbl_admin_log (user_id, log_action) VALUES (?, ?)");
                $stmt->execute([$user_id, 'Login']);
            } else {
                // Insert into user log table (for Student and Faculty)
                $stmt = $this->pdo->prepare("INSERT INTO tbl_user_log (user_id) VALUES (?)");
                $stmt->execute([$user_id]);
            }
            
        } catch (PDOException $e) {
            // Log the error but don't interrupt the process
            error_log("Log error: " . $e->getMessage());
        }
    }
}
?>