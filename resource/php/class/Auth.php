<?php
require_once 'EmailService.php';
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

    // ACCESS CONTROL METHODS
    public static function requireAuth() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit();
        }
    }

    public static function requireAccountType($allowed_types) {
        self::requireAuth();
        
        if (!isset($_SESSION['account_type']) || !in_array($_SESSION['account_type'], (array)$allowed_types)) {
            self::redirectToHomePage();
        }
    }

    public static function isSuperAdmin() {
        return isset($_SESSION['account_type']) && $_SESSION['account_type'] === 'Super Admin';
    }

    public static function isAdmin() {
        return isset($_SESSION['account_type']) && $_SESSION['account_type'] === 'Admin';
    }

    public static function isStudent() {
        return isset($_SESSION['account_type']) && $_SESSION['account_type'] === 'Student';
    }

    public static function isFaculty() {
        return isset($_SESSION['account_type']) && $_SESSION['account_type'] === 'Faculty';
    }

    public static function isUser() {
        return self::isStudent() || self::isFaculty();
    }

    // Redirect method for login page
    public static function requireAuthRedirect() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['user_id'])) {
            self::redirectToHomePage();
        }
    }

    // Redirect to account-type home page
    public static function redirectToHomePage() {
        if (self::isSuperAdmin()) {
            header('Location: s-account-management.php');
        } else if (self::isAdmin()) {
            header('Location: a-home.php');
        } else if (self::isUser()) {
            header('Location: index.php');
        } else {
            header('Location: index.php');
        }
        exit();
    }

    // Convenience method for Student/Faculty access
    public static function requireUserAccess() {
        self::requireAccountType(['Student', 'Faculty']);
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
            $verification_code = $this->generateVerificationCode();
            $verification_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Set is_active = 1 but is_verified = 0 for new registrations
            $sql = "INSERT INTO tbl_users (first_name, last_name, email, password, account_type, is_active, is_verified, verification_code, verification_code_expiry) VALUES (?, ?, ?, ?, ?, 1, 0, ?, ?)";
            $this->query($sql, [$first_name, $last_name, $email, $hashed_password, $account_type, $verification_code, $verification_expiry]);
            
            // Send verification email
            $emailService = new EmailService();
            $emailSent = $emailService->sendVerificationEmail($email, $first_name . ' ' . $last_name, $verification_code);
            
            if (!$emailSent) {
                $errors[] = "Registration successful but verification email failed to send. Please contact support.";
            }
        }
        return $errors;
    }

    private function generateVerificationCode($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }

    public function verifyEmail($verification_code) {
        $current_time = date('Y-m-d H:i:s');
        
        $user = $this->query("SELECT user_id, verification_code_expiry FROM tbl_users WHERE verification_code = ? AND is_verified = 0", [$verification_code]);
        
        if (empty($user)) {
            return "Invalid or expired verification code.";
        }
        
        if ($user[0]['verification_code_expiry'] < $current_time) {
            return "Verification code has expired. Please request a new one.";
        }
        
        // Update user as verified
        $this->query("UPDATE tbl_users SET is_verified = 1, verification_code = NULL, verification_code_expiry = NULL WHERE verification_code = ?", [$verification_code]);
        
        return true;
    }

    // Update login method to check verification
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
                
                // Check if email is verified
                if (!$user['is_verified']) {
                    $errors[] = "Please verify your email address before logging in. Check your email for the verification link.";
                    return $errors;
                }
                
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['account_type'] = $user['account_type'];

                // Add login log entry
                $this->addLoginLog($user['user_id'], $user['account_type']);

                if ($user['account_type'] == 'Super Admin') {
                header('Location: s-account-management.php');
                } else if ($user['account_type'] == 'Admin') {
                    header('Location: a-home.php');
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

    public function changePassword($user_id, $current_password, $new_password, $confirm_password) {
        $errors = [];

        // 1. Validate inputs
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $errors[] = "All fields are required.";
        }
        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match.";
        }
        if (strlen($new_password) < 8) {
            $errors[] = "New password must be at least 8 characters long.";
        }

        // 2. Verify the current password
        if (empty($errors)) {
            $user_data = $this->query("SELECT password FROM tbl_users WHERE user_id = ?", [$user_id]);
            
            if (!$user_data || !password_verify($current_password, $user_data[0]['password'])) {
                $errors[] = "Incorrect current password.";
            }
        }

        // 3. If all checks pass, update the database
        if (empty($errors)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $this->query("UPDATE tbl_users SET password = ? WHERE user_id = ?", [$hashed_password, $user_id]);
        }

        return $errors;
    }

    // Add login log entry based on account type
    private function addLoginLog($user_id, $account_type) {
        try {
            $current_date = date('Y-m-d H:i:s');
            
            if ($account_type === 'Admin' || $account_type === 'Super Admin') {
                /// Insert into admin log table with 'Login' action
                $stmt = $this->pdo->prepare("INSERT INTO tbl_admin_log (user_id, log_action) VALUES (?, ?)");
                $stmt->execute([$user_id, 'Login']);
                /// Insert into admin log table with 'Login' action
                $stmt = $this->pdo->prepare("INSERT INTO tbl_admin_log (user_id, log_action) VALUES (?, ?)");
                $stmt->execute([$user_id, 'Login']);
            } else {
                // Insert into user log table (for Student and Faculty)
                $stmt = $this->pdo->prepare("INSERT INTO tbl_user_log (user_id) VALUES (?)");
                $stmt->execute([$user_id]);
            }
            
        } catch (PDOException $e) {
            error_log("Log error: " . $e->getMessage());
        }
    }
}

?>