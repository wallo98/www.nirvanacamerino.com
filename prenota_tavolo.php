<?php
session_start();

// FLAG MANUTENZIONE - Imposta a true per attivare la manutenzione
$manutenzione_attiva = false;

// Controlla se sono già passate le 22:32 - se sì, disattiva automaticamente la manutenzione
$ora_corrente = (int)date('H');
$minuto_corrente = (int)date('i');
if ($ora_corrente > 22 || ($ora_corrente == 22 && $minuto_corrente >= 32)) {
    $manutenzione_attiva = false;
}

if ($manutenzione_attiva) {
    // Mostra pagina di manutenzione
    ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <title>Manutenzione - Nirvana Pub Pizzeria</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Josefin+Sans" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }
        
        .maintenance-container {
            text-align: center;
            color: white;
            z-index: 10;
            position: relative;
            padding: 40px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 90%;
        }
        
        .logo-maintenance {
            max-width: 150px;
            margin-bottom: 30px;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 600;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .subtitle {
            font-size: 1.2rem;
            margin-bottom: 40px;
            opacity: 0.9;
        }
        
        .message {
            font-size: 1rem;
            margin-top: 30px;
            opacity: 0.85;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <img src="images/logo.png" alt="Nirvana Logo" class="logo-maintenance">
        <h1><i class="fas fa-tools"></i> Manutenzione in Corso</h1>
        <p class="subtitle">Stiamo aggiornando il sistema di prenotazioni</p>
        <p class="message">
            Ci scusiamo per il disagio. Torneremo online tra poco tempo.<br>
            Grazie per la tua pazienza!
        </p>
    </div>
</body>
</html>
    <?php
    exit;
}

// CODICE NORMALE
include 'librerie/Database.php';
include 'librerie/metodi.php';

$db = new Database();

//controlla se è presente id utente altrimenti lo crea
if (!isset($_SESSION['id_utente'])) {
    $_SESSION['id_utente'] = uniqid();
    setcookie('id_utente', $_SESSION['id_utente'], time() + (86400 * 30), "/");
}

//prendo id_utente
$id_utente = $_SESSION['id_utente'];

$id_carrello=get_db_value("SELECT id_carrello FROM carrello WHERE id_utente= '$id_utente' AND flag_ordinato IS NULL");

$numeroProdotti = get_db_value("
    select COUNT( prodotticarrello.id_prodottiCarrello) 
    from prodotticarrello 
    inner join carrello on prodotticarrello.id_carrello= carrello.id_carrello where carrello.id_carrello='$id_carrello'"
);

?>

<!DOCTYPE html>
<html lang="it">
  <head>
    <title>Prenota un Tavolo - Nirvana</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Josefin+Sans" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nothing+You+Could+Do" rel="stylesheet">

    <link rel="stylesheet" href="css/open-iconic-bootstrap.min.css">
    <link rel="stylesheet" href="css/animate.css">
    
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/owl.theme.default.min.css">
    <link rel="stylesheet" href="css/magnific-popup.css">

    <link rel="stylesheet" href="css/aos.css">

    <link rel="stylesheet" href="css/ionicons.min.css">

    <link rel="stylesheet" href="css/sidebar.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="css/bootstrap-datepicker.css">
    <link rel="stylesheet" href="css/jquery.timepicker.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <link rel="stylesheet" href="css/flaticon.css">
    <link rel="stylesheet" href="css/icomoon.css">
    <link rel="stylesheet" href="css/style.css">

    <style>
        /* Hero Section Style */
        .hero-prenotazione {
            position: relative;
            padding: 80px 0 40px;
            background: linear-gradient(135deg, rgba(26, 26, 26, 0.95) 0%, rgba(40, 40, 40, 0.95) 100%);
        }

        .hero-prenotazione::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('images/bg_1.jpg') center center;
            background-size: cover;
            opacity: 0.15;
            z-index: -1;
        }

        /* Main Container */
        .prenotazione-container {
            background: linear-gradient(145deg, #1a1a1a 0%, #2d2d2d 100%);
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.4),
                        0 0 0 1px rgba(250, 197, 100, 0.1);
            padding: 50px;
            margin: 20px auto;
            position: relative;
            overflow: hidden;
        }

        .prenotazione-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #f96d00, #fac564, #f96d00);
            background-size: 200% 100%;
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        /* Form Controls */
        .form-control {
            background-color: rgba(255, 255, 255, 0.05) !important;
            color: #fff !important;
            border: 2px solid rgba(255, 255, 255, 0.1) !important;
            padding: 14px 18px !important;
            font-size: 16px !important;
            border-radius: 12px !important;
            transition: all 0.3s ease !important;
        }

        .form-control:focus {
            border-color: #fac564 !important;
            box-shadow: 0 0 0 3px rgba(250, 197, 100, 0.15),
                        0 0 20px rgba(250, 197, 100, 0.1) !important;
            background-color: rgba(255, 255, 255, 0.08) !important;
            color: #fff !important;
            transform: translateY(-2px);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4) !important;
            opacity: 1 !important;
        }

        select.form-control {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23fac564' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            padding-right: 40px !important;
            cursor: pointer;
        }

        select.form-control option {
            background-color: #1a1a1a;
            color: #fff;
            padding: 10px;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        /* Labels */
        .form-label {
            font-weight: 500;
            color: #fac564;
            margin-bottom: 10px;
            display: block;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .required::after {
            content: " *";
            color: #f96d00;
        }

        /* Form Groups */
        .form-group-custom {
            margin-bottom: 25px;
            position: relative;
        }

        /* Submit Button */
        .btn-prenota {
            background: linear-gradient(135deg, #fac564 0%, #f9b84d 100%);
            border: none;
            padding: 18px 60px;
            font-size: 16px;
            font-weight: 700;
            border-radius: 50px;
            transition: all 0.4s ease;
            color: #1a1a1a !important;
            text-transform: uppercase;
            letter-spacing: 2px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(250, 197, 100, 0.3);
        }

        .btn-prenota::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-prenota:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(250, 197, 100, 0.4),
                        0 0 0 3px rgba(250, 197, 100, 0.2);
        }

        .btn-prenota:hover::before {
            left: 100%;
        }

        .btn-prenota:active {
            transform: translateY(-2px);
        }

        /* Info Box */
        .info-box {
            background: rgba(250, 197, 100, 0.08);
            border-left: 4px solid #fac564;
            padding: 20px 25px;
            margin-bottom: 35px;
            border-radius: 0 12px 12px 0;
            color: rgba(255, 255, 255, 0.85);
        }

        .info-box h5 {
            color: #fac564;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .info-box i {
            color: #fac564;
            margin-right: 10px;
        }

        /* Messages */
        .success-message {
            display: none;
            background: linear-gradient(135deg, rgba(39, 174, 96, 0.15) 0%, rgba(46, 204, 113, 0.1) 100%);
            border: 2px solid rgba(46, 204, 113, 0.3);
            color: #2ecc71;
            padding: 20px 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            animation: slideIn 0.5s ease;
        }

        .error-message {
            display: none;
            background: linear-gradient(135deg, rgba(231, 76, 60, 0.15) 0%, rgba(192, 57, 43, 0.1) 100%);
            border: 2px solid rgba(231, 76, 60, 0.3);
            color: #e74c3c;
            padding: 20px 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-message i, .error-message i {
            font-size: 20px;
            margin-right: 10px;
        }

        /* Section Styling */
        .ftco-section {
            padding: 3em 0 !important;
            background: linear-gradient(180deg, #1a1a1a 0%, #252525 100%);
        }

        /* Heading Styling */
        .heading-section h2 {
            color: #fff;
            font-weight: 700;
            position: relative;
            display: inline-block;
        }

        .heading-section p {
            color: rgba(255, 255, 255, 0.7);
        }

        /* Icons in form */
        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #fac564;
            font-size: 16px;
            z-index: 10;
        }

        .input-icon .form-control {
            padding-left: 45px !important;
        }

        /* Row spacing */
        .row.mb-custom {
            margin-bottom: 5px;
        }

        /* Decorative elements */
        .decoration-circle {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(250, 197, 100, 0.1) 0%, rgba(249, 109, 0, 0.05) 100%);
            pointer-events: none;
        }

        .decoration-circle.circle-1 {
            width: 200px;
            height: 200px;
            top: -100px;
            right: -100px;
        }

        .decoration-circle.circle-2 {
            width: 150px;
            height: 150px;
            bottom: -75px;
            left: -75px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .prenotazione-container {
                padding: 30px 20px;
                margin: 10px;
            }

            .btn-prenota {
                padding: 15px 40px;
                font-size: 14px;
                width: 100%;
            }

            .form-label {
                font-size: 12px;
            }

            .form-control {
                padding: 12px 14px !important;
                font-size: 14px !important;
            }
        }

        /* Floating label animation */
        .floating-input {
            position: relative;
        }

        /* Card hover effect for form sections */
        .form-section {
            padding: 25px;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 15px;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .form-section:hover {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(250, 197, 100, 0.15);
        }

        .form-section-title {
            color: #fac564;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(250, 197, 100, 0.2);
        }

        .form-section-title i {
            margin-right: 10px;
        }
    </style>
  </head>
  <body>
  	<nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
	    <div class="container">
        <img src="images/logo.png" alt="Nirvana Logo" class="logo-img" style="max-height: 50px;">
        <a class="navbar-brand" href="/home">
            <br>Nirvana<small>Pub Pizzeria</small>
            <br>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
	        <span class="oi oi-menu"></span> 
	      </button>
	      <div class="collapse navbar-collapse" id="ftco-nav">
	        <ul class="navbar-nav ml-auto">
	          <li class="nav-item"><a href="/home" class="nav-link">Home</a></li>
	          <li class="nav-item"><a href="/menu" class="nav-link">Menu</a></li>
	          <li class="nav-item"><a href="/menuKebab" class="nav-link">Secondo Menu</a></li>
	          <li class="nav-item"><a href="/blog" class="nav-link">Eventi</a></li>
	          <li class="nav-item"><a href="/contatti" class="nav-link">Contatti</a></li>
	        </ul>
	      </div>
		  </div>
	  </nav>

    <section class="ftco-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-7 heading-section ftco-animate text-center">
                    <h2 class="mb-4">Prenota un Tavolo</h2>
                    <p class="flip"><span class="deg1"></span><span class="deg2"></span><span class="deg3"></span></p>
                    <p style="margin-top: 20px;">Riserva il tuo posto per una serata indimenticabile al Nirvana</p>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-9 col-md-10">
                    <div class="prenotazione-container">
                        <!-- Decorative circles -->
                        <div class="decoration-circle circle-1"></div>
                        <div class="decoration-circle circle-2"></div>
                        
                        <div class="success-message" id="successMessage">
                            <i class="fas fa-check-circle"></i> <strong>Prenotazione inviata con successo!</strong><br>
                            <span style="opacity: 0.9;">Riceverai una conferma via email o telefono entro breve tempo.</span>
                        </div>

                        <div class="error-message" id="errorMessage">
                            <i class="fas fa-exclamation-triangle"></i> <strong>Attenzione!</strong> <span id="errorText"></span>
                        </div>

                        <form id="prenotazioneForm">
                            <!-- Sezione Dati Personali -->
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fas fa-user"></i> Dati Personali
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nome" class="form-label required">Nome</label>
                                        <div class="input-icon">
                                            <i class="fas fa-user-edit"></i>
                                            <input type="text" class="form-control" id="nome" name="nome" placeholder="Il tuo nome" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="cognome" class="form-label required">Cognome</label>
                                        <div class="input-icon">
                                            <i class="fas fa-user-tag"></i>
                                            <input type="text" class="form-control" id="cognome" name="cognome" placeholder="Il tuo cognome" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="telefono" class="form-label required">Telefono</label>
                                        <div class="input-icon">
                                            <i class="fas fa-phone-alt"></i>
                                            <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="+39 123 456 7890" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label required">Email</label>
                                        <div class="input-icon">
                                            <i class="fas fa-envelope"></i>
                                            <input type="email" class="form-control" id="email" name="email" placeholder="nome@esempio.it" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sezione Dettagli Prenotazione -->
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fas fa-calendar-alt"></i> Dettagli Prenotazione
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="data" class="form-label required">Data</label>
                                        <div class="input-icon">
                                            <i class="fas fa-calendar-day"></i>
                                            <input type="date" class="form-control" id="data" name="data" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="ora" class="form-label required">Ora</label>
                                        <div class="input-icon">
                                            <i class="fas fa-clock"></i>
                                            <input type="time" class="form-control" id="ora" name="ora" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="numero_persone" class="form-label required">Persone</label>
                                        <div class="input-icon">
                                            <i class="fas fa-users"></i>
                                            <select class="form-control" id="numero_persone" name="numero_persone" required>
                                                <option value="">Seleziona...</option>
                                                <option value="1">1 persona</option>
                                                <option value="2">2 persone</option>
                                                <option value="3">3 persone</option>
                                                <option value="4">4 persone</option>
                                                <option value="5">5 persone</option>
                                                <option value="6">6 persone</option>
                                                <option value="7">7 persone</option>
                                                <option value="8">8 persone</option>
                                                <option value="9">9 persone</option>
                                                <option value="10">10+ persone</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sezione Note -->
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fas fa-comment-dots"></i> Note Aggiuntive
                                </div>
                                <div class="mb-3">
                                    <label for="note" class="form-label">Richieste Speciali <span style="opacity: 0.6; font-size: 11px;">(opzionale)</span></label>
                                    <textarea class="form-control" id="note" name="note" rows="4" placeholder="Eventuali richieste particolari, allergie, intolleranze, occasioni speciali..."></textarea>
                                </div>
                            </div>

                            <!-- Info Box -->
                            <div class="info-box">
                                <h5><i class="fas fa-info-circle"></i> Informazioni Utili</h5>
                                <p style="margin: 0; font-size: 14px;">
                                    La prenotazione verrà confermata telefonicamente o via email. </strong>
                                </p>
                            </div>

                            <div class="text-center mt-5">
                                <button type="submit" class="btn btn-primary btn-prenota">
                                    <i class="fas fa-paper-plane"></i>&nbsp;&nbsp;Invia Prenotazione
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="ftco-footer ftco-section img">
      <div class="overlay"></div>
      <div class="container">
          <div class="row mb-5">
          <div class="col-lg-4 col-md-6 mb-5">
                  <div class="ftco-footer-widget mb-4">
                      <h2 class="ftco-heading-2">Recapiti</h2>
                      <div class="block-23 mb-3">
                          <ul>
                              <li><span class="icon icon-map-marker"></span><span class="text">Via Madonna delle carceri 4, Camerino, MC 62032</span></li>
                              <li><span class="icon icon-phone"></span><span class="text">+39 389 694 5088</span></li>
                              <li><span class="icon icon-envelope"></span><span class="text">nirvanacamerino@gmail.com</span></li>
                          </ul>
                      </div>
                  </div>
              </div>
  
              <div class="col-lg-4 col-md-6 mb-5">
                  <div class="ftco-footer-widget mb-4 ml-md-4">
                      <h2 class="ftco-heading-2">Pagine</h2>
                      <ul class="list-unstyled">
                        <li><a href="/home" class="py-2 d-block">Home</a></li>
                        <li><a href="/menu" class="py-2 d-block">Menu</a></li>
                        <li><a href="/menuKebab" class="py-2 d-block">Secondo Menu</a></li>
                        <li><a href="/blog" class="py-2 d-block">Eventi</a></li>
                        <li><a href="/contatti" class="py-2 d-block">Contatti</a></li>
                    </ul>
                  </div>
              </div>
          </div>
          <div class="row">
              <div class="col-md-12 text-center">
                  <p>
                      Copyright &copy;<script>document.write(new Date().getFullYear());</script> All rights reserved
                  </p>
              </div>
          </div>
      </div>
  </footer>

  <!-- loader -->
  <div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px"><circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/><circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00"/></svg></div>

  <script src="js/jquery.min.js"></script>
  <script src="js/jquery-migrate-3.0.1.min.js"></script>
  <script src="js/popper.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/jquery.easing.1.3.js"></script>
  <script src="js/jquery.waypoints.min.js"></script>
  <script src="js/jquery.stellar.min.js"></script>
  <script src="js/owl.carousel.min.js"></script>
  <script src="js/jquery.magnific-popup.min.js"></script>
  <script src="js/aos.js"></script>
  <script src="js/bootstrap-datepicker.js"></script>
  <script src="js/jquery.timepicker.min.js"></script>
  <script src="js/scrollax.min.js"></script>
  <script src="js/main.js"></script>

  <script>
    $(document).ready(function() {
        // Imposta la data minima di oggi
        var today = new Date().toISOString().split('T')[0];
        document.getElementById("data").setAttribute('min', today);

        // Aggiungi effetto focus alle sezioni del form
        $('.form-control').on('focus', function() {
            $(this).closest('.form-section').css('border-color', 'rgba(250, 197, 100, 0.3)');
        }).on('blur', function() {
            $(this).closest('.form-section').css('border-color', 'rgba(255, 255, 255, 0.05)');
        });

        // Gestione submit form
        $('#prenotazioneForm').on('submit', function(e) {
            e.preventDefault();
            
            // Nascondi messaggi precedenti
            $('#successMessage').hide();
            $('#errorMessage').hide();

            // Mostra stato loading sul pulsante
            var $btn = $('.btn-prenota');
            var originalText = $btn.html();
            $btn.html('<i class="fas fa-spinner fa-spin"></i>&nbsp;&nbsp;Invio in corso...').prop('disabled', true);

            // Raccogli i dati dal form
            var formData = {
                nome: $('#nome').val(),
                cognome: $('#cognome').val(),
                telefono: $('#telefono').val(),
                email: $('#email').val(),
                data: $('#data').val(),
                ora: $('#ora').val(),
                numero_persone: $('#numero_persone').val(),
                note: $('#note').val()
            };

            // Invia i dati via AJAX
            $.ajax({
                type: "POST",
                url: 'action.php?_action=prenotaTavolo',
                data: formData,
                dataType: 'json',
                success: function(result) {
                    // Ripristina il pulsante
                    $btn.html(originalText).prop('disabled', false);
                    
                    if(result.status == 1) {
                        $('#successMessage').show();
                        $('#prenotazioneForm')[0].reset();
                        
                        // Effetto confetti (opzionale visual feedback)
                        $('.prenotazione-container').css('box-shadow', '0 15px 50px rgba(46, 204, 113, 0.3), 0 0 0 1px rgba(46, 204, 113, 0.2)');
                        setTimeout(function() {
                            $('.prenotazione-container').css('box-shadow', '0 15px 50px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(250, 197, 100, 0.1)');
                        }, 2000);
                        
                        // Scroll to success message
                        $('html, body').animate({
                            scrollTop: $('#successMessage').offset().top - 100
                        }, 500);
                    } else {
                        $('#errorText').text(result.message || 'Si è verificato un errore durante la prenotazione.');
                        $('#errorMessage').show();
                        
                        // Shake effect on error
                        $('.prenotazione-container').addClass('shake');
                        setTimeout(function() {
                            $('.prenotazione-container').removeClass('shake');
                        }, 500);
                        
                        // Scroll to error message
                        $('html, body').animate({
                            scrollTop: $('#errorMessage').offset().top - 100
                        }, 500);
                    }
                },
                error: function() {
                    // Ripristina il pulsante
                    $btn.html(originalText).prop('disabled', false);
                    
                    $('#errorText').text('Errore di comunicazione con il server. Riprova più tardi.');
                    $('#errorMessage').show();
                    
                    // Scroll to error message
                    $('html, body').animate({
                        scrollTop: $('#errorMessage').offset().top - 100
                    }, 500);
                }
            });
        });

        // Animazione entrata form sections
        $('.form-section').each(function(index) {
            $(this).css({
                'opacity': '0',
                'transform': 'translateY(20px)'
            });
            
            setTimeout(function() {
                $('.form-section').eq(index).css({
                    'opacity': '1',
                    'transform': 'translateY(0)',
                    'transition': 'all 0.5s ease'
                });
            }, 200 * (index + 1));
        });
    });
  </script>
  
  <style>
    /* Shake animation for errors */
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
    
    .shake {
        animation: shake 0.5s ease;
    }
  </style>
    
  </body>
</html>

