<?php

include 'librerie/Database.php';
include 'librerie/metodi.php';
require_once 'vendor/autoload.php';
$db = new Database();

// Set Stripe API Key
\Stripe\Stripe::setApiKey('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');

// Initialize variables
$paymentSuccess = false;
$errorMessage = '';
$paymentDetails = [];

$id_carrello = $db->esisteCarrello();

try {
    // Check if payment_intent is present in the URL
    if (!isset($_GET['payment_intent'])) {
        throw new Exception("No payment intent found.");
    }

    $paymentIntentId = $_GET['payment_intent'];

    // Retrieve payment intent from Stripe
    $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

    // Check payment status
    switch ($paymentIntent->status) {
        case 'succeeded':
            $paymentSuccess = true;
            $paymentDetails = [
                'amount' => number_format($paymentIntent->amount / 100, 2),
                'currency' => strtoupper($paymentIntent->currency),
                'transaction_id' => $paymentIntent->id,
                'payment_method' => $paymentIntent->payment_method_types[0],
                'timestamp' => date('Y-m-d H:i:s', $paymentIntent->created)
            ];

            // TODO: Add your post-payment processing here
            // Examples:
            // - Update order status in database
            // - Send confirmation email
            // - Log transaction
            break;

        case 'requires_payment_method':
            $errorMessage = "Payment failed. Please try again.";
            break;

        default:
            $errorMessage = "Payment status: " . $paymentIntent->status;
            break;
    }

    if($paymentSuccess){
        $data_odierna = date('Y-m-d');


        $sql = "UPDATE carrello 
        SET flag_ordinato = 1,  
            data_ordinazione = '$data_odierna'
        WHERE id_carrello = '$id_carrello'";  

        echo "sono dentro";
        $update_success = $db->conn->query($sql) === TRUE;
    }

} catch (Exception $e) {
    $errorMessage = $e->getMessage();
    // Log the error for admin review
    error_log("Payment Confirmation Error: " . $errorMessage);
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conferma Pagamento</title>
   <!-- Bootstrap CSS -->
   <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .step {
            display: none;
        }
        .step.active {
            display: block;
        }
        .form-container {
            max-width: 500px;
            margin: auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            color: #000;
        }
        .buttons {
            margin-top: 20px;
        }
        .form-control.is-invalid {
            border-color: red;
        }
        * {
            color: #000 !important;
        }
        /* Sovrascrivi il colore del testo e del background per i textarea con la classe form-control */
        .form-group textarea.form-control, select.form-control {
            color: #000 !important;
            background-color: #fff !important;
        }

        /* Anche i placeholder del textarea */
        textarea.form-control::placeholder, select.form-control::placeholder {
            color: #000 !important;
            opacity: 1;
        }
        /* Stile per allineare l'icona accanto al testo */
        .title-with-icon {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .title-with-icon h1 {
            margin-right: 10px; /* Spazio tra il testo e l'icona */
        }
        .table th {
            background-color: #f0f0f0;  /* Light gray background for table headers */
            color: #333;  /* Dark gray text for better readability */
        }
    </style>
</head>
<body>

<div class="container mt-5 text-white-custom">
        <div class="form-container text-center">
            <!-- Titolo con icona allineata a destra -->
            <div class="title-with-icon">
                <h2 class="mt-0">Pagamento completato</h2>
                <i class="bi bi-check-circle-fill text-success" style="font-size: 2.5rem;"></i>
            </div>
            <div class="row ml-5">
                <div class="col-md-8">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <tbody>
                                <tr>
                                    <th scope="row">Importo</th>
                                    <td>€<?= $paymentDetails['amount'] ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">ID Transazione</th>
                                    <td><?= $paymentDetails['transaction_id'] ?></td>
                                </tr>
                                <tr>
                                    <?php if($paymentDetails['payment_method']  == "card")
                                    {
                                        $tipopagamento="carta";
                                    }  
                                    ?>
                                    <th scope="row">Metodo di Pagamento</th>
                                    <td><?= $tipopagamento ?></td>
                                </tr>
                                <!-- <tr>
                                    <th scope="row">Data</th>
                                    <td><?= $paymentDetails['timestamp'] ?></td>
                                </tr> -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <p>A breve riceverai un email di conferma con i dettagli dell'ordine!</p>
            <form >
                <div class="buttons text-center">
                    <a type="button" class="btn btn-primary" href="menu.php" id="nextBtn" >Torna al menu</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>