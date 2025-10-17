<?php
session_start();

$is_logged_in = isset($_SESSION['user_id']);
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About - CEU Molecules</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
  <link rel="stylesheet" type="text/css"  href="resource/css/about.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Bona+Nova:ital,wght@0,400;0,700;1,400&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Quicksand:wght@300..700&family=Roboto:ital,wght@0,100..900;1,100..900&family=Rubik:ital,wght@0,300..900;1,300..900&family=Ruda:wght@400..900&family=Tilt+Warp&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

  <script src="https://kit.fontawesome.com/6563a04357.js" crossorigin="anonymous"></script>
</head>

<body>
<?php
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success alert-dismissible fade show m-3" role="alert">';
        echo    $_SESSION['success_message'];
        echo    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        
        unset($_SESSION['success_message']);
    }
  ?>

<!-- navbar -->
<nav class="navbar">
  <a class="navbar-brand" href="index.php">
    <img class="ceu-logo img-fluid" src="./resource/img/ceu-molecules.png"/>
  </a>
  <div class="right-side-icons">
    <i class="fa-solid fa-cart-shopping cart-icon"></i>
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
          <a class="nav-link text-white" href="user-search.php">Search</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="cart.php">Requests</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active text-white" aria-current="page" href="about.php">About</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="help.php">Help</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>
</nav>

<!-- About Section -->
<section class="about py-5">
    <div class="container">
        <!-- System Information -->
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="home-text mb-4 text-center">About CEU MOLECULES</h2>
                <p class="text-dark mb-3">
                    The CEU Malolos MOLECULES (Management of Orders and Laboratory Equipment with Chemical Utilization Logging and Evaluation System with AI) is an innovative digital platform designed to revolutionize the way laboratory materials are requested and managed within the Centro Escolar University Malolos community.
                </p>
                <p class="text-dark mb-3">
                    Our mission is to provide a seamless, efficient, and reliable system that connects students, faculty, and laboratory technicians, ensuring that essential laboratory resources are accessible when needed for academic and research purposes. This system addresses the challenges faced by the Science Laboratory in managing material requests, 
                    streamlining the requisition process, and maintaining accurate inventory records through digital transformation.
                </p>
                <p class="text-dark">
                    This system was created as part of the academic requirements of the developers for their Undergraduate Research under the supervision of their research adviser Mr. John Carlo Gamboa, with the aim of enhancing the overall science laboratory experience and supporting the academic excellence of CEU Malolos.
                </p>
            </div>
        </div>

        <hr class="my-5">

        <!-- Laboratory Technicians Section -->
        <div class="row mb-5">
            <div class="col-12 text-center mb-4">
                <h3 class="home-text">Meet Our Laboratory Technicians</h3>
                <p class="text-muted">Dedicated professionals ensuring the smooth operation of the CEU Malolos science laboratory</p>
            </div>
            
            <div class="col-12 col-md-4 mb-4">
                <div class="card tech-card h-100">
                    <div class="card-img-container">
                        <img src="./resource/img/tech1-placeholder.jpg" alt="Senior Lab Technician" class="card-img-top">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Mr. John Rexlester T. Santos</h5>
                        <h6 class="card-subtitle mb-2 text-muted">Coordinator, Science Laboratory Section</h6>
                        <p class="card-text">Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ipsa tenetur nam in, adipisci quidem aspernatur, eaque nesciunt repellendus suscipit sed, quis sapiente impedit incidunt obcaecati architecto quas dolor ratione magni!</p>
                        <p class="card-text contact-info"><small class="text-muted">jramos@ceu.edu.ph | jtramos@ceu.edu.ph</small></p>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-4 mb-4">
                <div class="card tech-card h-100">
                    <div class="card-img-container">
                        <img src="./resource/img/tech2-placeholder.jpg" alt="Lab Technician" class="card-img-top">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Ms. Kim Sacdalan</h5>
                        <h6 class="card-subtitle mb-2 text-muted">Laboratory Technician</h6>
                        <p class="card-text">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aspernatur, architecto provident. Atque nemo illo dolorem eaque fugiat excepturi, iure nostrum reprehenderit animi consequuntur minima qui eum dicta veritatis cumque mollitia!</p>
                        <p class="card-text contact-info"><small class="text-muted">kdbacdalan@ceu.edu.ph</small></p>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-4 mb-4">
                <div class="card tech-card h-100">
                    <div class="card-img-container">
                        <img src="./resource/img/placeholder.webp" alt="Assistant Lab Technician" class="card-img-top">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Mr. John Doe</h5>
                        <h6 class="card-subtitle mb-2 text-muted">Assistant Laboratory Technician</h6>
                        <p class="card-text">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Illum, repellat ad. Ratione facere amet velit, nihil non ipsum, aliquam cum distinctio quas facilis accusamus? Odit dolorum totam eligendi minus similique.</p>
                        <p class="card-text contact-info"><small class="text-muted">sample@ceu.edu.ph</small></p>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-5">

        <!-- Development Team Section -->
        <div class="row">
            <div class="col-12 text-center mb-4">
                <h3 class="home-text">Development Team</h3>
                <p class="text-muted">The talented individuals behind the CEU MOLECULES system</p>
            </div>
            
            <div class="col-12 col-md-4 mb-4">
                <div class="card dev-card h-100">
                    <div class="card-img-container">
                        <img src="./resource/img/rmagsakay.webp" alt="Renz Magsakay" class="card-img-top">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Renz Matthew Magsakay</h5>
                        <h6 class="card-subtitle mb-2 text-muted">Lead Developer</h6>
                        <p class="card-text">Full-stack developer responsible for system architecture and backend development. Ensures the system's reliability and performance.</p>
                        <p class="card-text contact-info"><small class="text-muted">official.renzmagsakay@gmail.com</small></p>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-4 mb-4">
                <div class="card dev-card h-100">
                    <div class="card-img-container">
                        <img src="./resource/img/tech2-placeholder.jpg" alt="Krizia Lleva" class="card-img-top">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Krizia Jane Lleva</h5>
                        <h6 class="card-subtitle mb-2 text-muted">Frontend Developer & UI/UX Designer</h6>
                        <p class="card-text">Creates intuitive user interfaces and ensures seamless user experience. Manages the visual design and user interaction flow.</p>
                        <p class="card-text contact-info"><small class="text-muted">lleva2234517@mls.ceu.edu.ph</small></p>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-4 mb-4">
                <div class="card dev-card h-100">
                    <div class="card-img-container">
                        <img src="./resource/img/tech2-placeholder.jpg" alt="Gelai Gabriel" class="card-img-top">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Angelique Mae Gabriel</h5>
                        <h6 class="card-subtitle mb-2 text-muted">Backend Developer & Database Architect</h6>
                        <p class="card-text">Designs and maintains the database structure. Implements business logic and ensures data integrity throughout the system.</p>
                        <p class="card-text contact-info"><small class="text-muted">gabriel2231439@mls.ceu.edu.ph</small></p>
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
</body>
</html>