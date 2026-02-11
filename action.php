<?php

include 'librerie/Database.php';
include 'librerie/metodi.php';

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/error.log'); 

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Restituisci uno stato 200 OK per la richiesta preflight
    http_response_code(200);
    exit;
}

$db = new Database();

function sendJsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

$paction=get_param("_action");

switch($paction) 
{	
    
    case "login":
        $username=get_param("_username");
        $password=get_param("_password");
        if ($db->login($username,$password)) {
            echo $db->login($username,$password); 
        } else {
            echo 0; 
        }
       
    break; 

    case "aggiungiProdotto": 
        $idProdotto=get_param("_k");
        if ($db->insertProdottoCarrello($idProdotto)) {
            echo 1;
        } else {
            echo 0;
        }
      
    break;

    case "aggiungiProdotto_menu2": 
        // Legge i dati JSON inviati dal client
        $data = json_decode(file_get_contents("php://input"), true);
    
        // Recupera i dati
        $idProdotto = $data['id_prodotto'];
        $ingredienti = $data['ingredienti']; // Array con gli ingredienti selezionati
    
        // Debug (solo per test)
        // print_r($idProdotto);
        // print_r($ingredienti);
        
        // Inserisci il prodotto nel carrello con gli ingredienti
        if ($db->insertProdottoCarrello_menu2($idProdotto, $ingredienti)) {
            echo 1; // Successo
        } else {
            echo 0; // Errore
        }
    
        break;
    

    case "FillCarrello":
        $risultato = $db->recuperaProdottiCarrello();
        if ($risultato !== "false") {
            echo json_encode([
                'prodotti' => $risultato['prodotti'],
                'totale' => $risultato['totale']
            ]);
        } else {
            echo json_encode([
                'prodotti' => [],
                'totale' => 0
            ]);
        }
        break;
    

    case "eliminaProdotto":
        $id_prodottoCarrello=get_param("_id_prodottiCarrello");
        if ($db->eliminaProdotto($id_prodottoCarrello)) {
            echo 1; 
        } else {
            echo 0; 
        }
       
    break;  

    case "eliminaProdottoMenu":
        $id_prodotto=get_param("id_prodotto");
        if ($db->eliminaProdottoMenu($id_prodotto)) {
            echo 1; 
        } else {
            echo 0; 
        }
       
    break;  

 
    case "incrementa":
        $id_prodottoCarrello=get_param("_id_prodottiCarrello");
        if ($db->incrementa($id_prodottoCarrello)) {
            echo 1; 
        } else {
            echo 0; 
        }
       
    break;  

    case "decrementa":
        $id_prodottoCarrello=get_param("_id_prodottiCarrello");
        if ($db->decrementa($id_prodottoCarrello)) {
            echo 1; 
        } else {
            echo 0; 
        }
       
    break;  
    
    case "ordina":
            $nome = get_param("nome");
            $cognome = get_param("cognome");
            $indirizzo = get_param("indirizzo");
            $telefono = get_param("telefono");
            $email = get_param("email");
            $orarioConsegna = get_param("orarioConsegna");
            $note = get_param("note");
            $deliveryType = get_param("deliveryType");
            $paymentType = get_param("paymentType");
            $risultato_ordine = $db->ordina($nome, $cognome, $indirizzo, $telefono, $email, $orarioConsegna, $note, $deliveryType, $paymentType);
           
            if ($risultato_ordine['success']) {
                sendJsonResponse([
                    'status' => 1,
                    'message' => 'Ordine completato con successo',
                    'data' => [
                        'id_carrello' => $risultato_ordine['id_carrello'],
                        'totale' => $risultato_ordine['totale'],
                        'prodotti' => $risultato_ordine['prodotti']
                    ]
                ]);
            } else {
                sendJsonResponse([
                    'status' => 0,
                    'message' => 'Errore durante l\'elaborazione dell\'ordine'
                ]);
            }
   
    break;

    case "salvaInformazioniPagamento":
        $nome = get_param("nome");
        $cognome = get_param("cognome");
        $indirizzo = get_param("indirizzo");
        $telefono = get_param("telefono");
        $email = get_param("email");
        $orarioConsegna = get_param("orarioConsegna");
        $note = get_param("note");
        $deliveryType = get_param("deliveryType");
        $paymentType = get_param("paymentType");
        $risultato_salvataggio = $db->salvaInformazioniPagamento($nome, $cognome, $indirizzo, $telefono, $email, $orarioConsegna, $note, $deliveryType, $paymentType);

            if ($risultato_salvataggio['success']) {
                sendJsonResponse([
                    'status' => 1,
                    'message' => 'salvataggio completato con successo',
                ]);
            } else {
                sendJsonResponse([
                    'status' => 0,
                    'message' => 'Errore durante l\'elaborazione dell\'ordine'
                ]);
            }
    break;



    case "riepilogo":
        try {
            $risultato_ordine = $db->riepilogo();
            if ($risultato_ordine['success']) {
                sendJsonResponse([
                    'status' => 1,
                    'message' => 'Riepilogo',
                    'data' => [
                        'id_carrello' => $risultato_ordine['id_carrello'],
                        'totale' => $risultato_ordine['totale'],
                        'prodotti' => $risultato_ordine['prodotti']
                    ]
                ]);
            } else {
                sendJsonResponse([
                    'status' => 0,
                    'message' => 'Errore durante l\'elaborazione dell\'ordine'
                ]);
            }
        } catch (Exception $e) {
            error_log("Errore nell'ordine: " . $e->getMessage());
            sendJsonResponse([
                'status' => 0,
                'message' => 'Si è verificato un errore inaspettato'
            ]);
        }
    break;


    case "importoTotale":
        try {
            $totale = $db->importoTotale();
            if ($totale['success']) {
                sendJsonResponse([
                    'status' => 1,
                    'message' => 'totale',
                    'data' => [
                        'totale' => $totale['totale'],
                    ]
                ]);
            } else {
                sendJsonResponse([
                    'status' => 0,
                    'message' => 'Errore durante l\'elaborazione dell\'ordine'
                ]);
            }
        } catch (Exception $e) {
            error_log("Errore nell'ordine: " . $e->getMessage());
            sendJsonResponse([
                'status' => 0,
                'message' => 'Si è verificato un errore inaspettato'
            ]);
        }
    break;

    case "elencoOrdini":
        try {
            $risultato_ordine = $db->elencoOrdini();
            if ($risultato_ordine['success']) {
                sendJsonResponse([
                    'status' => 1,
                    'message' => 'elencoOrdini',
                    'data' => [
                        'elencoOrdini' => $risultato_ordine['elencoOrdini']
                    ]
                ]);
            } else {
                sendJsonResponse([
                    'status' => 0,
                    'message' => 'Errore durante l\'elaborazione dell\'ordine'
                ]);
            }
        } catch (Exception $e) {
            error_log("Errore nell'ordine: " . $e->getMessage());
            sendJsonResponse([
                'status' => 0,
                'message' => 'Si è verificato un errore inaspettato'
            ]);
        }
    break;

    case "elencoOrdiniConfermati":
        try {
            $risultato_ordine = $db->elencoOrdiniConfermati();
            if ($risultato_ordine['success']) {
                sendJsonResponse([
                    'status' => 1,
                    'message' => 'elencoOrdiniConfermati',
                    'data' => [
                        'elencoOrdiniConfermati' => $risultato_ordine['elencoOrdiniConfermati']
                    ]
                ]);
            } else {
                sendJsonResponse([
                    'status' => 0,
                    'message' => 'Errore durante l\'elaborazione dell\'ordine'
                ]);
            }
        } catch (Exception $e) {
            error_log("Errore nell'ordine: " . $e->getMessage());
            sendJsonResponse([
                'status' => 0,
                'message' => 'Si è verificato un errore inaspettato'
            ]);
        }
    break;

    

    
    case "visualizzaDettagli":
        try {
            $id_carrello = get_param("id_carrello");
            $risultato_ordine = $db->visualizzaDettagli($id_carrello);
            
            if ($risultato_ordine['success']) {
                sendJsonResponse([
                    'status' => 1,
                    'message' => 'Dettagli dell\'ordine recuperati con successo',
                    'data' => [
                        'dettaglio' => $risultato_ordine['data'] // Cambiato da 'dettaglio' a 'data'
                    ]
                ]);
            } else {
                sendJsonResponse([
                    'status' => 0,
                    'message' => $risultato_ordine['message'] // Utilizza il messaggio di errore specifico
                ]);
            }
        } catch (Exception $e) {
            error_log("Errore nell'ordine: " . $e->getMessage());
            sendJsonResponse([
                'status' => 0,
                'message' => 'Si è verificato un errore inaspettato'
            ]);
        }
    break;

    case "visualizzaDettagliProdotto":
        try {
            $id = get_param("id");
            $prodotto = $db->visualizzaDettagliProdotto($id);
            
            if ($prodotto['success']) {
                sendJsonResponse([
                    'status' => 1,
                    'message' => 'Dettagli dell\'ordine recuperati con successo',
                    'data' => [
                        'dettaglio' => $prodotto['data'] // Cambiato da 'dettaglio' a 'data'
                    ]
                ]);
            } else {
                sendJsonResponse([
                    'status' => 0,
                    'message' => $prodotto['message'] // Utilizza il messaggio di errore specifico
                ]);
            }
        } catch (Exception $e) {
            error_log("Errore nell'ordine: " . $e->getMessage());
            sendJsonResponse([
                'status' => 0,
                'message' => 'Si è verificato un errore inaspettato'
            ]);
        }
    break;


    case "visualizzaDettagliEvento":
    try {
        $id = get_param("id");
        $evento = $db->visualizzaDettagliEventi($id);
        if ($evento['success']) {
            sendJsonResponse([
                'status' => 1,
                'message' => 'Dettagli dell\'evento recuperati con successo',
                'data' => [
                    'dettaglio' => $evento['data'] // Cambiato da 'dettaglio' a 'data'
                ]
            ]);
        } else {
            sendJsonResponse([
                'status' => 0,
                'message' => $evento['message'] // Utilizza il messaggio di errore specifico
            ]);
        }
    } catch (Exception $e) {
        error_log("Errore nell'evento: " . $e->getMessage());
        sendJsonResponse([
            'status' => 0,
            'message' => 'Si è verificato un errore inaspettato'
        ]);
    }
    break;

    case "visualizzaDettagliUtente":
        try {
            $id_utente = get_param("id_utente");
            $risultato_utente = $db->visualizzaDettagliUtente($id_utente);
            
            if ($risultato_utente['success']) {
                sendJsonResponse([
                    'status' => 1,
                    'message' => 'Dettagli dell\'ordine recuperati con successo',
                    'data' => [
                        'dettaglio' => $risultato_utente['data'] // Cambiato da 'dettaglio' a 'data'
                    ]
                ]);
            } else {
                sendJsonResponse([
                    'status' => 0,
                    'message' => $risultato_utente['message'] // Utilizza il messaggio di errore specifico
                ]);
            }
        } catch (Exception $e) {
            error_log("Errore nell'ordine: " . $e->getMessage());
            sendJsonResponse([
                'status' => 0,
                'message' => 'Si è verificato un errore inaspettato'
            ]);
        }
    break;

    case "visualizzaDettagliConfermato":
        try {
            $id_carrello = get_param("id_carrello");
            $risultato_ordine = $db->visualizzaDettagliConfermato($id_carrello);
            if ($risultato_ordine['success']) {
                sendJsonResponse([
                    'status' => 1,
                    'message' => 'Dettagli dell\'ordine recuperati con successo',
                    'data' => [
                        'dettaglio' => $risultato_ordine['data'] // Cambiato da 'dettaglio' a 'data'
                    ]
                ]);
            } else {
                sendJsonResponse([
                    'status' => 0,
                    'message' => $risultato_ordine['message'] // Utilizza il messaggio di errore specifico
                ]);
            }
        } catch (Exception $e) {
            error_log("Errore nell'ordine: " . $e->getMessage());
            sendJsonResponse([
                'status' => 0,
                'message' => 'Si è verificato un errore inaspettato'
            ]);
        }
    break;


    case "confermaOrdine":
        try {

            $id_carrello = get_param("id_carrello");
            $risultato_ordine = $db->confermaOrdine($id_carrello);
            
            if ($risultato_ordine['success']) {
                sendJsonResponse([
                    'status' => 1,
                    'message' => 'Mail inviata',
                  
                ]);
            } else {
                sendJsonResponse([
                    'status' => 0,
                    'message' => $risultato_ordine['message'] // Utilizza il messaggio di errore specifico
                ]);
            }
        } catch (Exception $e) {
            error_log("Errore nell'ordine: " . $e->getMessage());
            sendJsonResponse([
                'status' => 0,
                'message' => 'Si è verificato un errore inaspettato'
            ]);
        }
    break;

    case "eliminaOrdine":
        try {

            $id_carrello = get_param("id_carrello");
            $risultato_ordine = $db->eliminaOrdine($id_carrello);
            
            if ($risultato_ordine['success']) {
                sendJsonResponse([
                    'status' => 1,
                    'message' => 'ordine eliminato',
                  
                ]);
            } else {
                sendJsonResponse([
                    'status' => 0,
                    'message' => $risultato_ordine['message'] // Utilizza il messaggio di errore specifico
                ]);
            }
        } catch (Exception $e) {
            error_log("Errore nell'ordine: " . $e->getMessage());
            sendJsonResponse([
                'status' => 0,
                'message' => 'Si è verificato un errore inaspettato'
            ]);
        }
    break;


    case "rifiutaOrdine":
        try {

            $id_carrello = get_param("id_carrello");
            $risultato_ordine = $db->rifiutaOrdine($id_carrello);
            
            if ($risultato_ordine['success']) {
                sendJsonResponse([
                    'status' => 1,
                    'message' => 'ordine rifiutato',
                  
                ]);
            } else {
                sendJsonResponse([
                    'status' => 0,
                    'message' => $risultato_ordine['message'] // Utilizza il messaggio di errore specifico
                ]);
            }
        } catch (Exception $e) {
            error_log("Errore nell'ordine: " . $e->getMessage());
            sendJsonResponse([
                'status' => 0,
                'message' => 'Si è verificato un errore inaspettato'
            ]);
        }
    break;

    case "elencoUtenti":
        try {
            $risultato_utenti = $db->elencoUtenti();
            if ($risultato_utenti['success']) {
                sendJsonResponse([
                    'status' => 1,
                    'message' => 'elencoUtenti',
                    'data' => [
                        'elencoUtenti' => $risultato_utenti['elencoUtenti']
                    ]
                ]);
            } else {
                sendJsonResponse([
                    'status' => 0,
                    'message' => 'Errore durante l\'elaborazione dell\'ordine'
                ]);
            }
        } catch (Exception $e) {
            error_log("Errore nell'ordine: " . $e->getMessage());
            sendJsonResponse([
                'status' => 0,
                'message' => 'Si è verificato un errore inaspettato'
            ]);
        }
    break;

    case "numeroProdotti":
        try {
            $risultatoNumeroProdotti = $db->numeroProdotti();
            if ($risultatoNumeroProdotti['success']) {
                sendJsonResponse([
                    'status' => 1,
                    'message' => 'numeroProdotti',
                    'data' => [
                        'numeroProdotti' => $risultatoNumeroProdotti['numeroProdotti']
                    ]
                ]);
            } else {
                sendJsonResponse([
                    'status' => 0,
                    'message' => 'Errore durante l\'elaborazione dell\'ordine'
                ]);
            }
        } catch (Exception $e) {
            error_log("Errore nell'ordine: " . $e->getMessage());
            sendJsonResponse([
                'status' => 0,
                'message' => 'Si è verificato un errore inaspettato'
            ]);
        }
    break;

    case "numeroConfermare":
        try {
            $numeroConfermare = $db->numeroConfermare();
            $numeroCarrelli = $numeroConfermare['numero'][0]['COUNT(c.id_carrello)'];
            if ($numeroConfermare['success']) {
                sendJsonResponse([
                    'status' => 1,
                    'message' => 'elencoUtenti',
                    'data' => [
                        'numero' => $numeroCarrelli
                    ]
                ]);
            } else {
                sendJsonResponse([
                    'status' => 0,
                    'message' => 'Errore durante l\'elaborazione dell\'ordine'
                ]);
            }
        } catch (Exception $e) {
            error_log("Errore nell'ordine: " . $e->getMessage());
            sendJsonResponse([
                'status' => 0,
                'message' => 'Si è verificato un errore inaspettato'
            ]);
        }
    break;

    case "verifica":
        try {
            // Recupera il token dai parametri
            $token = get_param("token");
    
            // Stampa il token per il debug (puoi rimuovere questa parte)
    
            // Verifica il token utilizzando la funzione verifyToken
            $id_utente = $db->verifyToken($token);
    
            // Controlla se la verifica è andata a buon fine
            if ($id_utente['success']) {
                // Risposta JSON con successo
                sendJsonResponse([
                    'status' => 1,
                    'message' => 'elencoUtenti',
                    'data' => [
                        'id_utente' => $id_utente['id_utente']
                    ]
                ]);
            } else {
                // Risposta JSON in caso di errore di verifica
                sendJsonResponse([
                    'status' => 0,
                    'message' => 'Errore durante l\'elaborazione dell\'ordine: Token non valido o scaduto'
                ]);
            }
        } catch (Exception $e) {
            // Logga l'errore nel file di log e restituisci un messaggio di errore
            error_log("Errore nell'ordine: " . $e->getMessage());
    
            sendJsonResponse([
                'status' => 0,
                'message' => 'Si è verificato un errore inaspettato'
            ]);
        }
        break;

        case "caricaImmagine":
            if (isset($_POST['immagine']) && isset($_POST['nomeFile'])) {
                // Decodifica la stringa base64
                $data = $_POST['immagine'];
        
                // Trova la parte base64 dell'immagine
                list($type, $data) = explode(';', $data);
                list(, $data)      = explode(',', $data);
                $data = base64_decode($data);
                
                // Verifica se la decodifica ha avuto successo
                if ($data === false) {
                    echo json_encode(['status' => 0, 'message' => 'Immagine non valida.']);
                    exit;
                }
        
                // Definisci il percorso dove salvare l'immagine
                $uploadDir = 'images/'; // Sostituisci con il tuo percorso
                $fileName = $_POST['nomeFile'];
                $filePath = $uploadDir . basename($fileName);
        
                // Salva il file
                if (file_put_contents($filePath, $data)) {
                    echo json_encode(['status' => 1, 'nomeImmagine' => $fileName]);
                } else {
                    echo json_encode(['status' => 0, 'message' => 'Errore nel salvataggio dell\'immagine.']);
                }
            } else {
                echo json_encode(['status' => 0, 'message' => 'Dati mancanti.']);
            }
            break;
            
            case "caricaImmagineEvento":
                if (isset($_FILES['immagine']) && isset($_POST['nomeFile'])) {
                    $uploadDir = 'images/';
                    $fileName = basename($_FILES['immagine']['name']);
                    $filePath = $uploadDir . $fileName;
            
                    if (move_uploaded_file($_FILES['immagine']['tmp_name'], $filePath)) {
                        echo json_encode(['status' => 1, 'nomeImmagine' => $fileName]);
                    } else {
                        echo json_encode(['status' => 0, 'message' => 'Errore nel salvataggio dell\'immagine.']);
                    }
                } else {
                    echo json_encode(['status' => 0, 'message' => 'Dati mancanti.']);
                }
            break;
            
            case "salvaDatiProdotto":
                $idprodotto = get_param("id");
                $titolo = get_param("titolo");
                $descrizione = get_param("descrizione");
                $prezzo = get_param("prezzo");
                $categoria = get_param("categoria");
                $nomeImmagine = get_param("immagine"); // Aggiunto per l'immagine
                // Presumendo che la tua funzione di salvataggio richieda questi parametri
                $risultato_salvataggio = $db->salvaProdotto( $idprodotto,$titolo, $descrizione, $prezzo, $nomeImmagine, $categoria);
                if ($risultato_salvataggio['success']) {
                    sendJsonResponse([
                        'status' => 1,
                        'message' => 'Prodotto salvato con successo',
                        'data' => [
                            'id_prodotto' => $risultato_salvataggio['id_prodotto'],
                            // Aggiungi ulteriori dati se necessario
                        ]
                    ]);
                } else {
                    sendJsonResponse([
                        'status' => 0,
                        'message' => 'Errore durante il salvataggio del prodotto'
                    ]);
                }
            
       
            break;

            
            case "salvaCategoria":
                $descrizione = get_param("descrizione");               
                $risultato_salvataggio = $db->salvaCategoria($descrizione);
                if ($risultato_salvataggio['success']) {
                    sendJsonResponse([
                        'status' => 1,
                        'message' => 'categoria aggiunta con successo',
                    ]);
                } else {
                    sendJsonResponse([
                        'status' => 0,
                        'message' => 'Errore durante il salvataggio del prodotto'
                    ]);
                }
            break;
            

            case "salvaDatiEvento":
                $idevento = get_param("id");
                $titolo = get_param("titolo");
                $descrizione = get_param("descrizione");
                $data_evento = get_param("data_evento");
                $nome_invitato = get_param("nome_invitato");
                $nomeImmagine = get_param("immagine");
            
                // Presumendo che la tua funzione di salvataggio richieda questi parametri
                $risultato_salvataggio = $db->salvaEvento(
                    $idevento,
                    $titolo,
                    $descrizione,
                    $data_evento,
                    $nome_invitato,
                    $nomeImmagine
                );
            
                if ($risultato_salvataggio['success']) {
                    sendJsonResponse([
                        'status' => 1,
                        'message' => 'Evento salvato con successo',
                        'data' => [
                            'id_evento' => $risultato_salvataggio['id_evento'],
                            // Aggiungi ulteriori dati se necessario
                        ]
                    ]);
                } else {
                    sendJsonResponse([
                        'status' => 0,
                        'message' => 'Errore durante il salvataggio dell\'evento'
                    ]);
                }
                break;


                case "Paga":
                    require_once 'librerie/Stripe.php';
                    
                    $payment = new StripePaymentHandler();

                    $id_carrello = $db->esisteCarrello();

                    $sql = "SELECT *, carrello.id_carrello from prodotticarrello
                                    INNER join carrello on prodotticarrello.id_carrello = carrello.id_carrello
                                    INNER JOIN prodotto ON prodotticarrello.id_prodotto=prodotto.id 
                                    WHERE carrello.flag_ordinato IS NULL and prodotticarrello.id_carrello= ".$id_carrello."";

                        $result = $db->conn->query($sql);

                        if ($result->num_rows > 0) {
                            $totale = 0;
                            while($row = $result->fetch_assoc()) {
                                $totale += $row['numero_prodotti'] * $row['prezzo'];
                            }

                            try {
                                $paymentIntent = $payment->createPayment(
                                    amount: $totale,
                                    currency: 'eur',
                                    description: $row['id_carrello']
                                );
                                
                                echo json_encode(['client_secret' => $paymentIntent->client_secret]);                                

                            } catch (Exception $e) {
                                http_response_code(500);
                                echo json_encode(['error' => $e->getMessage()]);
                            }
                        } else {
                            return "false";
                        }

                break;

    case "prenotaTavolo":
        try {
            $nome = get_param("nome");
            $cognome = get_param("cognome");
            $telefono = get_param("telefono");
            $email = get_param("email");
            $data = get_param("data");
            $ora = get_param("ora");
            $numero_persone = get_param("numero_persone");
            $note = get_param("note");
            
            // Validazione dei campi obbligatori
            if (empty($nome) || empty($cognome) || empty($telefono) || empty($email) || empty($data) || empty($ora) || empty($numero_persone)) {
                sendJsonResponse([
                    'status' => 0,
                    'message' => 'Tutti i campi obbligatori devono essere compilati'
                ]);
                break;
            }
            
            // Validazione formato email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                sendJsonResponse([
                    'status' => 0,
                    'message' => 'Formato email non valido'
                ]);
                break;
            }
            
            // Recupera l'id_utente dalla sessione
            session_start();
            $id_utente = isset($_SESSION['id_utente']) ? $_SESSION['id_utente'] : null;
            
            // Inserisce la prenotazione nel database
            $sql = "INSERT INTO prenotazioni (nome, cognome, telefono, email, data_prenotazione, ora_prenotazione, numero_persone, note, confermato, id_utente) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?)";
            
            $stmt = $db->conn->prepare($sql);
            $stmt->bind_param("ssssssiss", $nome, $cognome, $telefono, $email, $data, $ora, $numero_persone, $note, $id_utente);
            
            if ($stmt->execute()) {
                $id_prenotazione = $stmt->insert_id;
                
                sendJsonResponse([
                    'status' => 1,
                    'message' => 'Prenotazione ricevuta con successo! Ti contatteremo per confermare.',
                    'data' => [
                        'id_prenotazione' => $id_prenotazione
                    ]
                ]);
            } else {
                sendJsonResponse([
                    'status' => 0,
                    'message' => 'Errore durante il salvataggio della prenotazione'
                ]);
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            error_log("Errore nella prenotazione: " . $e->getMessage());
            sendJsonResponse([
                'status' => 0,
                'message' => 'Si è verificato un errore inaspettato'
            ]);
        }
    break;

    case "elencoPrenotazioni":
        try {
            $sql = "SELECT * FROM prenotazioni WHERE confermato = 0 ORDER BY data_prenotazione ASC, ora_prenotazione ASC";
            $result = $db->conn->query($sql);
            
            $prenotazioni = [];
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $prenotazioni[] = $row;
                }
            }
            
            sendJsonResponse([
                'status' => 1,
                'message' => 'Elenco prenotazioni',
                'data' => [
                    'elencoPrenotazioni' => $prenotazioni
                ]
            ]);
        } catch (Exception $e) {
            error_log("Errore nel recupero prenotazioni: " . $e->getMessage());
            sendJsonResponse([
                'status' => 0,
                'message' => 'Si è verificato un errore inaspettato'
            ]);
        }
    break;

    case "elencoPrenotazioniConfermate":
        try {
            $sql = "SELECT * FROM prenotazioni WHERE confermato = 1 ORDER BY data_prenotazione DESC, ora_prenotazione DESC";
            $result = $db->conn->query($sql);
            
            $prenotazioni = [];
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $prenotazioni[] = $row;
                }
            }
            
            sendJsonResponse([
                'status' => 1,
                'message' => 'Elenco prenotazioni confermate',
                'data' => [
                    'elencoPrenotazioniConfermate' => $prenotazioni
                ]
            ]);
        } catch (Exception $e) {
            error_log("Errore nel recupero prenotazioni: " . $e->getMessage());
            sendJsonResponse([
                'status' => 0,
                'message' => 'Si è verificato un errore inaspettato'
            ]);
        }
    break;

    case "visualizzaDettagliPrenotazione":
        try {
            $id_prenotazione = get_param("id_prenotazione");
            
            $sql = "SELECT * FROM prenotazioni WHERE id_prenotazione = ?";
            $stmt = $db->conn->prepare($sql);
            $stmt->bind_param("i", $id_prenotazione);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $prenotazione = $result->fetch_assoc();
                sendJsonResponse([
                    'status' => 1,
                    'message' => 'Dettagli prenotazione',
                    'data' => [
                        'dettaglio' => $prenotazione
                    ]
                ]);
            } else {
                sendJsonResponse([
                    'status' => 0,
                    'message' => 'Prenotazione non trovata'
                ]);
            }
            
            $stmt->close();
        } catch (Exception $e) {
            error_log("Errore nel recupero dettagli: " . $e->getMessage());
            sendJsonResponse([
                'status' => 0,
                'message' => 'Si è verificato un errore inaspettato'
            ]);
        }
    break;

    case "confermaPrenotazione":
        try {
            $id_prenotazione = get_param("id_prenotazione");
            
            $sql = "UPDATE prenotazioni SET confermato = 1, data_modifica = CURRENT_TIMESTAMP WHERE id_prenotazione = ?";
            $stmt = $db->conn->prepare($sql);
            $stmt->bind_param("i", $id_prenotazione);
            
            if ($stmt->execute()) {
                sendJsonResponse([
                    'status' => 1,
                    'message' => 'Prenotazione confermata con successo'
                ]);
            } else {
                sendJsonResponse([
                    'status' => 0,
                    'message' => 'Errore nella conferma della prenotazione'
                ]);
            }
            
            $stmt->close();
        } catch (Exception $e) {
            error_log("Errore nella conferma: " . $e->getMessage());
            sendJsonResponse([
                'status' => 0,
                'message' => 'Si è verificato un errore inaspettato'
            ]);
        }
    break;

    case "eliminaPrenotazione":
        try {
            $id_prenotazione = get_param("id_prenotazione");
            
            $sql = "DELETE FROM prenotazioni WHERE id_prenotazione = ?";
            $stmt = $db->conn->prepare($sql);
            $stmt->bind_param("i", $id_prenotazione);
            
            if ($stmt->execute()) {
                sendJsonResponse([
                    'status' => 1,
                    'message' => 'Prenotazione eliminata con successo'
                ]);
            } else {
                sendJsonResponse([
                    'status' => 0,
                    'message' => 'Errore nell\'eliminazione della prenotazione'
                ]);
            }
            
            $stmt->close();
        } catch (Exception $e) {
            error_log("Errore nell'eliminazione: " . $e->getMessage());
            sendJsonResponse([
                'status' => 0,
                'message' => 'Si è verificato un errore inaspettato'
            ]);
        }
    break;

    case "get_cards_html":
        $categoria = get_param("categoria");
        $data = $db->getCardsHtml($categoria);
        sendJsonResponse([
            'status' => 1,
            'message' => 'Card prodotti',
            'data' => [
                'html' => $data['html'],
                'query' => $data['query']
            ]
        ]);
    break;

    case "numeroPrenotazioniDaConfermare":
        try {
            $sql = "SELECT COUNT(*) as numero FROM prenotazioni WHERE confermato = 0";
            $result = $db->conn->query($sql);
            $row = $result->fetch_assoc();
            
            sendJsonResponse([
                'status' => 1,
                'message' => 'Numero prenotazioni da confermare',
                'data' => [
                    'numero' => $row['numero']
                ]
            ]);
        } catch (Exception $e) {
            error_log("Errore nel conteggio: " . $e->getMessage());
            sendJsonResponse([
                'status' => 0,
                'message' => 'Si è verificato un errore inaspettato'
            ]);
        }
    break;

    case "getDeliveryStatus":
        try {
            // Crea la tabella configurazioni se non esiste
            $createTable = "CREATE TABLE IF NOT EXISTS configurazioni (
                id INT AUTO_INCREMENT PRIMARY KEY,
                chiave VARCHAR(100) UNIQUE NOT NULL,
                valore TEXT,
                data_modifica TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            $db->conn->query($createTable);
            
            // Verifica se esiste il record delivery_disabilitato
            $sql = "SELECT valore FROM configurazioni WHERE chiave = 'delivery_disabilitato'";
            $result = $db->conn->query($sql);
            
            if ($result->num_rows == 0) {
                // Inserisci il valore di default (consegne attive = 0)
                $insertSql = "INSERT INTO configurazioni (chiave, valore) VALUES ('delivery_disabilitato', '0')";
                $db->conn->query($insertSql);
                $deliveryDisabilitato = '0';
            } else {
                $row = $result->fetch_assoc();
                $deliveryDisabilitato = $row['valore'];
            }
            
            // Recupera anche il messaggio personalizzato
            $sqlMsg = "SELECT valore FROM configurazioni WHERE chiave = 'delivery_messaggio'";
            $resultMsg = $db->conn->query($sqlMsg);
            
            if ($resultMsg->num_rows == 0) {
                $defaultMsg = "Le consegne a domicilio sono temporaneamente sospese. È possibile effettuare ordini solo con ritiro in sede (Asporto).";
                $insertMsgSql = "INSERT INTO configurazioni (chiave, valore) VALUES ('delivery_messaggio', ?)";
                $stmt = $db->conn->prepare($insertMsgSql);
                $stmt->bind_param("s", $defaultMsg);
                $stmt->execute();
                $stmt->close();
                $messaggio = $defaultMsg;
            } else {
                $rowMsg = $resultMsg->fetch_assoc();
                $messaggio = $rowMsg['valore'];
            }
            
            sendJsonResponse([
                'status' => 1,
                'message' => 'Stato delivery recuperato',
                'data' => [
                    'delivery_disabilitato' => $deliveryDisabilitato,
                    'messaggio' => $messaggio
                ]
            ]);
        } catch (Exception $e) {
            error_log("Errore nel recupero stato delivery: " . $e->getMessage());
            sendJsonResponse([
                'status' => 0,
                'message' => 'Si è verificato un errore inaspettato'
            ]);
        }
    break;

    case "toggleDelivery":
        try {
            $stato = get_param("stato"); // 1 = disabilitato, 0 = abilitato
            
            $sql = "UPDATE configurazioni SET valore = ? WHERE chiave = 'delivery_disabilitato'";
            $stmt = $db->conn->prepare($sql);
            $stmt->bind_param("s", $stato);
            
            if ($stmt->execute()) {
                sendJsonResponse([
                    'status' => 1,
                    'message' => $stato == '1' ? 'Consegne disabilitate' : 'Consegne abilitate'
                ]);
            } else {
                sendJsonResponse([
                    'status' => 0,
                    'message' => 'Errore nell\'aggiornamento dello stato'
                ]);
            }
            
            $stmt->close();
        } catch (Exception $e) {
            error_log("Errore nel toggle delivery: " . $e->getMessage());
            sendJsonResponse([
                'status' => 0,
                'message' => 'Si è verificato un errore inaspettato'
            ]);
        }
    break;

    case "updateDeliveryMessage":
        try {
            $messaggio = get_param("messaggio");
            
            $sql = "UPDATE configurazioni SET valore = ? WHERE chiave = 'delivery_messaggio'";
            $stmt = $db->conn->prepare($sql);
            $stmt->bind_param("s", $messaggio);
            
            if ($stmt->execute()) {
                sendJsonResponse([
                    'status' => 1,
                    'message' => 'Messaggio aggiornato con successo'
                ]);
            } else {
                sendJsonResponse([
                    'status' => 0,
                    'message' => 'Errore nell\'aggiornamento del messaggio'
                ]);
            }
            
            $stmt->close();
        } catch (Exception $e) {
            error_log("Errore nell\'aggiornamento messaggio: " . $e->getMessage());
            sendJsonResponse([
                'status' => 0,
                'message' => 'Si è verificato un errore inaspettato'
            ]);
        }
    break;
    
    
    
}   
?>