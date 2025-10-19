<?php
session_start();
require_once 'resource/php/init.php';
require_once 'resource/php/class/Auth.php';
Auth::requireUserAccess();

require_once 'vendor/phpmailer/src/Exception.php';
require_once 'vendor/phpmailer/src/PHPMailer.php';
require_once 'vendor/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if (basename($_SERVER['PHP_SELF']) !== 'change-pass.php') {
    $_SESSION['previous_page'] = $_SERVER['REQUEST_URI'];
}

$errors = [];
$preserved_data = [
    'name' => '',
    'email' => '',
    'subject' => '',
    'message' => ''
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    $preserved_data = [
        'name' => htmlspecialchars($name),
        'email' => htmlspecialchars($email),
        'subject' => htmlspecialchars($subject),
        'message' => htmlspecialchars($message)
    ];

    // Validation
    if (empty($name)) $errors[] = "Name is required.";
    if (empty($email)) $errors[] = "Email is required.";
    if (empty($subject)) $errors[] = "Subject is required.";
    if (empty($message)) $errors[] = "Message is required.";

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (!empty($message) && strlen($message) < 10) {
        $errors[] = "Message should be at least 10 characters long.";
    }

    if (empty($errors)) {
        try {
            $mail = new PHPMailer(true);
            
            // Server settings proxy email
            $mail->isSMTP();
            $mail->Host       = ''; 
            $mail->SMTPAuth   = true;
            $mail->Username   = '';
            $mail->Password   = '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            $mail->setFrom('ceumolecules.system@gmail.com', 'CEU MOLECULES System');
            
            $mail->addAddress('magsakay2233884@mls.ceu.edu.ph', 'Lab Technician');
            
            $mail->addReplyTo($email, $name);
            
            // Content
            $mail->isHTML(false);
            $mail->Subject = "CEU Molecules Contact: " . $subject;
            $mail->Body    = "You have received a new contact form message:\n\n"
                           . "From: $name\n"
                           . "Email: $email\n"
                           . "Subject: $subject\n\n"
                           . "Message:\n$message\n\n"
                           . "---\n"
                           . "This message was sent via the CEU MOLECULES System\n"
                           . "User ID: " . $_SESSION['user_id'] . "\n"
                           . "Account Type: " . $_SESSION['account_type'];
            
            $mail->send();
            $_SESSION['success_message'] = "Your message has been sent successfully! We'll get back to you soon.";
            header('Location: help.php');
            exit();
            
        } catch (Exception $e) {
            $errors[] = "Sorry, there was an error sending your message. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact - CEU Molecules</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
  <link rel="stylesheet" type="text/css"  href="resource/css/home.css">
  <link rel="stylesheet" type="text/css"  href="resource/css/help.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Bona+Nova:ital,wght@0,400;0,700;1,400&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Rubik:ital,wght@0,300..900;1,300..900&family=Ruda:wght@400..900&family=Tilt+Warp&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

  <script src="https://kit.fontawesome.com/6563a04357.js" crossorigin="anonymous"></script>
</head>

<body>
<!-- navbar -->
<nav class="navbar">
  <a class="navbar-brand" href="index.php">
    <img class="ceu-logo img-fluid" src="./resource/img/ceu-molecules.png"/>
  </a>
  <div class="right-side-icons">
    <a href="u-cart.php"><i class="fa-solid fa-cart-shopping cart-icon"></i></a>
      <button class="navbar-toggler me-3 custom-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  </div>

  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="offcanvasNavbarLabel">CEU Molecules</h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
      <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
        <li class="nav-item">
          <a class="nav-link text-white" href="index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="change-pass.php">Change Password</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="u-search.php">Search</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="u-request.php">Requests</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="u-about.php">About</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white active" aria-current="page" href="u-help.php">Help</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>
</nav>

<!-- Help Section -->
<section class="help py-4">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-4">
                <h2 class="section-header">Help Center</h2>
                <p class="section-subtitle">Find answers to common questions or contact our laboratory technicians</p>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="row">
            <div class="col-12">
                <div class="faq-container">
                    <h3 class="section-header mb-3"><i class="fas fa-question-circle me-2"></i>Frequently Asked Questions</h3>
                    
                    <div class="faq-item">
                        <div class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="false" aria-controls="faq1">
                            <i class="fas fa-chevron-right"></i> How long does it take to process a request?
                        </div>
                        <div class="collapse faq-answer" id="faq1">
                            Requests are typically processed within 24-48 hours during weekdays. However, processing times may vary depending on the type of material and current laboratory workload.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false" aria-controls="faq2">
                            <i class="fas fa-chevron-right"></i> What are the lead times for different materials?
                        </div>
                        <div class="collapse faq-answer" id="faq2">
                            <ul>
                                <li>Chemicals / Supplies / Models / Charts: 2 days before intended use</li>
                                <li>Equipment: On the day of use</li>
                                <li>Specimen for instruction: 1 month before its use</li>
                                <li>Specimen for research: 2 months before its use</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false" aria-controls="faq3">
                            <i class="fas fa-chevron-right"></i> What happens if I damage or lose a material?
                        </div>
                        <div class="collapse faq-answer" id="faq3">
                            Any damaged or lost material must be replaced or paid for at the Cashier's office before the end of the semester. Please handle all laboratory materials with care.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq4" aria-expanded="false" aria-controls="faq4">
                            <i class="fas fa-chevron-right"></i> Do I need to submit a physical form after making a request online?
                        </div>
                        <div class="collapse faq-answer" id="faq4">
                            Yes. Once your request is finalized, the system will provide a downloadable PDF that you must print, sign, and present to the science laboratory technicians in person. Students will also need their adviser's signature.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq5" aria-expanded="false" aria-controls="faq5">
                            <i class="fas fa-chevron-right"></i> Can I cancel or modify a request after submission?
                        </div>
                        <div class="collapse faq-answer" id="faq5">
                            Requests cannot be modified upon finalization but it can cancelled if you have not yet submitted the physical form to the laboratory technicians. Once processing has begun, please contact the laboratory directly for any changes.
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq6" aria-expanded="false" aria-controls="faq6">
                            <i class="fas fa-chevron-right"></i> What should I do if I'm having technical issues with the system?
                        </div>
                        <div class="collapse faq-answer" id="faq6">
                            If you're experiencing technical difficulties with the CEU Molecules system, please contact us using the form below with details about the issue you're encountering.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Section -->
        <div class="row mt-4">
            <div class="col-12 text-center mb-3">
                <h3 class="section-header">Still Need Help?</h3>
                <p class="section-subtitle">Contact our laboratory technicians directly</p>
            </div>
        </div>

        <div class="row">
            <!-- Contact Form -->
            <?php
                if (isset($_SESSION['success_message'])) {
                    echo '<div class="alert alert-success alert-dismissible fade show m-3" role="alert">';
                    echo    $_SESSION['success_message'];
                    echo    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                    echo '</div>';
                    
                    unset($_SESSION['success_message']);
                }
            ?>
            <div class="col-12 col-md-8 mb-4">
                <div class="contact-form-container">
                    <?php
                    if (!empty($errors)) {
                        foreach ($errors as $error) {
                            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
                            echo    $error;
                            echo    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                            echo '</div>';
                        }
                    }
                    ?>

                    <form method="POST" action="help.php">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?= $preserved_data['name'] ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= $preserved_data['email'] ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" 
                                   value="<?= $preserved_data['subject'] ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="6" 
                                      required><?= $preserved_data['message'] ?></textarea>
                            <div class="form-text">Minimum 10 characters required.</div>
                        </div>
                        
                        <div class="btn-div text-center">
                            <button type="submit" class="btn btn-contact btn-lg">
                                Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Contact Information -->
            <div class="col-12 col-md-4">
                <div class="contact-info h-100">
                    <h4 class="mb-4"><i class="fas fa-info-circle me-2"></i>Contact Information</h4>
                    
                    <div class="mb-4">
                        <h6><i class="fas fa-envelope me-2"></i>Email</h6>
                        <p>sample@mls.ceu.edu.ph</p>
                    </div>
                    
                    <div class="mb-4">
                        <h6><i class="fas fa-clock me-2"></i>Response Time</h6>
                        <p>We typically respond within 24-48 hours during weekdays.</p>
                    </div>
                    
                    <div class="mb-4">
                        <h6><i class="fas fa-exclamation-circle me-2"></i>Note</h6>
                        <p>For urgent laboratory matters, please visit the Science Laboratory in person.</p>
                    </div>
                    
                    <div class="mt-4">
                        <h6>Common Inquiries:</h6>
                        <ul class="small">
                            <li>Material availability</li>
                            <li>Request status updates</li>
                            <li>Technical issues with the system</li>
                            <li>Laboratory policies and procedures</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- footer -->
<footer>
  <div class="container-fluid">
    <p class="text-center text-white pt-2"><small>
      CEU MALOLOS MOLECULES || <strong>Chemical Laboratory: sample@ceu.edu.ph</strong><br>
      <i class="fa-regular fa-copyright"></i> 2025 Copyright <strong>CENTRO ESCOLAR UNIVERSITY MALOLOS, Chemical Laboratory</strong><br>
      Developed by <strong>Renz Matthew Magsakay (official.renzmagsakay@gmail.com), Krizia Jane Lleva (lleva2234517@mls.ceu.edu.ph) & Angelique Mae Gabriel (gabriel2231439@mls.ceu.edu.ph)</strong>
      </small>
    </p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>

<script>
    document.querySelectorAll('.faq-question').forEach(question => {
        question.addEventListener('click', function() {
            const icon = this.querySelector('i');
            if (icon.classList.contains('fa-chevron-right')) {
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-down');
            } else {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-right');
            }
        });
    });
</script>
</body>
</html>