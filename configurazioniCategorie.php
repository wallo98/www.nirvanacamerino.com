<?php 
include 'librerie/Database.php';
include 'librerie/metodi.php';

// Ottieni tutte le categorie dal database
$query = "SELECT * FROM categoria ORDER BY descrizione";
$categorie = get_data($query);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Categorie</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="bootstrap-5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Navbar</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="ordini.php">
                        Ordini
                        <span class="badge bg-danger" id="badgeOrdini">0</span> <!-- Badge per il conteggio delle notifiche -->
                    </a>
                </li>
                <li class="nav-item ">
                    <a class="nav-link" aria-current="page" href="utenti.html">Utenti</a>
                </li>
                <li class="nav-item ">
                    <a class="nav-link" href="Comunicazioni.html">Comunicazioni</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active " href="configurazioni.php">Configurazioni</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            Elenco prodotti
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link text-dark" href="configurazioni.php">Prodotti</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark"  aria-current="page" href="configurazioniEventi.php">Eventi</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active text-dark" href="configurazioniCategorie.php">Categorie</a>
                </li>
            </ul>
            <div id="alertContainer" class="mt-3"></div>

            <div class="row mb-3">
                <form id="formCategoria" class="d-flex gap-2" onsubmit="aggiungiCategoria()">
                    <input type="text" id="nuovaCategoria" class="form-control" placeholder="Inserisci nuova categoria" required>
                    <button type="submit" class="btn btn-primary">Aggiungi</button>
                </form>
            </div>

           <!-- Elenco categorie -->
           <table class="table table-striped mt-3">
                <thead>
                    <tr>
                        <th>Categoria</th>
                    </tr>
                </thead>
                <tbody id="elencoCategorie">
                    <?php foreach ($categorie as $categoria): ?>
                        <tr>
                            <td><?= htmlspecialchars($categoria['descrizione']) ?></td>
                            <!-- <td>
                                <button class="btn btn-danger btn-sm" onclick="eliminaCategoria(<?= $categoria['id_categoria'] ?>)">
                                    <i class="bi bi-trash"></i> Elimina
                                </button>
                            </td> -->
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>
</div>





<script>
// Funzione per aggiungere una nuova categoria
function aggiungiCategoria() {
    const nuovaCategoria = $('#nuovaCategoria').val();

    $.ajax({
        url: 'action.php?_action=salvaCategoria',
        method: 'POST',
        data: { descrizione: nuovaCategoria },
        success: function(response) {
            if (response.status === 1) {
                location.reload();
            } else {
                alert('Errore durante l\'aggiunta della categoria.');
            }
        }
    });
}


// Funzione per eliminare una categoria
function eliminaCategoria(idCategoria) {
    if (confirm('Sei sicuro di voler eliminare questa categoria?')) {
        $.ajax({
            url: 'gestioneCategorie.php?action=delete',
            method: 'POST',
            data: { id_categoria: idCategoria },
            success: function(response) {
                const data = JSON.parse(response);
                if (data.success) {
                    // Rimuovi la riga corrispondente
                    $(`button[onclick="eliminaCategoria(${idCategoria})"]`).closest('tr').remove();
                } else {
                    alert('Errore durante l\'eliminazione della categoria.');
                }
            }
        });
    }
}
</script>

<script src="bootstrap-5.3.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
