<?php 
include 'librerie/Database.php';
include 'librerie/metodi.php';

$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';
// Costruisci la query in base alla categoria selezionata
if (!empty($categoria)) {
    $query = "SELECT * FROM prodotto WHERE categoria = '" .$categoria. "' AND visibile = 1";
} else {
    $query = "SELECT * FROM prodotto WHERE visibile = 1";
}
$prodotti = get_data($query);

$categoriaSQL="SELECT * FROM categoria";
$Arraycategoria=get_data($categoriaSQL);


?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionale Pizzeria</title>

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
                    <a class="nav-link active" href="configurazioni.php">Configurazioni</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="statistiche_carrelli.php">Statistiche Carrelli</a>
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
                    <a class="nav-link active" aria-current="page" href="configurazioni.php">Prodotti</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  text-dark" href="configurazioniEventi.php">Eventi</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  text-dark" href="configurazioniCategorie.php">Categorie</a>
                </li>
            </ul>
        <div id="alertContainer" class="mt-3"></div>

        <div class="row mb-3">
    <div class="d-flex justify-content-between align-items-end">
        <form id="filterForm" method="GET" class="d-flex align-items-end gap-2">
            <div class="flex-grow-1">
                <label for="categoria" class="form-label">Filtra per categoria:</label>
                <select class="form-select" id="categoria" name="categoria">
                    <option value="">Tutte le categorie</option>
                    <?php
                    // Query per ottenere le categorie uniche
                    // $categorie = get_data("SELECT DISTINCT categoria FROM prodotto ORDER BY categoria");
                    foreach ($Arraycategoria as $cat) {
                        $selected = ($categoria == $cat['descrizione']) ? 'selected' : '';
                        echo "<option value='" . $cat['descrizione'] . "' $selected>" . $cat['descrizione'] . "</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtra</button>
            <?php if (!empty($categoria)): ?>
                <a href="?" class="btn btn-secondary">Reset</a>
            <?php endif; ?>
        </form>

        <!-- Bottone Visualizza fuori dal form -->
        <div class="ms-3">
            <button class="btn btn-primary" onclick="visualizzaProdotto()" type="button">Nuovo prodotto</button>
        </div>
    </div>
</div>

        <div class="table-responsive">
            <table class="table table-striped mt-3" id="tabellaUtenti">
                <thead>
                    <tr>
                        <th scope="col">ID prodotto</th>
                        <th scope="col">Titolo</th>
                    <th scope="col">Descrizione</th>
                    <th scope="col">Prezzo</th>
                    <th scope="col">Categoria</th>
                    <th scope="col"></th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody id="elencoProdotti">
                <?php
                // Ciclo per iterare attraverso i prodotti e popolare la tabella
                foreach ($prodotti as $item) {
                    echo "<tr>";
                    echo "<td>" . $item['id'] . "</td>";
                    echo "<td>" . $item['titolo'] . "</td>";
                    echo "<td>" . $item['descrizione'] . "</td>";
                    echo "<td>€" . $item['prezzo'] . "</td>";
                    echo "<td>" . $item['categoria'] . "</td>";
                    echo "<td><button class='btn btn-primary' onclick='visualizzaProdotto(" . $item['id'] . ")'>Visualizza</button></td>";
                    echo "<td><button class='btn btn-danger' onclick='apriModalEliminaOrdine(" . $item['id'] . ")'>Elimina</button></td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        </div>


        </div>
    </div>
</div>

<!-- Modal per Visualizzare Utente -->
<div class="modal fade" id="modalVisualizzaUtente" tabindex="-1" aria-labelledby="modalVisualizzaUtenteLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalVisualizzaUtenteLabel">Dettagli prodotto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formProdotto" class="container-fluid">
                    <div class="row g-4">
                        <!-- Prima riga con titolo e prezzo -->
                        <div class="col-md-8">
                            <label for="titolo" class="form-label">Titolo</label>
                            <input type="text" class="form-control" id="titolo" placeholder="Inserisci il titolo">
                        </div>
                        <div class="col-md-4">
                            <label for="prezzo" class="form-label">Prezzo</label>
                            <input type="text" class="form-control" id="prezzo" placeholder="Inserisci il prezzo">
                        </div>

                        <!-- Seconda riga con descrizione e categoria -->
                        <div class="col-md-8">
                            <label for="descrizione" class="form-label">Descrizione</label>
                            <textarea class="form-control" id="descrizione" rows="3" placeholder="Inserisci descrizione"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label for="categoriaModal" class="form-label">Categoria</label>
                            <select class="form-control" id="categoriaModal">
                                <?php 
                                    foreach ($Arraycategoria as $cat) {
                                    echo "<option value='" . $cat['descrizione'] . "' $selected>" . $cat['descrizione'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Terza riga con gestione immagine -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <label for="immagine" class="form-label">Carica Immagine</label>
                                    <input type="file" class="form-control" id="immagine" accept="image/*">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <!-- Div per l'anteprima dell'immagine -->
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Anteprima Immagine</h6>
                                    <div id="anteprimaImmagine" class="text-center">
                                        <img id="preview" src="" alt="Nessuna immagine selezionata" 
                                             style="max-width: 100%; max-height: 300px; object-fit: contain;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-dark btn-lg" onclick="salvaProdotto()">Salva</button>
                <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">Chiudi</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal di conferma per Elimina Ordine -->
<div class="modal fade" id="modalEliminaProdotto" tabindex="-1" aria-labelledby="modalEliminaProdottoLabel" aria-hidden="true">
  <div class="modal-dialog  modal-lg"> <!-- Aggiungi "custom-modal" -->
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="modalEliminaProdottoLabel">Elimina Ordine</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              Sei sicuro di voler eliminare l'ordine?
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
              <button type="button" class="btn btn-danger" onclick="eliminaProdotto()">Sì, elimina</button>
          </div>
      </div>
  </div>
</div>

<input type="hidden" id="idProdotto" name="idProdotto">
<input type="hidden" id="id_prodotto" name="id_prodotto" value="">
<script src="bootstrap-5.3.3/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    numeroConfermare();
    numeroPrenotazioniDaConfermare();
});

// Funzione da chiamare all'avvio della pagina per controllare i messaggi
function checkMessageAfterReload() {
    const message = localStorage.getItem('productMessage');
    const timestamp = localStorage.getItem('messageTimestamp');

    if ((message === 'success' || message === 'danger') && timestamp) {
        const currentTime = Date.now();
        const messageAge = currentTime - parseInt(timestamp);

        // Controlla se sono passati meno di 5 secondi
        if (messageAge < 5000) {

            if( message === 'success')
                 mostraAlertConferma("Prodotto salvato con successo", "success");
            else 
                 mostraAlertConferma("Prodotto eliminato con successo", "danger");


            // Rimuove il messaggio dopo 5 secondi dalla creazione
            setTimeout(() => {
                localStorage.removeItem('productMessage');
                localStorage.removeItem('messageTimestamp');
            }, 5000 - messageAge);
        } else {
            // Se sono già passati più di 5 secondi, rimuove subito il messaggio
            localStorage.removeItem('productMessage');
            localStorage.removeItem('messageTimestamp');
        }
    }
}
// Aggiungi questo listener che viene eseguito quando la pagina è completamente caricata
document.addEventListener('DOMContentLoaded', checkMessageAfterReload);



document.addEventListener('DOMContentLoaded', function() {
    // Gestione del caricamento dell'immagine
    document.getElementById('immagine').addEventListener('change', function() {
        const preview = document.getElementById('preview');
        const file = this.files[0];

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            reader.readAsDataURL(file);
        } else {
            preview.src = '';
        }
    });
});

function visualizzaProdotto(id) {
    // Reset dei campi
    $("#id").val('');
    $("#titolo").val('');
    $("#descrizione").val('');
    $("#prezzo").val('');
    $("#immagine").val('');
    $("#immagine").attr('data-existing-image', '');
    $('#preview').attr('src', '');
    $("#categoriaModal").val('');
    
    // Mostra il modal
    $("#modalVisualizzaUtente").modal('show');
    $("#idProdotto").val(id);
    
    $.ajax({
        type: "POST",
        url: 'action.php?_action=visualizzaDettagliProdotto',
        data: { id: id },
        dataType: 'json',
        success: function (result) {
            if (result.status === 1) {
                $("#id").val(result.data.dettaglio.id);
                $("#titolo").val(result.data.dettaglio.titolo);
                $("#descrizione").val(result.data.dettaglio.descrizione);
                $("#prezzo").val(result.data.dettaglio.prezzo);
                
                if (result.data.dettaglio.immagine) {
                    const immaginePercorso = 'images/' + result.data.dettaglio.immagine;
                    $('#preview').attr('src', immaginePercorso);
                    $("#immagine").attr('data-existing-image', result.data.dettaglio.immagine);
                }
                
                impostaCategoria(result.data.dettaglio.categoria);
            }
        }
    });
}
function impostaCategoria(categoria) {
// Rimuovi eventuali spazi extra e normalizza
    categoria = categoria.trim();
    
    // Log per debug
    $(`#categoriaModal option[value="${categoria}"]`).prop('selected', true);

}

function nuovoProdotto() {
// Rimuovi eventuali spazi extra e normalizza
    $("#modalVisualizzaUtente").modal('show');
    var titolo = $("#titolo").val();
    var descrizione = $("#descrizione").val();
    var prezzo = $("#prezzo").val();
    var categoria = $("#categoriaModal").val(); // Ottieni il valore della select della categoria
    var immagine = $("#immagine")[0].files[0];  // Ottieni il file immagine selezionato

}


function salvaProdotto() {
    var idProdotto = $("#idProdotto").val();
    var titolo = $("#titolo").val();
    var descrizione = $("#descrizione").val();
    var prezzo = $("#prezzo").val();
    var categoria = $("#categoriaModal").val(); // Ottieni il valore della select della categoria
    var immagine = $("#immagine")[0].files[0];  // Ottieni il file immagine selezionato
    
    $("#modalVisualizzaUtente").modal('hide');

    $("#loader").removeClass("d-none");

    if (immagine) {
        var reader = new FileReader();

        // Leggi il file immagine selezionato
        reader.readAsDataURL(immagine);

        reader.onload = function (e) {
            var base64Image = e.target.result;

            // Rimuovi gli spazi dal nome del file
            var nomeFileSenzaSpazi = immagine.name.replace(/\s+/g, '');
            // alert(nomeFileSenzaSpazi);

            // Invia l'immagine al server come stringa base64
            $.ajax({
                type: "POST",
                url: 'action.php?_action=caricaImmagine',  // URL per caricare l'immagine
                data: {
                    immagine: base64Image,
                    nomeFile: nomeFileSenzaSpazi
                },
                success: function (result) {
                    var jsonResponse = JSON.parse(result); // Parsing della risposta

                    if (jsonResponse.status === 1) {
                        alert("Immagine salvata con successo!");
                        salvaDatiProdotto(idProdotto, titolo, descrizione, prezzo, jsonResponse.nomeImmagine, categoria);
                    } else {
                        alert('Errore durante il caricamento dell\'immagine.');
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Errore nella richiesta AJAX:", status, error);
                    alert("Errore durante il caricamento dell'immagine.");
                }
            });
        };
    } else {
        // Se non c'è immagine, inviamo solo i dati del prodotto
        salvaDatiProdotto(idProdotto, titolo, descrizione, prezzo, null, categoria);
    }
}


function salvaDatiProdotto(idProdotto, titolo, descrizione, prezzo, nomeImmagine, categoria) {
    $.ajax({
        type: "POST",
        url: 'action.php?_action=salvaDatiProdotto',
        data: {
            id: idProdotto,
            titolo: titolo,
            descrizione: descrizione,
            prezzo: prezzo,
            categoria: categoria,
            immagine: nomeImmagine
        },
        success: function (result) {
            if (result.status === 1) {
                // Salva il messaggio e il timestamp nel localStorage
                localStorage.setItem('productMessage', 'success');
                localStorage.setItem('messageTimestamp', Date.now());
                $("#modalVisualizzaUtente").modal('hide');
                location.reload();
            } else {
                alert('Errore durante il salvataggio del prodotto.');
            }
        },
        error: function (xhr, status, error) {
            console.error("Errore nella richiesta AJAX:", status, error);
            alert("Errore nel salvataggio del prodotto.");
        }
    });
}



function numeroConfermare() {
    $.ajax({
        type: "POST",
        url: 'action.php?_action=numeroConfermare',
        dataType: 'json',
        success: function (result) {
            if (result.status === 1) {
              contaNuoviOrdini(result.data.numero)
            } else {
                alert("Errore durante il caricamento degli ordini.");
            }
        },
        error: function (xhr, status, error) {
            console.error("Errore nella richiesta AJAX:", status, error);
            alert("Si è verificato un errore. Riprova più tardi.");
        }
    });
}

function contaNuoviOrdini(count) {
    // Aggiorna il contenuto del badge
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

function mostraAlertConferma(message, type) {
    // Crea l'alert
    let alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Aggiungi l'alert al DOM
    $("#alertContainer").append(alertHtml);
    
    $('html, body').animate({
        scrollTop: $("#containerMain").offset().top
    }, 500); // Tempo di animazione in millisecondi
    
    // Rimuovi l'alert dopo 3 secondi
    setTimeout(() => {
        $(".alert").alert('close');
    }, 10000);
}


function apriModalEliminaOrdine(id) {
    $("#modalEliminaProdotto").modal('show');
    $("#id_prodotto").val(id);

}


function eliminaProdotto() {
    // Ottieni l'ID del carrello dall'input hidden
    let id_prodotto = $("#id_prodotto").val();

    $("#modalEliminaProdotto").modal('hide');

       $.ajax({
        type: "POST",
        url: 'action.php?_action=eliminaProdottoMenu',
        data: { id_prodotto: id_prodotto }, // Usa l'ID del carrello
        dataType: 'json',
        success: function (result) {
            
            if (result == 1) {
                // Nascondi entrambi i modali
                localStorage.setItem('productMessage', 'danger');
                localStorage.setItem('messageTimestamp', Date.now());
                location.reload();

                // Richiama la funzione per aggiornare l'elenco ordini
                numeroConfermare();

                // Mostra un alert di Bootstrap per la conferma

            } else {
                alert("Errore nella conferma dell'ordine: " + result.message);
            }
        },
        error: function (xhr, status, error) {
            // Nascondi il loader
            $("#loader").addClass("d-none");
            
            console.error("Errore nella richiesta AJAX:", status, error);
            alert("Si è verificato un errore. Riprova più tardi.");
        }
    });
    
}


</script>
</body>
</html>
