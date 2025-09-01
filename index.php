<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css"  href="resource/css/home.css">
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
  <a class="navbar-brand" href="#">
    <img class="ceu-logo img-fluid" src="./resource/img/ceu-molecules.png"/>
  </a>
  <button class="navbar-toggler me-3 custom-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="offcanvasNavbarLabel">CEU Molecules</h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
      <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
        <li class="nav-item">
          <a class="nav-link active text-white" aria-current="page" href="#">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="#">Profile</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="#">Search</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="#">Requests</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="#">About</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="#">Help</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>
</nav>
    <!-- navbar end -->

    <!-- home -->
    <section class="home">
        <div class="container-fluid">
            <div class="row row-no-gutters align-items-center">
                <div class="col-12 col-md-6 col-lg-5">
                    <img src="./resource/img/bg.jpg" alt="ceu-building" class="img-fluid">
                </div>
                <div class="col-12 col-md-6 col-lg-7">
                    <h4 class="home-text text-center pt-2">CHEMICAL LABORATORY M.O.L.E.C.U.L.E.S</h4>
                    <p class="text-dark ps-3 pe-3" style="font-size: 0.9rem;">The CEU Malolos MOLECULES is a system developed to make requisition of laboratory materials easier, faster
                    and more reliable. The system directly sends your requisition form to the CEU Malolos Chemical Laboratory
                    so you can request materials for your research or lessons anytime, anywhere.You can track your requests
                    within the system, as well as view your requisition history. <br><br>

                    NOTE: <br>
                    1. Requests are subject to review by Chemical Laboratory Technicians. <br>
                    2. Materials are subject to specific lead times in which materials must be requested: <br> &nbsp;&nbsp;&nbsp;&nbsp;Chemicals / Supplies / Models / Charts - 2 days before intended use<br> 
                    &nbsp;&nbsp;&nbsp;&nbsp;Equipment - On the day of use<br> &nbsp;&nbsp;&nbsp;&nbsp;Specimen for instruction - 1 month before its use<br> &nbsp;&nbsp;&nbsp;&nbsp;Specimen for research - 2 months before its use<br>
                    3. Any damage/lost material must be replaced or paid at the Cashier’s office before the end of the semester.<br>
                    4. Personal information, including e-signatures, will be requested by the system in accordance to the university’s policy regarding laboratory material requisition. Personal information will be securely stored by the system with access restricted to authorized personnel only and is governed by strict confidentiality obligations in compliance with the Data Privacy Act of 2012 (Republic Act No. 10173).<br>
                    5. For student requests, an  e-signature from your subject professor or program head will be required. A copy of your requisition will be sent to them as well.</p>
                </div>
                <hr>
            </div>
            <div class="container mt-2">
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-4"> 
                    <div class="col">
                        <div class="card home-card">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <div class="card-title gradient-text"><i class="fa-solid fa-flask"></i></div>
                                <div class="card-text pt-2">CHEMICALS</div>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="card home-card">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <div class="card-title gradient-text"><i class="fa-solid fa-prescription-bottle"></i></div>
                                <div class="card-text pt-2">SUPPLIES</div>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="card home-card">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <div class="card-title gradient-text"><i class="fa-solid fa-diagram-project"></i></div> 
                                <div class="card-text pt-2">MODELS/CHARTS</div>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="card home-card">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <div class="card-title gradient-text"><i class="fa-solid fa-microscope"></i></div>
                                <div class="card-text pt-2">EQUIPMENT</div>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="card home-card">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <div class="card-title gradient-text"><i class="fa-solid fa-vial"></i></div>
                                <div class="card-text pt-2">SPECIMENS</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
        </div>
    </section>
    <!-- home -->

    <!-- footer -->
<footer class="sticky-footer  fixed-bottom">
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