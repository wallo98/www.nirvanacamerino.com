<?php
class Database {
    private $servername = "31.11.39.179";
    private $username = "Sql1819808";
    private $password = "Safinirvana@2024";
    private $dbname = "Sql1819808_1";
    public $conn;
    private $secretKey;
    
    public function __construct() {
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        
        if ($this->conn->connect_error) {
            die("Connessione fallita: " . $this->conn->connect_error);
        }
        
    }
    

    
    private function getSecretKey() {
        $secret_key=("SELECT secret_key FROM secret_keys WHERE is_active=true");
        if ($secret_key) {
            return $secret_key;
        }
        throw new Exception("No active secret key found");
    }
    
    

    public function closeConnection() {
        $this->conn->close();
    }


    public function registrati($userId, $password) {
        // Esegui l'hashing della password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
        // Prepara la query SQL utilizzando i prepared statements
        $sql = "INSERT INTO utenti (nome, password) VALUES (?, ?)";
    
        // Prepara la query
        $stmt = $this->conn->prepare($sql);
    
        if ($stmt === false) {
            throw new Exception("Errore nella preparazione della query: " . $this->conn->error);
        }
    
        // Lega i parametri alla query (user_id e hashedPassword)
        $stmt->bind_param("ss", $userId, $hashedPassword);
    
        // Esegue la query
        $stmt->execute();
    
        if ($stmt->affected_rows === 0) {
            throw new Exception("Inserimento fallito");
        }
    
        // Chiudi lo statement
        $stmt->close();
    }
    


    public function createSecretKey($keyName) {
        // Genera una chiave segreta casuale
        $secretKey = bin2hex(random_bytes(32)); // Genera una stringa esadecimale di 64 caratteri (256 bit)
    
        // Prepara la query SQL
        $sql = "INSERT INTO secret_keys (key_name, secret_key) VALUES (?, ?)";
        
        // Prepara lo statement
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt === false) {
            throw new Exception("Errore nella preparazione della query: " . $this->conn->error);
        }
        
        // Collega i parametri
        $stmt->bind_param("ss", $keyName, $secretKey);
        
        // Esegue lo statement
        if (!$stmt->execute()) {
            throw new Exception("Errore nell'inserimento della chiave: " . $stmt->error);
        }
        
        // Chiude lo statement
        $stmt->close();
        
        return $secretKey;
    }

    public function login($username, $password) {
        $username = $this->conn->real_escape_string($username);
        $password = $this->conn->real_escape_string($password);

        $sql = "SELECT id_utente, password FROM utenti WHERE nome = '$username'";
        $result = $this->conn->query($sql);

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if ($password==$user['password']) {
                $token = $this->generateToken($user['id_utente']);
                $this->saveToken($user['id_utente'], $token);
                return $token;
            }else{
               
            }
        }
        else{
           
        }
        return false;
    }


    private function generateToken($user_id) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'user_id' => $user_id,
            'exp' => time() + 3600 
        ]);
        $this->secretKey=$this->getSecretKey(); 
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secretKey, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }



    private function saveToken($user_id, $token) {
        $user_id = $this->conn->real_escape_string($user_id);
        $token = $this->conn->real_escape_string($token);
        $expires = date('Y-m-d H:i:s', time() + 36000);

        $sql = "INSERT INTO utenti_tokens (id_utente, token, scadenza) VALUES ('$user_id', '$token', '$expires')";
        $this->conn->query($sql);
    }

    public function verifyToken($token) {
        // Prevenzione SQL injection
        $token = $this->conn->real_escape_string($token);
    
        // Query per verificare il token con la scadenza
        $sql = "SELECT id_utente FROM utenti_tokens WHERE token = '$token' AND scadenza > NOW()";
        $result = $this->conn->query($sql);
    
        // Se viene trovato un risultato
        if ($result->num_rows == 1) {
            // Decodifica il payload del token
            $tokenParts = explode('.', $token);
            $payload = json_decode(base64_decode($tokenParts[1]), true);
    
            // Verifica che il payload contenga l'user_id
            if (isset($payload['user_id'])) {
                return [
                    'success' => true,
                    'id_utente' => $payload['user_id']
                ];
            }
        }
    
        // Se il token non è valido o non è trovato
        return ['success' => false];
    }
    

    public function logout($token) {
        $token = $this->conn->real_escape_string($token);
        $sql = "DELETE FROM utenti_tokens WHERE token = '$token'";
        $this->conn->query($sql);
    }


    public function insertProdottoCarrello($id) {

       $id_carrello = $this->controlloCarrello();
        $id = $this->conn->real_escape_string($id);
        $prezzo=get_db_value("SELECT prezzo FROM prodotto WHERE id=$id");
       
        $sql = "INSERT INTO prodotticarrello (id_carrello, id_prodotto, prezzo,numero_prodotti) VALUES ($id_carrello, '$id','$prezzo',1)";

        if ($this->conn->query($sql) === TRUE) {
            return true;
        } else {
            return false;
        }
    }


    public function insertProdottoCarrello_menu2($id, $ingredienti) {
        
        $id_carrello = $this->controlloCarrello();
        $id = $this->conn->real_escape_string($id);
        $prezzo = get_db_value("SELECT prezzo FROM prodotto WHERE id=$id");
    
        // Inserisce il prodotto nel carrello
        $sql = "INSERT INTO prodotticarrello (id_carrello, id_prodotto, prezzo, numero_prodotti) VALUES ($id_carrello, '$id', '$prezzo', 1)";
    
        if ($this->conn->query($sql) === TRUE) {
            $idProdottoCarrello = $this->conn->insert_id; // Ottieni l'ID dell'inserimento
    
            // Inserisci gli ingredienti associati
            foreach ($ingredienti as $ingrediente) {
                $ingrediente = $this->conn->real_escape_string($ingrediente);
                $sqlIngredienti = "INSERT INTO IngredientiKebab (id_prodottiCarrello, descrizione) VALUES ('$idProdottoCarrello', '$ingrediente')";
                
                // Controlla se la query ha successo
                if (!$this->conn->query($sqlIngredienti)) {
                    echo "Errore durante l'inserimento dell'ingrediente: " . $this->conn->error . "<br>";
                }
            }
    
            return true;
        } else {
            echo "Errore nell'inserimento del prodotto: " . $this->conn->error;
            return false;
        }
    }
    


    public function recuperaProdottiCarrello() {
        $id_carrello = $this->controlloCarrello(); 

        $sql = "SELECT * from prodotticarrello
                        INNER join carrello on prodotticarrello.id_carrello = carrello.id_carrello
                        INNER JOIN prodotto ON prodotticarrello.id_prodotto=prodotto.id 
                        WHERE carrello.flag_ordinato IS NULL and prodotticarrello.id_carrello= ".$id_carrello."";

        $result = $this->conn->query($sql);

        if ($result->num_rows > 0) {
            $prodotti = [];
            $totale = 0;
            while($row = $result->fetch_assoc()) {
                $totale += $row['numero_prodotti'] * $row['prezzo'];
                $prodotti[] = $row;
            }
            return ['prodotti' => $prodotti, 'totale' => $totale];
        } else {
            return "false";
        }
    }


    public function eliminaProdotto($id) {
        $id_carrello = $this->controlloCarrello(); 

        $sql = "DELETE FROM prodotticarrello WHERE id_carrello=$id_carrello AND id_prodottiCarrello=$id";

        if ($this->conn->query($sql) === TRUE) {
            return true;
        } else {
            return false;
        }
    }

    public function eliminaProdottoMenu($id) {

        $sql = "DELETE FROM prodotto WHERE id=$id ;";

        if ($this->conn->query($sql) === TRUE) {
            return true;
        } else {
            return false;
        }
    }

    public function incrementa($id) {
        $id_carrello = $this->controlloCarrello(); 
       $numero_prodotto = get_db_value("SELECT numero_prodotti FROM prodotticarrello WHERE id_carrello = '$id_carrello' AND id_prodottiCarrello = '$id'");
        $numero_prodotto++;
        $sql = "UPDATE prodotticarrello 
                SET numero_prodotti = $numero_prodotto
                WHERE id_carrello = '$id_carrello' AND id_prodottiCarrello = '$id'";
        
        if ($this->conn->query($sql) === TRUE) {
            return $numero_prodotto;
        } else {
            return false;
        }
    }

    public function decrementa($id) {
        $id_carrello = $this->controlloCarrello();
        $numero_prodotto = get_db_value("SELECT numero_prodotti FROM prodotticarrello WHERE id_carrello = '$id_carrello' AND id_prodottiCarrello = $id");

        $numero_prodotto--;

        if ($numero_prodotto < 0) {
            $numero_prodotto = 0;
        }

        $sql = "UPDATE prodotticarrello 
                SET numero_prodotti = $numero_prodotto
                WHERE id_carrello = '$id_carrello' AND id_prodottiCarrello = $id";
        
        if ($this->conn->query($sql) === TRUE) {
            return true;
        } else {
           error_log("Errore nella query di aggiornamento: " . $this->conn->error);
        return false;
        }
    }


    /**
     * Ottiene l'IP reale dell'utente, gestendo anche i proxy
     * @return string IP dell'utente
     */
    private function getIpAddress() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }

    private function controlloCarrello() {
        session_start();    

        $id_utente = $_SESSION['id_utente'];
        $ip_macchina = $this->getIpAddress();
        $id_carrello=get_db_value("SELECT id_carrello FROM carrello WHERE id_utente= '$id_utente' AND flag_ordinato IS NULL");

        if(empty($id_carrello))
        {
            $ip_macchina_escaped = $this->conn->real_escape_string($ip_macchina);
            $sql = "INSERT INTO carrello ( id_utente, contoTotale, flag_eliminato, flag_rifiutato, data_creazione, ip_macchina) VALUES ('$id_utente',0, 0, 0, NOW(), '$ip_macchina_escaped')";         

            if ($this->conn->query($sql) === TRUE) {
                return $id_carrello=get_db_value("SELECT id_carrello FROM carrello WHERE id_utente= '$id_utente' AND flag_ordinato IS NULL");
            } else {
                return false;
            }
        } else {
            $ip_macchina_escaped = $this->conn->real_escape_string($ip_macchina);
            $sql = "UPDATE carrello SET ip_macchina = '$ip_macchina_escaped' WHERE id_carrello = '$id_carrello'";
            $this->conn->query($sql);
        }
        return $id_carrello;
    }

    
    public function esisteCarrello() {
        session_start();    

        $id_utente = $_SESSION['id_utente'];
        $id_carrello=get_db_value("SELECT id_carrello FROM carrello WHERE id_utente= '$id_utente' AND flag_ordinato IS NULL");
        if(empty($id_carrello)){
            return false;
        }
    
        return $id_carrello;
    }

    public function ordina($nome, $cognome, $indirizzo, $telefono, $email, $orarioConsegna, $note, $deliveryType, $paymentType) {
        $id_carrello = $this->esisteCarrello();
        $data_odierna = date('Y-m-d');
        $ora_corrente = date('H:i:s'); // Ottiene l'ora corrente con i secondi inclusi
        if ($id_carrello) {
            // Recupera i prodotti nel carrello
            $ordine = get_data("SELECT id_prodottiCarrello, numero_prodotti, prezzo, id_prodotto FROM prodotticarrello WHERE id_carrello='$id_carrello'");
            
            $totale = 0;
            $prodotti_ordinati = [];
            
            foreach ($ordine as $item) {
                if (is_array($item)) {
                    $subtotale = $item['numero_prodotti'] * $item['prezzo'];
                    $totale += $subtotale;
                    $nomeProdotto = get_db_value("SELECT titolo FROM prodotto WHERE id='" . $item['id_prodotto'] . "'");
                    $prodotti_ordinati[] = [
                        'id_prodotto_carrello' => $item['id_prodottiCarrello'],
                        'nomeProdotto' => $nomeProdotto,
                        'quantita' => $item['numero_prodotti'],
                        'prezzo_unitario' => $item['prezzo'],
                        'subtotale' => $subtotale
                    ];
                } else {
                    error_log("Elemento non valido nell'ordine: " . print_r($item, true));
                }
            }
            
            // Converti il tipo di consegna in un valore numerico per il database (1 = Delivery, 2 = Asporto)
            $tipologia = ($deliveryType === 'Delivery') ? 1 : 2;

            // Inserisci i dettagli dell'ordine nella tabella `carrello_dettaglio`
            if ($this->inserisciDettagliOrdine($id_carrello, $tipologia, $orarioConsegna, $note, $paymentType)) {
                // Salva le informazioni dell'utente
                if ($this->salvaInformazioniUtente($nome, $cognome, $email, $indirizzo, $telefono, $id_carrello)) {
                    // Aggiorna il flag "ordinato" per il carrello
                    $sql = "UPDATE carrello 
                    SET flag_ordinato = 1,  
                        data_ordinazione = '$data_odierna',
                        orario_ordinazione = '$ora_corrente'
                    WHERE id_carrello = '$id_carrello'";
            
                    

                    
                    $update_success = $this->conn->query($sql) === TRUE;
    
                    // Invia email con i dettagli dell'ordine
                    $invioMail = $this->inviaEmailOrdine($nome, $cognome, $indirizzo, $telefono, $email, $orarioConsegna, $note, $deliveryType, $prodotti_ordinati, $totale);
                    

                    if ($invioMail) {
                        return [
                            'success' => $update_success,
                            'id_carrello' => $id_carrello,
                            'prodotti' => $prodotti_ordinati,
                            'totale' => $totale
                        ];
                    } else {
                        return [
                            'success' => 0,
                        ];
                    }
                } else {
                    return [
                        'success' => 0,
                        'message' => 'Errore durante il salvataggio delle informazioni utente.'
                    ];
                }
            } else {
                return [
                    'success' => 0,
                    'message' => 'Errore durante l\'inserimento dei dettagli dell\'ordine.'
                ];
            }
        } else {
            return [
                'success' => 0,
            ];
        }
    }
    

    public function salvaInformazioniPagamento($nome, $cognome, $indirizzo, $telefono, $email, $orarioConsegna, $note, $deliveryType, $paymentType) {
        $id_carrello = $this->esisteCarrello();
        if ($id_carrello) {
            // Recupera i prodotti nel carrello
            
      
            
            // Converti il tipo di consegna in un valore numerico per il database (1 = Delivery, 2 = Asporto)
            $tipologia = ($deliveryType === 'Delivery') ? 1 : 2;

            // Inserisci i dettagli dell'ordine nella tabella `carrello_dettaglio`
            if ($this->inserisciDettagliOrdine($id_carrello, $tipologia, $orarioConsegna, $note, $paymentType)) {
                // Salva le informazioni dell'utente
                if ($this->salvaInformazioniUtente($nome, $cognome, $email, $indirizzo, $telefono, $id_carrello)) {
                    // Aggiorna il flag "ordinato" per il carrello
                   
            
                    

                    
    
                    // Invia email con i dettagli dell'ordine
                    

               
                        return [
                            'success' => true,
                            'id_carrello' => $id_carrello,
                        ];
                    }
                } else {
                    return [
                        'success' => 0,
                        'message' => 'Errore durante il salvataggio delle informazioni utente.'
                    ];
                }
            } else {
                return [
                    'success' => 0,
                    'message' => 'Errore durante l\'inserimento dei dettagli dell\'ordine.'
                ];
            }
        
    }
    

    public function riepilogo() {
        $id_carrello = $this->controlloCarrello();
        
        $ordine = get_data("SELECT id_prodottiCarrello, numero_prodotti, prezzo, id_prodotto  FROM prodotticarrello WHERE id_carrello='$id_carrello'");
        
        $totale = 0;
        $prodotti_ordinati = [];
        
        foreach ($ordine as $item) {
            if (is_array($item)) {
                $subtotale = $item['numero_prodotti'] * $item['prezzo'];
                $totale += $subtotale;
                $nomeProdotto = get_db_value("SELECT titolo FROM prodotto WHERE id='" . $item['id_prodotto'] . "'");
                $prodotti_ordinati[] = [
                    'id_prodotto_carrello' => $item['id_prodottiCarrello'],
                    'nomeProdotto' => $nomeProdotto,
                    'quantita' => $item['numero_prodotti'],
                    'prezzo_unitario' => $item['prezzo'],
                    'subtotale' => $subtotale
                ];
            } else {
                error_log("Elemento non valido nell'ordine: " . print_r($item, true));
            }
        }
        
    
        return [
            'success' => TRUE,
            'id_carrello' => $id_carrello,
            'prodotti' => $prodotti_ordinati,
            'totale' => $totale
        ];
    }

    public function importoTotale() {
        $id_carrello = $this->controlloCarrello();
        
        $ordine = get_data("SELECT id_prodottiCarrello, numero_prodotti, prezzo, id_prodotto  FROM prodotticarrello WHERE id_carrello='$id_carrello'");
        
        $totale = 0;
        $prodotti_ordinati = [];
        
        foreach ($ordine as $item) {
            if (is_array($item)) {
                $subtotale = $item['numero_prodotti'] * $item['prezzo'];
                $totale += $subtotale;
                
            } else {
                error_log("Elemento non valido nell'ordine: " . print_r($item, true));
            }
        }
        
    
        return [
            'success' => TRUE,
            'totale' => $totale
        ];
    }
    public function elencoOrdini() {
        // Ottieni gli ordini e il totale del prezzo direttamente in un'unica query
        $ordini = get_data("
            SELECT 
                c.id_carrello, c.data_ordinazione, c.orario_ordinazione, uc.nome, uc.cognome, uc.email, 
                SUM(pc.prezzo * pc.numero_prodotti) AS prezzo_totale
            FROM carrello c
            INNER JOIN utente_carrello uc ON c.id_carrello = uc.id_carrello
            INNER JOIN prodotticarrello pc ON c.id_carrello = pc.id_carrello
            WHERE c.flag_conferma IS NULL 
              AND c.flag_ordinato IS NOT NULL
              AND c.flag_eliminato =0
              AND c.flag_rifiutato =0
            GROUP BY c.id_carrello, uc.nome, uc.cognome, uc.email
            ORDER BY c.data_ordinazione DESC

        ");
    
        // Inizializza un array per raccogliere gli ordini elaborati
        $elencoOrdini = [];
        
        foreach ($ordini as $item) {
            if (is_array($item)) {
                $orarioOriginale = $item['orario_ordinazione'];

                $orarioDateTime = DateTime::createFromFormat('H:i:s', $orarioOriginale);

                if ($orarioDateTime !== false) {
                    $orarioFormattato = $orarioDateTime->format('H:i');
                } else {
                    $orarioFormattato = 'Formato orario non valido';
                }

                // Aggiungi l'ordine all'elenco con il totale del prezzo
                $elencoOrdini[] = [
                    'id_carrello' => $item['id_carrello'],
                    'nome' => $item['nome'],
                    'cognome' => $item['cognome'],
                    'email' => $item['email'],
                    'data_ordinazione' => $item['data_ordinazione'],
                    'orario_ordinazione' => $orarioFormattato,
                    'prezzo' => $item['prezzo_totale'] // Prezzo totale per l'ordine
                ];
            } else {
                error_log("Elemento non valido nell'ordine: " . print_r($item, true));
            }
        }
    
        return [
            'success' => TRUE,
            'elencoOrdini' => $elencoOrdini
        ];
    }
    
    

    public function elencoOrdiniConfermati() {
        
        $ordini = get_data("SELECT  * 
        FROM carrello
        INNER JOIN utente_carrello ON carrello.id_carrello = utente_carrello.id_carrello
        INNER JOIN carrello_dettaglio ON carrello.id_carrello = carrello_dettaglio.id_carrello
        WHERE 
        (carrello.flag_conferma =1 or flag_eliminato=1) or(carrello.flag_conferma=1 or flag_rifiutato=1)
        AND carrello.flag_ordinato IS NOT NULL
        ORDER BY carrello.data_ordinazione DESC, carrello.orario_ordinazione DESC");
        $elencoOrdini = [];
        
        foreach ($ordini as $item) {
            if (is_array($item)) {
                if ($item['flag_conferma'] == 1) {
                    $flag_confermato = "CONFERMATO";
                } elseif ($item['flag_eliminato'] == 1) {
                    $flag_confermato = "ELIMINATO";
                } elseif ($item['flag_rifiutato'] == 1) {
                    $flag_confermato = "RIFIUTATO";
                } 

                //formatta ore senza secondi 
                $orarioOriginale = $item['orario_ordinazione'];

                $orarioDateTime = DateTime::createFromFormat('H:i:s', $orarioOriginale);

                if ($orarioDateTime !== false) {
                    $orarioFormattato = $orarioDateTime->format('H:i');
                } else {
                    $orarioFormattato = 'Formato orario non valido';
                }

                $elencoOrdini[] = [
                    'id_carrello' => $item['id_carrello'],
                    'nome' => $item['nome'],
                    'cognome' => $item['cognome'],
                    'email' => $item['email'],
                    'data_ordinazione' => $item['data_ordinazione'],
                    'data_conferma' => $item['data_conferma'],
                    'orario_ordinazione' => $orarioFormattato,
                    'flag_confermato' => $flag_confermato
                ];
            } else {
                error_log("Elemento non valido nell'ordine: " . print_r($item, true));
            }
        }
        
    
        return [
            'success' => TRUE,
            'elencoOrdiniConfermati' => $elencoOrdini
        ];
    }
    
    public function visualizzaDettagli($id_carrello) {
        $ordine = get_data("SELECT * 
        FROM carrello
        INNER JOIN utente_carrello ON carrello.id_carrello = utente_carrello.id_carrello
        INNER JOIN carrello_dettaglio ON carrello.id_carrello = carrello_dettaglio.id_carrello
        WHERE carrello.flag_conferma IS NULL 
          AND carrello.flag_ordinato IS NOT NULL
          AND carrello.id_carrello='$id_carrello';");


        $prodotti=get_data("SELECT * FROM prodotticarrello WHERE id_carrello='$id_carrello'");

        // Initialize a new array to store processed product data
        $prodotti_list = [];

        // Process the products and store them in $prodotti_list
        if (is_array($prodotti)) {
            foreach ($prodotti as $item) {
                if (is_array($item)) {
                    $nomeProdotto = get_db_value("SELECT titolo FROM prodotto WHERE id='" . $item['id_prodotto'] . "'");
                    $ingredientiArray = [];

                    $ingredienti=get_data("SELECT descrizione FROM IngredientiKebab WHERE id_prodottiCarrello='" . $item['id_prodottiCarrello'] . "'");

                    if (is_array($ingredienti)) {
                        foreach ($ingredienti as $ingrediente) {
                            $ingredientiArray[] = $ingrediente;
                        }
                    }
                    
                    $prodotti_list[] = [
                        'prezzo' => $item['prezzo'],
                        'quantita' => $item['numero_prodotti'],
                        'nomeProdotto' => $nomeProdotto,
                        // Add other fields if necessary
                        'ingredienti' => $ingredientiArray, // Aggiungi gli ingredienti

                    ];
                }
            }
        }
        // Controlla se l'ordine esiste
        if (!empty($ordine) && is_array($ordine[0])) {
            $item = $ordine[0]; // Prendi il primo (e unico) elemento


            if($item['tipologia_pagamento']=="Elettronico"){
                $tipologia_pagamento="POS";
            }elseif($item['tipologia_pagamento']=="Carta"){
                $tipologia_pagamento="Pagato";
            }else{
                $tipologia_pagamento="CASH";
            }
    
            return [
                'success' => true,
                'data' => [
                    'id_carrello' => $item['id_carrello'],
                    'nome' => $item['nome'],
                    'cognome' => $item['cognome'],
                    'email' => $item['email'],
                    'indirizzo' => $item['indirizzo'],
                    'telefono' => $item['telefono'],
                    'pagamento' => $tipologia_pagamento,
                    'orario_consegna' => substr($item['orario_consegna'], 0, 5),
                    'prodotti' => $prodotti_list
                    // Aggiungi altri campi se necessario
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Ordine non trovato.'
            ];
        }
    }


    public function visualizzaDettagliProdotto($id) {

        $prodotto = get_data("SELECT *  FROM prodotto WHERE id='$id'");


        // Controlla se l'ordine esiste
        if (!empty($prodotto) && is_array($prodotto[0])) {
            $item = $prodotto[0]; // Prendi il primo (e unico) elemento


    
            return [
                'success' => true,
                'data' => [
                    'id' => $item['id'],
                    'titolo' => $item['titolo'],
                    'descrizione' => $item['descrizione'],
                    'prezzo' => $item['prezzo'],
                    'categoria' => $item['categoria'],
                    'immagine' => $item['immagine'],
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Ordine non trovato.'
            ];
        }
    }



    public function visualizzaDettagliEvento($id) {
        echo "ciaooo";
        // Controlla se l'evento esiste
        if (!empty($evento) && is_array($evento[0])) {
            $item = $evento[0]; // Prendi il primo (e unico) elemento
    
            return [
                'success' => true,
                'data' => [
                    'id_eventi' => $item['id_eventi'],
                    'titolo' => $item['titolo'],
                    'descrizione' => $item['descrizione'],
                    'data_evento' => $item['data_evento'],
                    'nome_invitato' => $item['nome_invitato'],
                    'immagine' => $item['immagine'],
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Evento non trovato.'
            ];
        }
    }

    
    public function visualizzaDettagliEventi($id) {
        $evento = get_data("SELECT * FROM eventi WHERE id_eventi='$id'");

        // Controlla se l'evento esiste
        if (!empty($evento) && is_array($evento[0])) {
            $item = $evento[0]; // Prendi il primo (e unico) elemento
    
            return [
                'success' => true,
                'data' => [
                    'id_eventi' => $item['id_eventi'],
                    'titolo' => $item['titolo'],
                    'descrizione' => $item['descrizione'],
                    'data_evento' => $item['data_evento'],
                    'nome_invitato' => $item['nome_invitato'],
                    'immagine' => $item['immagine'],
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Evento non trovato.'
            ];
        }
    }


    public function visualizzaDettagliUtente($id_utente) {
        $utente = get_data("SELECT *  FROM utente_carrello WHERE id_utente_carrello='$id_utente';");



        // Controlla se l'ordine esiste
        if (!empty($utente) && is_array($utente[0])) {
            $item = $utente[0]; // Prendi il primo (e unico) elemento


    
            return [
                'success' => true,
                'data' => [
                    'nome' => $item['nome'],
                    'cognome' => $item['cognome'],
                    'email' => $item['email'],
                    'indirizzo' => $item['indirizzo'],
                    'telefono' => $item['telefono'],
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Ordine non trovato.'
            ];
        }
    }

    
    public function visualizzaDettagliConfermato($id_carrello) {
        $ordine = get_data("SELECT * 
        FROM carrello
        INNER JOIN utente_carrello ON carrello.id_carrello = utente_carrello.id_carrello
        INNER JOIN carrello_dettaglio ON carrello.id_carrello = carrello_dettaglio.id_carrello
        WHERE 
           carrello.flag_ordinato IS NOT NULL
          AND carrello.id_carrello='$id_carrello'");


        $prodotti=get_data("SELECT * FROM prodotticarrello WHERE id_carrello='$id_carrello'");
        // Initialize a new array to store processed product data
        $prodotti_list = [];

        // Process the products and store them in $prodotti_list
        if (is_array($prodotti)) {
            foreach ($prodotti as $item) {
                if (is_array($item)) {
                    $nomeProdotto = get_db_value("SELECT titolo FROM prodotto WHERE id='" . $item['id_prodotto'] . "'");
                    $prodotti_list[] = [
                        'prezzo' => $item['prezzo'],
                        'quantita' => $item['numero_prodotti'],
                        'nomeProdotto' => $nomeProdotto,
                        // Add other fields if necessary
                    ];
                }
            }
        }
        // Controlla se l'ordine esiste
        if (!empty($ordine) && is_array($ordine[0])) {
            $item = $ordine[0]; // Prendi il primo (e unico) elemento
    
            return [
                'success' => true,
                'data' => [
                    'id_carrello' => $item['id_carrello'],
                    'nome' => $item['nome'],
                    'cognome' => $item['cognome'],
                    'email' => $item['email'],
                    'indirizzo' => $item['indirizzo'],
                    'telefono' => $item['telefono'],
                    'orario_consegna' => substr($item['orario_consegna'], 0, 5),
                    'prodotti' => $prodotti_list
                    // Aggiungi altri campi se necessario
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Ordine non trovato.'
            ];
        }
    }

    public function confermaOrdine($id_carrello) {
        // Ottieni i dettagli dell'ordine incluso prodotti, tipologia, orario di consegna e note
        $data_conferma = date("Y-m-d H:i:s"); // Formato compatibile con SQL DATETIME

        $ordini = get_data("
            SELECT 
                c.id_carrello, uc.nome, uc.cognome, uc.email, 
                p.titolo AS nomeProdotto, pc.prezzo, pc.numero_prodotti,
                cd.tipologia, cd.orario_consegna, cd.note, uc.indirizzo, uc.telefono
            FROM carrello c
            INNER JOIN utente_carrello uc ON c.id_carrello = uc.id_carrello
            INNER JOIN carrello_dettaglio cd ON c.id_carrello = cd.id_carrello
            INNER JOIN prodotticarrello pc ON c.id_carrello = pc.id_carrello
            INNER JOIN prodotto p ON pc.id_prodotto = p.id
            WHERE c.flag_conferma IS NULL 
                AND c.id_carrello = '$id_carrello'
                AND c.flag_ordinato IS NOT NULL
        ");
    
        
        if (is_array($ordini) && count($ordini) > 0) {
            $ordine = $ordini[0];  
    
            $deliveryType = ($ordine['tipologia'] == 1) ? 'Delivery' : 'Asporto';
    
            $prodotti_ordinati = [];
            $totaleOrdine = 0;
    
            foreach ($ordini as $item) {
                if (is_array($item)) {
                    $prezzoProdottoTotale = $item['prezzo'] * $item['numero_prodotti'];
                    $totaleOrdine += $prezzoProdottoTotale;
    
                    $prodotti_ordinati[] = [
                        'nomeProdotto' => $item['nomeProdotto'],
                        'quantita' => $item['numero_prodotti'],
                        'prezzo_unitario' => $item['prezzo'],
                        'subtotale' => $prezzoProdottoTotale
                    ];
                }
            }
    

         

            if($this->inviaEmailConferma(
                $ordine['nome'],
                $ordine['cognome'],
                $ordine['indirizzo'],    
                $ordine['telefono'],     
                $ordine['email'],
                $ordine['orario_consegna'],
                $ordine['note'],
                $deliveryType,
                $prodotti_ordinati,
                $totaleOrdine
            )){
                $sql = "UPDATE carrello 
                SET flag_conferma = 1,
                data_conferma = '$data_conferma'
                WHERE id_carrello = '$id_carrello'";
        
                $update_success = $this->conn->query($sql) === TRUE;
            }
            
            if($update_success){
                return [
                    'success' => true,
                    'message' => 'Email inviata con successo.'
                ];
            }else{
                return [
                    'success' => false,
                    'message' => 'Errore invio mail.'
                ];
            }
           
        } else {
            return [
                'success' => false,
                'message' => 'Ordine non trovato.'
            ];
        }
    }


    public function eliminaOrdine($id_carrello) {
                $sql = "UPDATE carrello 
                SET flag_eliminato = 1
                WHERE id_carrello = '$id_carrello'";
        
                $update_success = $this->conn->query($sql) === TRUE;
            
            if($update_success){
                return [
                    'success' => true,
                    'message' => 'Eliminato con successo.'
                ];
            }else{
                return [
                    'success' => false,
                    'message' => 'Errore invio mail.'
                ];
            }
           
       
    }

    public function rifiutaOrdine($id_carrello) {
        // Recupera i dati dell'utente associati all'ordine
        $dati_utente = get_data("SELECT * FROM utente_carrello WHERE id_carrello='$id_carrello'");
    
        if (!empty($dati_utente) && is_array($dati_utente[0])) {
            $item = $dati_utente[0]; // Prendi il primo (e unico) elemento
    
            // Recupera le informazioni necessarie per l'invio dell'email
            $nome = $item['nome'];
            $cognome = $item['cognome'];
            $email = $item['email'];
    
            // Aggiorna il flag dell'ordine come rifiutato
            $sql = "UPDATE carrello 
                    SET flag_rifiutato = 1
                    WHERE id_carrello = '$id_carrello'";
            $update_success = $this->conn->query($sql) === TRUE;
    
            // Se l'aggiornamento è andato a buon fine, invia l'email
            if ($update_success) {
                $email_inviata = $this->inviaEmailRifiutoOrdine($nome, $cognome, $email);
                if ($email_inviata) {
                    return [
                        'success' => true,
                        'message' => 'Ordine rifiutato e email inviata con successo.'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Ordine rifiutato, ma si è verificato un errore durante l\'invio dell\'email.'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Errore nell\'aggiornamento dello stato dell\'ordine.'
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => 'Nessun ordine trovato con l\'ID fornito.'
            ];
        }
    }
    
    
    public function elencoUtenti() {
        // Ottieni gli ordini e il totale del prezzo direttamente in un'unica query
        $utenti = get_data("SELECT distinct * FROM utente_carrello");
    
        // Inizializza un array per raccogliere gli ordini elaborati
        $elencoUtenti = [];
        
        foreach ($utenti as $item) {
            if (is_array($item)) {
                // Aggiungi l'ordine all'elenco con il totale del prezzo
                $elencoUtenti[] = [
                    'id_utente' => $item['id_utente_carrello'],
                    'nome' => $item['nome'],
                    'cognome' => $item['cognome'],
                    'email' => $item['email'],
                    'telefono' => $item['telefono'] // Prezzo totale per l'ordine
                ];
            } else {
                error_log("Elemento non valido nell'ordine: " . print_r($item, true));
            }
        }
    
        return [
            'success' => TRUE,
            'elencoUtenti' => $elencoUtenti
        ];
    }
        
    public function getCardsHtml($categoria) {
        $query = "SELECT * FROM prodotto WHERE categoria='$categoria' AND visibile=1";
        if($categoria == ""){
            $query = "SELECT * FROM prodotto WHERE visibile=1";
        }
        $prodotti = get_data($query);
        $html = "";
        foreach ($prodotti as $item) {
            $html .= "<div class='col-lg-4 d-flex ftco-animate mb-5 product-card' data-category='" . $item['categoria'] . "'>
            <div class='services-wrap d-flex'>
            <a class='img' style='background-image: url(images/" . $item['immagine'] . ");'></a>
            <div class='text p-4'>
            <h3>" . $item['titolo'] . "</h3>
            <p>" . $item['descrizione'] . "</p>
            <p>" . $item['prezzo'] . "€</p>
            <button id='btn-aggiungi' class='ml-2 btn btn-white btn-outline-white' data-id='" . $item['id'] . "' onclick='aggiungiProdotto(" . $item['id'] . ")'>Aggiungi al Carrello</button>
            </div>
            </div>
            </div>";
        }
        $data = [
            'success' => TRUE,
            'html' => $html,
            'query' => $query
        ];
        return $data;
    }

    
    public function numeroConfermare() {
        // Ottieni gli ordini e il totale del prezzo direttamente in un'unica query
        $ordini = get_data("
        SELECT distinct 
            COUNT(c.id_carrello)
           
        FROM carrello c
        INNER JOIN utente_carrello uc ON c.id_carrello = uc.id_carrello
        WHERE c.flag_conferma IS NULL 
          AND c.flag_ordinato IS NOT NULL
          AND c.flag_eliminato=0
          AND c.flag_rifiutato=0;
       
    ");
        
     
           
    
        return [
            'success' => TRUE,
            'numero' => $ordini
        ];
    }


    public function numeroProdotti() {

        $id_carrello = $this->esisteCarrello();
        // Ottieni gli ordini e il totale del prezzo direttamente in un'unica query
        $numeroProdotti = get_db_value("
      select COUNT( prodotticarrello.id_prodottiCarrello) 
      from prodotticarrello 
      inner join carrello on prodotticarrello.id_carrello= carrello.id_carrello where carrello.id_carrello='$id_carrello'"
       
     );
        
        return [
            'success' => TRUE,
            'numeroProdotti' => $numeroProdotti
        ];
    }


    
    private function inviaEmailOrdine($nome, $cognome, $indirizzo, $telefono, $email, $orarioConsegna, $note, $deliveryType, $prodotti_ordinati, $totale) {
        require 'PHPMailer/src/Exception.php';
        require 'PHPMailer/src/PHPMailer.php';
        require 'PHPMailer/src/SMTP.php';
        $mail = new PHPMailer\PHPMailer\PHPMailer();
    
        try {
            $mail->IsSMTP(); 
            $mail->SMTPSecure = 'tls'; 
            $mail->Port = 587;
            $mail->Host = "smtp.gmail.com"; 
            $mail->SMTPAuth = true;
            $mail->isHTML(true);
            $mail->Username = "safi.jumadin94@gmail.com";  
            $mail->Password = "zihg kynt bzwq vnhg";  // Non lasciare mai le password nel codice finale!
            
            // Mittente, destinatario e CC
            $mail->setFrom('mittente@email.com', 'Sistema Ordini');
            // $mail->addAddress("nirvanacamerino@gmail.com");  
            $mail->addAddress("camerinowork@gmail.com");  
            $mail->addCC("retahanterai@gmail.com"); // Aggiungi qui il CC desiderato

            // Oggetto dell'email
            $mail->Subject = 'Nuovo Ordine Ricevuto da ' . $nome . ' ' . $cognome;
        
            // Corpo dell'email
            $body = "<h1>Riepilogo Ordine</h1>";
            $body .= "<p>Un nuovo ordine è stato effettuato. Di seguito i dettagli del cliente:</p>";
            
            // Elenco puntato dei dettagli dell'utente
            $body .= "<ul>";
            $body .= "<li><strong>Nome:</strong> $nome $cognome</li>";
            $body .= "<li><strong>Indirizzo:</strong> $indirizzo</li>";
            $body .= "<li><strong>Telefono:</strong> $telefono</li>";
            $body .= "<li><strong>Email:</strong> $email</li>";
            $body .= "<li><strong>Orario di Consegna:</strong> $orarioConsegna</li>";
            $body .= "<li><strong>Note:</strong> $note</li>";
            $body .= "<li><strong>Tipo di Consegna:</strong> $deliveryType</li>";
            $body .= "</ul>";
    
            // Aggiungi i prodotti ordinati in una tabella
            $body .= "<h2>Prodotti Ordinati</h2>";
            $body .= "<table border='1' cellpadding='5' cellspacing='0'>";
            $body .= "<thead><tr><th>Prodotto</th><th>Quantità</th><th>Prezzo Unitario</th><th>Subtotale</th></tr></thead>";
            $body .= "<tbody>";
        
            // Aggiungi i prodotti all'email
            foreach ($prodotti_ordinati as $prodotto) {
                $body .= "<tr>
                            <td>{$prodotto['nomeProdotto']}</td>
                            <td>{$prodotto['quantita']}</td>
                            <td>€" . number_format($prodotto['prezzo_unitario'], 2) . "</td>
                            <td>€" . number_format($prodotto['subtotale'], 2) . "</td>
                          </tr>";
            }
    
            $body .= "</tbody>";
            $body .= "</table>";
            
            // Totale dell'ordine
            $body .= "<p><strong>Totale Ordine: €" . number_format($totale, 2) . "</strong></p>";
    
            // Imposta il corpo dell'email
            $mail->Body = $body;
        
            // Invia l'email
            if ($mail->send()) {
                return true;
            } else {
                echo "Errore durante l'invio dell'email.";
            }
        } catch (Exception $e) {
            echo "Errore: " . $mail->ErrorInfo;
        }
    }


    private function inviaEmailConferma($nome, $cognome, $indirizzo, $telefono, $email, $orarioConsegna, $note, $deliveryType, $prodotti_ordinati, $totale) {
        require 'PHPMailer/src/Exception.php';
        require 'PHPMailer/src/PHPMailer.php';
        require 'PHPMailer/src/SMTP.php';
    
        $mail = new PHPMailer\PHPMailer\PHPMailer();
    
        try {
            $mail->IsSMTP(); 
            $mail->SMTPSecure = 'tls'; 
            $mail->Port = 587;
            $mail->Host = "smtp.gmail.com"; 
            $mail->SMTPAuth = true;
            $mail->isHTML(true);
            $mail->Username = "safi.jumadin94@gmail.com";  
            $mail->Password = "zihg kynt bzwq vnhg";
            
            // Mittente e destinatario
            $mail->setFrom('mittente@email.com', 'Sistema Ordini');
            $mail->addAddress($email);  
            
            // Oggetto dell'email
            $mail->Subject = 'Conferma ordine';
        
            // Corpo dell'email
            $body = "<h1>Conferma Ordine Nirvana</h1>";
            $body .= "<p>Di seguito i dettagli dell'ordine:</p>";
            
            // Dettagli del cliente
            $body .= "<ul>";
            $body .= "<li><strong>Nome:</strong> $nome $cognome</li>";
            $body .= "<li><strong>Indirizzo:</strong> $indirizzo</li>";
            $body .= "<li><strong>Telefono:</strong> $telefono</li>";
            $body .= "<li><strong>Email:</strong> $email</li>";
            $body .= "<li><strong>Orario di Consegna:</strong> $orarioConsegna</li>";
            $body .= "<li><strong>Note:</strong> $note</li>";
            $body .= "<li><strong>Tipo di Consegna:</strong> $deliveryType</li>";
            $body .= "</ul>";
        
            // Prodotti ordinati
            $body .= "<h2>Prodotti Ordinati</h2>";
            $body .= "<table border='1' cellpadding='5' cellspacing='0'>";
            $body .= "<thead><tr><th>Prodotto</th><th>Quantità</th><th>Prezzo Unitario</th><th>Subtotale</th></tr></thead>";
            $body .= "<tbody>";
        
            // Aggiungi i prodotti all'email
            foreach ($prodotti_ordinati as $prodotto) {
                $body .= "<tr>
                            <td>{$prodotto['nomeProdotto']}</td>
                            <td>{$prodotto['quantita']}</td>
                            <td>€" . number_format($prodotto['prezzo_unitario'], 2) . "</td>
                            <td>€" . number_format($prodotto['subtotale'], 2) . "</td>
                          </tr>";
            }
        
            $body .= "</tbody>";
            $body .= "</table>";
            
            // Totale dell'ordine
            $body .= "<p><strong>Totale Ordine: €" . number_format($totale, 2) . "</strong></p>";
        
            // Imposta il corpo dell'email
            $mail->Body = $body;
        
            // Invia l'email
            if ($mail->send()) {
                return true;
            } else {
                echo "Errore durante l'invio dell'email.";
            }
        } catch (Exception $e) {
            echo "Errore: " . $mail->ErrorInfo;
        }
    }

    private function inviaEmailRifiutoOrdine($nome, $cognome, $email) {
        require 'PHPMailer/src/Exception.php';
        require 'PHPMailer/src/PHPMailer.php';
        require 'PHPMailer/src/SMTP.php';
    
        $mail = new PHPMailer\PHPMailer\PHPMailer();
    
        try {
            $mail->IsSMTP(); 
            $mail->SMTPSecure = 'tls'; 
            $mail->Port = 587;
            $mail->Host = "smtp.gmail.com"; 
            $mail->SMTPAuth = true;
            $mail->isHTML(true);
            $mail->Username = "safi.jumadin94@gmail.com";  
            $mail->Password = "zihg kynt bzwq vnhg"; // Assicurati di sostituire questa password con una sicura
            $mail->setFrom('noreply@tuosito.com', 'Sistema Ordini');
            $mail->addAddress($email);  
    
            // Oggetto dell'email
            $mail->Subject = 'Rifiuto Ordine da ' . $nome . ' ' . $cognome;
    
            // Corpo dell'email
            $body = "<h1>Rifiuto dell'Ordine</h1>";
            $body .= "<p>Caro $nome $cognome, purtroppo il tuo ordine è stato rifiutato. Ci scusiamo per l'inconveniente.</p>";
            $body .= "<p>Se hai domande, non esitare a contattarci.</p>";
    
            // Imposta il corpo dell'email
            $mail->Body = $body;
    
            // Invia l'email
            if ($mail->send()) {
                return true;
            } else {
                echo "Errore durante l'invio dell'email: " . $mail->ErrorInfo;
                return false;
            }
        } catch (Exception $e) {
            echo "Errore: " . $mail->ErrorInfo;
            return false;
        }
    }


    
    
    private function inserisciDettagliOrdine($id_carrello, $tipologia, $orarioConsegna, $note, $paymentType) {
        // Controlla se esiste già un record in carrello_dettaglio per l'id_carrello fornito
        $carrello_dettaglio = get_db_value("SELECT id_carrello_dettaglio FROM carrello_dettaglio WHERE id_carrello='$id_carrello'");
    
        if (!empty($carrello_dettaglio)) {
            // Query di aggiornamento
            $sqlDettagli_update = "UPDATE carrello_dettaglio 
                                  SET tipologia = '$tipologia', 
                                      orario_consegna = '$orarioConsegna', 
                                      note = '$note', 
                                      tipologia_pagamento = '$paymentType' 
                                  WHERE id_carrello = '$id_carrello'";
    
            // Esegui la query di aggiornamento e ritorna il risultato
            return $this->conn->query($sqlDettagli_update) === TRUE;
        } else {
            // Query di inserimento
            $sqlDettagli = "INSERT INTO carrello_dettaglio (id_carrello, tipologia, orario_consegna, note, tipologia_pagamento)
                            VALUES ('$id_carrello', '$tipologia', '$orarioConsegna', '$note', '$paymentType')";
    
            // Esegui la query di inserimento e ritorna il risultato
            return $this->conn->query($sqlDettagli) === TRUE;
        }
    }
    
        
    private function salvaInformazioniUtente($nome, $cognome, $email, $indirizzo, $telefono, $id_carrello) {
        // Controlla se esiste già un record in utente_carrello per l'id_carrello fornito
        $utente_carrello = get_db_value("SELECT id_utente_carrello FROM utente_carrello WHERE id_carrello='$id_carrello'");
    
        if (!empty($utente_carrello)) {
            // Query di aggiornamento
            $sqlUtente_update = "UPDATE utente_carrello 
                                 SET nome = '$nome', 
                                     cognome = '$cognome', 
                                     email = '$email', 
                                     indirizzo = '$indirizzo', 
                                     telefono = '$telefono' 
                                 WHERE id_carrello = '$id_carrello'";
    
            // Esegui la query di aggiornamento e ritorna il risultato
            return $this->conn->query($sqlUtente_update) === TRUE;
        } else {
            // Query di inserimento
            $sqlUtente = "INSERT INTO utente_carrello (nome, cognome, email, indirizzo, telefono, id_carrello)
                          VALUES ('$nome', '$cognome', '$email', '$indirizzo', '$telefono', '$id_carrello')";
    
            // Esegui la query di inserimento e ritorna il risultato
            return $this->conn->query($sqlUtente) === TRUE;
        }
    }
    


    public function salvaProdotto($idProdotto,$titolo, $descrizione, $prezzo, $nomeImmagine, $categoria) {
        $titolo = $this->conn->real_escape_string($titolo);
        $descrizione = $this->conn->real_escape_string($descrizione);
        $prezzo = floatval($prezzo); // Assicurati che sia un numero
        $nomeImmagine = $this->conn->real_escape_string($nomeImmagine);
        $categoria = $this->conn->real_escape_string($categoria);


        $idProdotto=get_db_value("SELECT id FROM prodotto WHERE id='$idProdotto'");
        if(empty($idProdotto)){
            if(!empty($nomeImmagine)){
                 $sql = "INSERT INTO prodotto (titolo, descrizione, prezzo,immagine,categoria) VALUES ('$titolo', '$descrizione','$prezzo','$nomeImmagine','$categoria')";

            } else {
                $sql = "INSERT INTO prodotto (titolo, descrizione, prezzo,categoria) VALUES ('$titolo', '$descrizione','$prezzo','$categoria')";

            }

            if ($this->conn->query($sql) === TRUE){
                return [
                    'success' => true,
                    'message' => 'Prodotto aggiornato con successo.'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $this->conn->error // Restituisci l'errore in caso di fallimento
                ];
            }


        } else {

            if(!empty($nomeImmagine)){
                $sql = "UPDATE prodotto 
                SET titolo = '$titolo', descrizione = '$descrizione', prezzo = $prezzo, immagine = '$nomeImmagine', categoria = '$categoria' 
                WHERE id = '$idProdotto'";
            }else{
                $sql = "UPDATE prodotto 
                SET titolo = '$titolo', descrizione = '$descrizione', prezzo = $prezzo, categoria = '$categoria' 
                WHERE id = '$idProdotto'";  
            }
            
            
            // Esegui la query
            if ($this->conn->query($sql) === TRUE) {
                return [
                    'success' => true,
                    'message' => 'Prodotto aggiornato con successo.'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $this->conn->error // Restituisci l'errore in caso di fallimento
                ];
            }
        }


       

        // Prepara la query per l'update
    
    }

    public function salvaCategoria($descrizione) {
        $descrizione = $this->conn->real_escape_string($descrizione);
        $sql = "INSERT INTO categoria (descrizione) VALUES ('$descrizione')";
        if ($this->conn->query($sql) === TRUE) {
            return [
                'success' => true,
                'message' => 'categoria aggiunta.'
            ];
        } else {
            return [
                'success' => false,
                'error' => $this->conn->error
            ];
        }
    }
    

    public function salvaEvento($idEvento, $titolo, $descrizione, $data_evento, $nome_invitato, $nomeImmagine) {
        $titolo = $this->conn->real_escape_string($titolo);
        $descrizione = $this->conn->real_escape_string($descrizione);
        $data_evento = $this->conn->real_escape_string($data_evento);
        $nome_invitato = $this->conn->real_escape_string($nome_invitato);
        $nomeImmagine = $this->conn->real_escape_string($nomeImmagine);

        $idEvento=get_db_value("SELECT id_eventi FROM eventi WHERE id_eventi='$idEvento'");

        if(empty($idEvento)){

            if(!empty($nomeImmagine)){
                $sql = "INSERT INTO eventi (titolo, descrizione, data_evento,nome_invitato,immagine) VALUES ('$titolo', '$descrizione','$data_evento','$nome_invitato','$nomeImmagine')";
            } else {
                $sql = "INSERT INTO eventi (titolo, descrizione, data_evento,nome_invitato) VALUES ('$titolo', '$descrizione','$data_evento','$nome_invitato')";
            }


            if ($this->conn->query($sql) === TRUE){
                return [
                    'success' => true,
                    'message' => 'Prodotto aggiornato con successo.'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $this->conn->error // Restituisci l'errore in caso di fallimento
                ];
            }

        }else{
              // Prepara la query per l'update
        if(!empty($nomeImmagine)){
            $sql = "UPDATE eventi
                    SET titolo = '$titolo', descrizione = '$descrizione', data_evento = '$data_evento', nome_invitato = '$nome_invitato', immagine = '$nomeImmagine'
                    WHERE id_eventi = '$idEvento'";
            }else{
                $sql = "UPDATE eventi 
                 SET titolo = '$titolo', descrizione = '$descrizione', data_evento = '$data_evento', nome_invitato = '$nome_invitato'
                    WHERE id_eventi = '$idEvento'";
            }
        
            // Esegui la query
            if ($this->conn->query($sql) === TRUE) {
                return [
                    'success' => true,
                    'message' => 'Evento aggiornato con successo.',
                    'id_evento' => $idEvento
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $this->conn->error // Restituisci l'errore in caso di fallimento
                ];
            }
            
        }

      

    }
    
    /**
     * Registra un accesso alla pagina menu.php nel database
     * @param string $id_utente ID dell'utente (dalla sessione)
     * @param string $ip_address Indirizzo IP del visitatore
     * @param string $user_agent User agent del browser
     * @param string $pagina Nome della pagina visitata
     * @return bool True se l'inserimento è riuscito, false altrimenti
     */
    public function registraAccesso($id_utente = null, $ip_address = null, $user_agent = null, $pagina = 'menu.php') {
        // Prepara i dati per l'inserimento
        $id_utente = $id_utente ? $this->conn->real_escape_string($id_utente) : 'NULL';
        $ip_address = $ip_address ? $this->conn->real_escape_string($ip_address) : 'NULL';
        $user_agent = $user_agent ? $this->conn->real_escape_string($user_agent) : 'NULL';
        $pagina = $this->conn->real_escape_string($pagina);
        $data_ora = date('Y-m-d H:i:s');
        
        // Query per inserire il log
        $sql = "INSERT INTO log_accessi (id_utente, ip_address, user_agent, pagina, data_ora) 
                VALUES (" . ($id_utente !== 'NULL' ? "'$id_utente'" : 'NULL') . ", 
                        " . ($ip_address !== 'NULL' ? "'$ip_address'" : 'NULL') . ", 
                        " . ($user_agent !== 'NULL' ? "'$user_agent'" : 'NULL') . ", 
                        '$pagina', 
                        '$data_ora')";
        
        if ($this->conn->query($sql) === TRUE) {
            return true;
        } else {
            error_log("Errore durante la registrazione dell'accesso: " . $this->conn->error);
            return false;
        }
    }
    
    /**
     * Recupera le statistiche degli accessi
     * @param string $periodo Periodo da analizzare (oggi, settimana, mese, totale)
     * @return array Array con le statistiche
     */
    public function getStatisticheAccessi($periodo = 'totale') {
        $where = '';
        
        switch($periodo) {
            case 'oggi':
                $where = "WHERE DATE(data_ora) = CURDATE()";
                break;
            case 'settimana':
                $where = "WHERE data_ora >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case 'mese':
                $where = "WHERE data_ora >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                break;
            default:
                $where = "";
        }
        
        $statistiche = [];
        
        // Totale accessi
        $sql = "SELECT COUNT(*) as totale FROM log_accessi $where";
        $result = $this->conn->query($sql);
        if ($result) {
            $row = $result->fetch_assoc();
            $statistiche['totale_accessi'] = $row['totale'];
        }
        
        // Accessi unici (per IP)
        $sql = "SELECT COUNT(DISTINCT ip_address) as unici FROM log_accessi $where";
        $result = $this->conn->query($sql);
        if ($result) {
            $row = $result->fetch_assoc();
            $statistiche['accessi_unici'] = $row['unici'];
        }
        
        // Accessi per giorno (ultimi 7 giorni)
        $sql = "SELECT DATE(data_ora) as giorno, COUNT(*) as accessi 
                FROM log_accessi 
                WHERE data_ora >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY DATE(data_ora)
                ORDER BY giorno DESC";
        $result = $this->conn->query($sql);
        $statistiche['accessi_per_giorno'] = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $statistiche['accessi_per_giorno'][] = $row;
            }
        }
        
        // Accessi per ora del giorno (media)
        $sql = "SELECT HOUR(data_ora) as ora, COUNT(*) as accessi 
                FROM log_accessi $where
                GROUP BY HOUR(data_ora)
                ORDER BY ora";
        $result = $this->conn->query($sql);
        $statistiche['accessi_per_ora'] = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $statistiche['accessi_per_ora'][] = $row;
            }
        }
        
        return $statistiche;
    }
    
    

    
    
}
