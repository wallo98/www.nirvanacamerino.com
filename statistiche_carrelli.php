<?php 
include 'librerie/Database.php';
include 'librerie/metodi.php';

// Gestione filtri
$data_da = isset($_GET['data_da']) ? $_GET['data_da'] : '';
$data_a = isset($_GET['data_a']) ? $_GET['data_a'] : '';
$filtro_stato = isset($_GET['filtro_stato']) ? $_GET['filtro_stato'] : 'tutti';
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$per_pagina = 20; // Numero di record per pagina
$offset = ($pagina - 1) * $per_pagina;

// Costruisci la condizione WHERE per i filtri
$where_conditions = [];
if (!empty($data_da)) {
    $where_conditions[] = "DATE(c.data_creazione) >= '" . $data_da . "'";
}
if (!empty($data_a)) {
    $where_conditions[] = "DATE(c.data_creazione) <= '" . $data_a . "'";
}

// Filtro per stato
if ($filtro_stato == 'confermati') {
    $where_conditions[] = "c.flag_conferma = 1";
} elseif ($filtro_stato == 'rifiutati') {
    $where_conditions[] = "c.flag_rifiutato = 1";
} elseif ($filtro_stato == 'ordinati') {
    $where_conditions[] = "c.flag_ordinato = 1 AND (c.flag_conferma IS NULL OR c.flag_conferma = 0)";
} elseif ($filtro_stato == 'non_ordinati') {
    $where_conditions[] = "(c.flag_ordinato IS NULL OR c.flag_ordinato = 0) AND (c.flag_rifiutato IS NULL OR c.flag_rifiutato = 0)";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Query per contare le visite (carrelli con data_creazione)
$query_visite = "SELECT COUNT(*) as totale FROM carrello c " . $where_clause;
$visite_result = get_data($query_visite);
$totale_visite = !empty($visite_result) ? $visite_result[0]['totale'] : 0;

// Query per contare gli ordini (carrelli con data_ordinazione e flag_conferma)
$where_ordini_conditions = ["c.data_ordinazione IS NOT NULL", "c.flag_conferma = 1"];
if (!empty($data_da)) {
    $where_ordini_conditions[] = "DATE(c.data_ordinazione) >= '" . $data_da . "'";
}
if (!empty($data_a)) {
    $where_ordini_conditions[] = "DATE(c.data_ordinazione) <= '" . $data_a . "'";
}
$where_ordini_clause = "WHERE " . implode(" AND ", $where_ordini_conditions);
$query_ordini = "SELECT COUNT(*) as totale FROM carrello c " . $where_ordini_clause;
$ordini_result = get_data($query_ordini);
$totale_ordini = !empty($ordini_result) ? $ordini_result[0]['totale'] : 0;

// Query per contare le visite di oggi
$data_oggi = date('Y-m-d');
$query_visite_oggi = "SELECT COUNT(*) as totale FROM carrello c WHERE DATE(c.data_creazione) = '$data_oggi'";
$visite_oggi_result = get_data($query_visite_oggi);
$totale_visite_oggi = !empty($visite_oggi_result) ? $visite_oggi_result[0]['totale'] : 0;

// Query per contare i confermati di oggi
$query_confermati_oggi = "SELECT COUNT(*) as totale FROM carrello c WHERE c.flag_conferma = 1 AND DATE(c.data_creazione) = '$data_oggi'";
$confermati_oggi_result = get_data($query_confermati_oggi);
$totale_confermati_oggi = !empty($confermati_oggi_result) ? $confermati_oggi_result[0]['totale'] : 0;

// Query per contare il totale dei record (per paginazione)
$query_count = "SELECT COUNT(*) as totale FROM carrello c " . $where_clause;
$count_result = get_data($query_count);
$totale_record = !empty($count_result) ? $count_result[0]['totale'] : 0;
$totale_pagine = ceil($totale_record / $per_pagina);

// Query per ottenere i carrelli con informazioni utente (con paginazione)
$query_carrelli = "SELECT c.*, uc.nome, uc.cognome, uc.email, uc.telefono
                    FROM carrello c 
                    LEFT JOIN utente_carrello uc ON c.id_carrello = uc.id_carrello 
                    " . $where_clause . " 
                    ORDER BY c.data_creazione DESC, c.id_carrello DESC
                    LIMIT $per_pagina OFFSET $offset";
$carrelli = get_data($query_carrelli);

// Calcola il totale per ogni carrello
foreach ($carrelli as &$carrello) {
    $id_carrello = $carrello['id_carrello'];
    $query_totale = "SELECT COALESCE(SUM(numero_prodotti * prezzo), 0) as totale 
                     FROM prodotticarrello 
                     WHERE id_carrello = '$id_carrello'";
    $totale_result = get_data($query_totale);
    $carrello['totale_calcolato'] = !empty($totale_result) && isset($totale_result[0]['totale']) 
                                     ? $totale_result[0]['totale'] : 0;
}
unset($carrello);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiche Carrelli - Gestionale Pizzeria</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="bootstrap-5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<!-- Loader -->
<div id="loader" class="d-none position-fixed top-0 start-0 w-100 h-100 bg-light bg-opacity-75 d-flex justify-content-center align-items-center" style="z-index: 1050;">   
   <div class="text-center">
       <div class="spinner-border" role="status">       
           <span class="visually-hidden">Loading...</span>   
       </div>       
       <p class="mt-2">Attendi<br>caricamento dati</p>
   </div>
</div>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Nirvana</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="ordini.php">
                        Ordini
                        <span class="badge bg-danger" id="badgeOrdini">0</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="gestione_prenotazioni.php">
                        Prenotazioni
                        <span class="badge bg-danger" id="badgePrenotazioni">0</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="utenti.html">Utenti</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link disabled">Comunicazioni</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="configurazioni.php">Configurazioni</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="statistiche_carrelli.php">Statistiche Carrelli</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Statistiche Carrelli</h4>
        </div>
        <div class="card-body">
            <!-- Box statistiche -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Visite Totali</h5>
                                    <h2 class="mb-0"><?php echo number_format($totale_visite); ?></h2>
                                    <small>Carrelli creati</small>
                                </div>
                                <div>
                                    <i class="bi bi-eye" style="font-size: 3rem; opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Ordini Totali</h5>
                                    <h2 class="mb-0"><?php echo number_format($totale_ordini); ?></h2>
                                    <small>Ordini completati</small>
                                </div>
                                <div>
                                    <i class="bi bi-cart-check" style="font-size: 3rem; opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Box statistiche di oggi -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Visite Oggi</h5>
                                    <h2 class="mb-0"><?php echo number_format($totale_visite_oggi); ?></h2>
                                    <small>Carrelli creati oggi</small>
                                </div>
                                <div>
                                    <i class="bi bi-calendar-day" style="font-size: 3rem; opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Confermati Oggi</h5>
                                    <h2 class="mb-0"><?php echo number_format($totale_confermati_oggi); ?></h2>
                                    <small>Ordini confermati oggi</small>
                                </div>
                                <div>
                                    <i class="bi bi-check-circle" style="font-size: 3rem; opacity: 0.5;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtri -->
            <div class="row mb-3">
                <div class="col-12">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="data_da" class="form-label">Data da:</label>
                            <input type="date" class="form-control" id="data_da" name="data_da" value="<?php echo htmlspecialchars($data_da); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="data_a" class="form-label">Data a:</label>
                            <input type="date" class="form-control" id="data_a" name="data_a" value="<?php echo htmlspecialchars($data_a); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="filtro_stato" class="form-label">Stato:</label>
                            <select class="form-select" id="filtro_stato" name="filtro_stato">
                                <option value="tutti" <?php echo $filtro_stato == 'tutti' ? 'selected' : ''; ?>>Tutti</option>
                                <option value="non_ordinati" <?php echo $filtro_stato == 'non_ordinati' ? 'selected' : ''; ?>>Non Ordinati</option>
                                <option value="ordinati" <?php echo $filtro_stato == 'ordinati' ? 'selected' : ''; ?>>Ordinati (non confermati)</option>
                                <option value="confermati" <?php echo $filtro_stato == 'confermati' ? 'selected' : ''; ?>>Confermati</option>
                                <option value="rifiutati" <?php echo $filtro_stato == 'rifiutati' ? 'selected' : ''; ?>>Rifiutati</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Filtra</button>
                            <?php if (!empty($data_da) || !empty($data_a) || $filtro_stato != 'tutti'): ?>
                                <a href="statistiche_carrelli.php" class="btn btn-secondary">Reset</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            

            <!-- Tabella carrelli -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Data Creazione</th>
                            <th scope="col">Data Ordinazione</th>
                            <th scope="col">Cliente / IP</th>
                            <th scope="col">ID Carrello</th>
                            <th scope="col">Totale</th>
                            <th scope="col">Stato</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($carrelli)): ?>
                            <tr>
                                <td colspan="6" class="text-center">Nessun carrello trovato</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($carrelli as $carrello): ?>
                                <tr>
                                    <td>
                                        <?php 
                                        if (!empty($carrello['data_creazione'])) {
                                            $data_creazione = new DateTime($carrello['data_creazione']);
                                            echo '<strong style="font-size: 1.1em; color: #000; padding: 4px 8px; border: 2px solid #dee2e6; border-radius: 4px; display: inline-block;">' . $data_creazione->format('d/m/Y') . '</strong>';
                                            echo '<br><small class="text-muted">' . $data_creazione->format('H:i') . '</small>';
                                        } else {
                                            echo '<span class="text-muted">N/A</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if (!empty($carrello['data_ordinazione'])) {
                                            $data_ordinazione = new DateTime($carrello['data_ordinazione']);
                                            echo '<strong class="text-success">' . $data_ordinazione->format('d/m/Y') . '</strong>';
                                            if (!empty($carrello['orario_ordinazione'])) {
                                                echo '<br><small class="text-muted">' . substr($carrello['orario_ordinazione'], 0, 5) . '</small>';
                                            }
                                        } else {
                                            echo '<span class="text-muted">-</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($carrello['nome']) || !empty($carrello['cognome'])): ?>
                                            <div>
                                                <strong><?php echo htmlspecialchars($carrello['nome'] . ' ' . $carrello['cognome']); ?></strong>
                                                <?php if (!empty($carrello['ip_macchina'])): ?>
                                                    <br><small class="text-secondary"><i class="bi bi-globe"></i> <?php echo htmlspecialchars($carrello['ip_macchina']); ?></small>
                                                <?php endif; ?>
                                                <?php if (!empty($carrello['email'])): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($carrello['email']); ?></small>
                                                <?php endif; ?>
                                                <?php if (!empty($carrello['telefono'])): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($carrello['telefono']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <?php if (!empty($carrello['ip_macchina'])): ?>
                                                <small class="text-secondary"><i class="bi bi-globe"></i> <?php echo htmlspecialchars($carrello['ip_macchina']); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong>#<?php echo htmlspecialchars($carrello['id_carrello']); ?></strong>
                                    </td>
                                    <td>
                                        <strong class="text-success" style="font-size: 1.1em;">
                                            €<?php 
                                            $totale = !empty($carrello['totale_calcolato']) ? $carrello['totale_calcolato'] : ($carrello['contoTotale'] ?? 0);
                                            echo number_format($totale, 2, ',', '.'); 
                                            ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <?php 
                                        $flag_ordinato = $carrello['flag_ordinato'] ?? 0;
                                        $flag_rifiutato = $carrello['flag_rifiutato'] ?? 0;
                                        $flag_conferma = $carrello['flag_conferma'] ?? 0;
                                        
                                        if ($flag_rifiutato == 1) {
                                            echo '<span class="badge bg-danger">Rifiutato</span>';
                                        } elseif ($flag_conferma == 1) {
                                            echo '<span class="badge bg-success">Confermato</span>';
                                        } elseif ($flag_ordinato == 1) {
                                            echo '<span class="badge bg-info">Ordinato</span>';
                                        } else {
                                            echo '<span class="badge bg-warning text-dark">Non Ordinato</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginazione -->
            <?php if ($totale_pagine > 1): ?>
                <nav aria-label="Paginazione">
                    <ul class="pagination justify-content-center mt-4">
                        <?php
                        // Costruisci i parametri per mantenere i filtri
                        $parametri = [];
                        if (!empty($data_da)) $parametri['data_da'] = $data_da;
                        if (!empty($data_a)) $parametri['data_a'] = $data_a;
                        if ($filtro_stato != 'tutti') $parametri['filtro_stato'] = $filtro_stato;
                        $query_string = !empty($parametri) ? '&' . http_build_query($parametri) : '';
                        
                        // Pulsante Precedente
                        if ($pagina > 1):
                        ?>
                            <li class="page-item">
                                <a class="page-link" href="?pagina=<?php echo $pagina - 1; ?><?php echo $query_string; ?>">Precedente</a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">Precedente</span>
                            </li>
                        <?php endif; ?>
                        
                        <?php
                        // Calcola il range di pagine da mostrare
                        $inizio = max(1, $pagina - 2);
                        $fine = min($totale_pagine, $pagina + 2);
                        
                        // Mostra prima pagina se non è nel range
                        if ($inizio > 1):
                        ?>
                            <li class="page-item">
                                <a class="page-link" href="?pagina=1<?php echo $query_string; ?>">1</a>
                            </li>
                            <?php if ($inizio > 2): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php
                        // Mostra le pagine nel range
                        for ($i = $inizio; $i <= $fine; $i++):
                        ?>
                            <li class="page-item <?php echo $i == $pagina ? 'active' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $i; ?><?php echo $query_string; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php
                        // Mostra ultima pagina se non è nel range
                        if ($fine < $totale_pagine):
                        ?>
                            <?php if ($fine < $totale_pagine - 1): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?pagina=<?php echo $totale_pagine; ?><?php echo $query_string; ?>"><?php echo $totale_pagine; ?></a>
                            </li>
                        <?php endif; ?>
                        
                        <?php
                        // Pulsante Successivo
                        if ($pagina < $totale_pagine):
                        ?>
                            <li class="page-item">
                                <a class="page-link" href="?pagina=<?php echo $pagina + 1; ?><?php echo $query_string; ?>">Successivo</a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">Successivo</span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
            
            <div class="mt-3 text-muted text-center">
                <small>
                    Visualizzati <strong><?php echo count($carrelli); ?></strong> di <strong><?php echo $totale_record; ?></strong> record totali
                    <?php if ($totale_pagine > 1): ?>
                        - Pagina <strong><?php echo $pagina; ?></strong> di <strong><?php echo $totale_pagine; ?></strong>
                    <?php endif; ?>
                </small>
            </div>
        </div>
    </div>
</div>

<script src="bootstrap-5.3.3/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    numeroConfermare();
    numeroPrenotazioniDaConfermare();
});

function numeroConfermare() {
    $.ajax({
        type: "POST",
        url: 'action.php?_action=numeroConfermare',
        dataType: 'json',
        success: function (result) {
            if (result.status === 1) {
                contaNuoviOrdini(result.data.numero);
            } else {
                console.error("Errore durante il caricamento degli ordini.");
            }
        },
        error: function (xhr, status, error) {
            console.error("Errore nella richiesta AJAX:", status, error);
        }
    });
}

function contaNuoviOrdini(count) {
    $("#badgeOrdini").text(count);
}

function numeroPrenotazioniDaConfermare() {
    $.ajax({
        type: "POST",
        url: 'action.php?_action=numeroPrenotazioniDaConfermare',
        dataType: 'json',
        success: function (result) {
            if (result.status === 1) {
                $("#badgePrenotazioni").text(result.data.numero);
            }
        },
        error: function (xhr, status, error) {
            console.error("Errore nella richiesta AJAX:", status, error);
        }
    });
}
</script>
</body>
</html>

