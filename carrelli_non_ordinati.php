<?php
session_start();
include 'librerie/Database.php';
include 'librerie/metodi.php';

$db = new Database();

// Query per trovare i carrelli con prodotti ma non ordinati
// Esclude i carrelli che contengono solo prodotti non più esistenti nella tabella prodotto
// Mostra solo i carrelli che hanno corrispondenza nella tabella utente_carrello
$query = "SELECT DISTINCT c.*
          FROM carrello c
          INNER JOIN prodotticarrello pc ON c.id_carrello = pc.id_carrello
          INNER JOIN prodotto p ON pc.id_prodotto = p.id
          WHERE (c.flag_ordinato = 0 OR c.flag_ordinato IS NULL) AND c.flag_eliminato = 0 AND c.flag_rifiutato = 0";

$carrelli = get_data($query);

// Per ogni carrello, recuperiamo i prodotti associati e le informazioni utente
foreach ($carrelli as &$carrello) {
    $id_carrello = $carrello['id_carrello'];
    
    // Recupera i prodotti del carrello con i dettagli dalla tabella prodotto
    $query_prodotti = "SELECT pc.*, p.titolo, p.descrizione, p.categoria
                       FROM prodotticarrello pc
                       INNER JOIN prodotto p ON pc.id_prodotto = p.id
                       WHERE pc.id_carrello = '$id_carrello'";
    
    $carrello['prodotti'] = get_data($query_prodotti);
    
    // Recupera le informazioni utente dalla tabella utente_carrello
    $query_utente = "SELECT * FROM utente_carrello WHERE id_carrello = '$id_carrello' LIMIT 1";
    $utente_info = get_data($query_utente);
    $carrello['utente'] = !empty($utente_info) ? $utente_info[0] : null;
}
unset($carrello); // Rimuove il riferimento
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <title>Carrelli Non Ordinati - Nirvana Pub Pizzeria</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header h1 {
            margin: 0;
            font-size: 2.5rem;
        }
        
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .table thead th {
            border: none;
            padding: 15px;
            font-weight: 600;
        }
        
        .table tbody td {
            padding: 15px;
            vertical-align: middle;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .badge {
            padding: 5px 10px;
            font-size: 0.85rem;
        }
        
        .prodotti-list {
            max-height: 200px;
            overflow-y: auto;
        }
        
        .prodotto-item {
            padding: 8px;
            margin-bottom: 5px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border-left: 3px solid #667eea;
        }
        
        .prodotto-item strong {
            color: #667eea;
        }
        
        .prodotto-item .quantita {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .prodotto-item .prezzo {
            color: #28a745;
            font-weight: 600;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .btn-back {
            margin-bottom: 20px;
        }
        
        .carrello-info {
            font-size: 0.9rem;
        }
        
        .carrello-info .label {
            font-weight: 600;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <a href="menu.php" class="btn btn-secondary btn-back">
            <i class="fas fa-arrow-left"></i> Torna al Menu
        </a>
        
        <div class="header">
            <h1><i class="fas fa-shopping-cart"></i> Carrelli Non Ordinati</h1>
            <p>Visualizzazione di tutti i carrelli con prodotti che non sono stati ancora ordinati</p>
        </div>
        
        <?php if (empty($carrelli)): ?>
            <div class="table-container">
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>Nessun carrello trovato</h3>
                    <p>Non ci sono carrelli con prodotti non ordinati al momento.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID Carrello</th>
                                <th>ID Utente</th>
                                <th>Cliente</th>
                                <th>Conto Totale</th>
                                <th>Data Creazione</th>
                                <th>Flag Ordinato</th>
                                <th>Flag Eliminato</th>
                                <th>Flag Rifiutato</th>
                                <th>Prodotti</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($carrelli as $carrello): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo htmlspecialchars($carrello['id_carrello']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?php echo htmlspecialchars($carrello['id_utente'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($carrello['utente'])): ?>
                                            <div class="carrello-info">
                                                <div><span class="label">Nome:</span> <?php echo htmlspecialchars($carrello['utente']['nome'] ?? 'N/A'); ?></div>
                                                <div><span class="label">Cognome:</span> <?php echo htmlspecialchars($carrello['utente']['cognome'] ?? 'N/A'); ?></div>
                                                <div><span class="label">Email:</span> <?php echo htmlspecialchars($carrello['utente']['email'] ?? 'N/A'); ?></div>
                                                <div><span class="label">Tel:</span> <?php echo htmlspecialchars($carrello['utente']['telefono'] ?? 'N/A'); ?></div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong class="text-success">
                                            <?php echo number_format($carrello['contoTotale'] ?? 0, 2, ',', '.'); ?>€
                                        </strong>
                                    </td>
                                    <td>
                                        <?php 
                                        if (!empty($carrello['data_ordinazione'])) {
                                            $data = new DateTime($carrello['data_ordinazione']);
                                            echo $data->format('d/m/Y');
                                            if (!empty($carrello['orario_ordinazione'])) {
                                                echo '<br><small class="text-muted">' . $carrello['orario_ordinazione'] . '</small>';
                                            }
                                        } else {
                                            echo '<span class="text-muted">N/A</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $flag_ordinato = $carrello['flag_ordinato'] ?? null;
                                        if ($flag_ordinato === null || $flag_ordinato == 0) {
                                            echo '<span class="badge badge-warning">Non Ordinato</span>';
                                        } else {
                                            echo '<span class="badge badge-success">Ordinato</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $flag_eliminato = $carrello['flag_eliminato'] ?? 0;
                                        echo $flag_eliminato == 1 
                                            ? '<span class="badge badge-danger">Eliminato</span>' 
                                            : '<span class="badge badge-secondary">Attivo</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $flag_rifiutato = $carrello['flag_rifiutato'] ?? 0;
                                        echo $flag_rifiutato == 1 
                                            ? '<span class="badge badge-danger">Rifiutato</span>' 
                                            : '<span class="badge badge-secondary">Valido</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <div class="prodotti-list">
                                            <?php if (!empty($carrello['prodotti']) && is_array($carrello['prodotti'])): ?>
                                                <?php 
                                                $totale_carrello = 0;
                                                foreach ($carrello['prodotti'] as $prodotto): 
                                                    $subtotale = ($prodotto['prezzo'] ?? 0) * ($prodotto['numero_prodotti'] ?? 1);
                                                    $totale_carrello += $subtotale;
                                                ?>
                                                    <div class="prodotto-item">
                                                        <strong><?php echo htmlspecialchars($prodotto['titolo'] ?? 'Prodotto sconosciuto'); ?></strong>
                                                        <br>
                                                        <span class="quantita">
                                                            Quantità: <?php echo htmlspecialchars($prodotto['numero_prodotti'] ?? 1); ?>
                                                        </span>
                                                        <span class="prezzo float-right">
                                                            <?php echo number_format($prodotto['prezzo'] ?? 0, 2, ',', '.'); ?>€ 
                                                            <?php if (($prodotto['numero_prodotti'] ?? 1) > 1): ?>
                                                                (<?php echo number_format($subtotale, 2, ',', '.'); ?>€)
                                                            <?php endif; ?>
                                                        </span>
                                                        <?php if (!empty($prodotto['categoria'])): ?>
                                                            <br>
                                                            <small class="text-muted">
                                                                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($prodotto['categoria']); ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                                <div class="prodotto-item mt-2" style="background-color: #e7f3ff; border-left-color: #28a745;">
                                                    <strong>Totale Carrello: <?php echo number_format($totale_carrello, 2, ',', '.'); ?>€</strong>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">Nessun prodotto</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="mt-3 text-center text-muted">
                <p>Totale carrelli trovati: <strong><?php echo count($carrelli); ?></strong></p>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

