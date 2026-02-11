<?php 
include 'librerie/Database.php';
include 'librerie/metodi.php';


$query = "SELECT * FROM eventi ORDER BY id_eventi DESC ";

$eventi = get_data($query);

// print_r($eventi);

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
                    <a class="nav-link active text-dark"  aria-current="page" href="configurazioniEventi.php">Eventi</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="configurazioniCategorie.php">Categorie</a>
                </li>
                <!-- <li class="nav-item">
                    <a class="nav-link text-dark" aria-disabled="true">Disabled</a>
                </li> -->
            </ul>
            <div id="alertContainer" class="mt-3"></div>

            <div class="col ">
                <button class="btn btn-primary btn-sm" onclick="visualizzaEvento()">Nuovo evento</button>
            </div>

            <div class="table-responsive">
                <table class="table table-striped mt-3" id="tabellaUtenti">
                    <thead>
                        <tr>
                            <th scope="col">ID Eventi</th>
                            <th scope="col">Titolo</th>
                            <th scope="col">Descrizione</th>
                            <th scope="col">nome invitato</th>
                        </tr>
                    </thead>
                    <tbody id="elencoProdotti">
                        <?php
                        // Ciclo per iterare attraverso i prodotti e popolare la tabella
                        foreach ($eventi as $item) {
                            echo "<tr>";
                            echo "<td>" . $item['id_eventi'] . "</td>";
                            echo "<td>" . $item['titolo'] . "</td>";
                            echo "<td>" . $item['descrizione'] . "</td>";
                            echo "<td>" . $item['nome_invitato'] . "</td>";
                            echo "<td><button class='btn btn-primary' onclick='visualizzaEvento(" . $item['id_eventi'] . ")'>Visualizza</button></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody> 
                </table>
            </div>
    </div>
</div>

<!-- Modal per Visualizzare Utente -->
<div class="modal fade" id="modalVisualizzaEvento" tabindex="-1" aria-labelledby="modalVisualizzaEventoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVisualizzaEventoLabel">Dettagli evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
        <div class="modal-body">
            <form id="formEvento">
                <div class="row mb-3">
                    <div class="col">
                        <label for="titolo" class="form-label">Titolo</label>
                        <input type="text" class="form-control" id="titolo" placeholder="Inserisci il titolo">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="descrizione" class="form-label">Descrizione</label>
                    <textarea class="form-control" id="descrizione" rows="3" placeholder="Inserisci la descrizione"></textarea>
                </div>
                <div class="mb-3">
                    <label for="data_evento" class="form-label">Data evento</label>
                    <input type="date" class="form-control" id="data_evento">
                </div>
                <div class="mb-3">
                    <label for="nome_invitato" class="form-label">Nome invitato</label>
                    <input type="text" class="form-control" id="nome_invitato" placeholder="Inserisci il nome dell'invitato">
                </div>
                <div class="mb-3">
                    <label for="immagine" class="form-label">Carica immagine</label>
                    <input type="file" class="form-control" id="immagine" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label">Anteprima immagine</label>
                    <div id="anteprimaImmagine" style="border: 1px solid #ddd; padding: 10px; max-width: 300px;">
                        <img id="preview" src="" alt="Anteprima" style="max-width: 100%; display: none;">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-dark" onclick="salvaEvento()">Salva</button>
            <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Chiudi</button>
        </div>
        </div>
    </div>
</div>

<input type="hidden" id="idEvento" name="idEvento">
<script src="bootstrap-5.3.3/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    numeroConfermare();
});


function checkMessageAfterReload() {
    const message = localStorage.getItem('productMessage');
    const timestamp = localStorage.getItem('messageTimestamp');

    if ((message === 'success' || message === 'danger') && timestamp) {
        const currentTime = Date.now();
        const messageAge = currentTime - parseInt(timestamp);

        // Controlla se sono passati meno di 5 secondi
        if (messageAge < 5000) {

            if( message === 'success')
                 mostraAlertConferma("Evento salvato con successo", "success");
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


function visualizzaEvento(id) {
    // Mostra il modal quando si clicca sul bottone
    $("#modalVisualizzaEvento").modal('show');
    $("#idEvento").val(id);
    $.ajax({
        type: "POST",
        url: 'action.php?_action=visualizzaDettagliEvento',
        data: { id: id },
        dataType: 'json',
        success: function (result) {
            if (result.status === 1) {
                // Popola il modal con i dettagli dell'evento
                $("#id").val(result.data.dettaglio.id_eventi);
                $("#titolo").val(result.data.dettaglio.titolo);
                $("#descrizione").val(result.data.dettaglio.descrizione);
                $("#data_evento").val(result.data.dettaglio.data_evento);
                $("#nome_invitato").val(result.data.dettaglio.nome_invitato);

                // Gestione dell'immagine esistente
                if (result.data.dettaglio.immagine) {
                    const immaginePercorso = 'images/' + result.data.dettaglio.immagine;
                    $('#preview').attr('src', immaginePercorso);
                    $('#preview').show();
                    // Mantieni il riferimento all'immagine esistente
                    $("#immagine").attr('data-existing-image', result.data.dettaglio.immagine);
                } else {
                    $('#preview').hide();
                    $("#immagine").attr('data-existing-image', '');
                }
            }
        }
    });
}

function impostaCategoria(categoria) {
    // Rimuovi eventuali spazi extra e normalizza
    categoria = categoria.trim();
    
    // Log per debug
    $(`#categoria option[value="${categoria}"]`).prop('selected', true);

}

function salvaEvento() {
    var idEvento = $("#idEvento").val();
    var titolo = $("#titolo").val();
    var descrizione = $("#descrizione").val();
    var data_evento = $("#data_evento").val();
    var nome_invitato = $("#nome_invitato").val();
    var immagine = $("#immagine")[0].files[0];
    $("#modalVisualizzaEvento").modal('hide');
    $("#loader").removeClass("d-none");

    // var startTime = performance.now();

    if (immagine) {
        var formData = new FormData();
        formData.append('immagine', immagine);
        formData.append('nomeFile', immagine.name);

        $.ajax({
            type: "POST",
            url: 'action.php?_action=caricaImmagineEvento',
            data: formData,
            processData: false, // Necessario per il FormData
            contentType: false, // Necessario per il FormData
            success: function (result) {
                // var endTime = performance.now();
                //     var caricamentoSecondi = ((endTime - startTime) / 1000).toFixed(2);
                    
                var jsonResponse = JSON.parse(result);

                if (jsonResponse.status === 1) {
                    // alert(`Immagine caricata in ${caricamentoSecondi} secondi`);

                    $("#loader").addClass("d-none");
                    salvaDatiEvento(idEvento, titolo, descrizione, data_evento, nome_invitato, jsonResponse.nomeImmagine);
                } else {
                    alert('Errore durante il caricamento dell\'immagine.');
                }
            },
            error: function (xhr, status, error) {
                console.error("Errore nella richiesta AJAX:", status, error);
                alert("Errore durante il caricamento dell'immagine.");
            }
        });
    } else {
        salvaDatiEvento(idEvento, titolo, descrizione, data_evento, nome_invitato, null);
    }
}

function salvaDatiEvento(idEvento, titolo, descrizione, data_evento, nome_invitato, nomeImmagine) {
    $.ajax({
        type: "POST",
        url: 'action.php?_action=salvaDatiEvento',
        data: {
            id: idEvento,
            titolo: titolo,
            descrizione: descrizione,
            data_evento: data_evento,
            nome_invitato: nome_invitato,
            immagine: nomeImmagine
        },
        success: function (result) {
            if (result.status === 1) {
                localStorage.setItem('productMessage', 'success');
                localStorage.setItem('messageTimestamp', Date.now());
                location.reload(); // Ricarica la pagina immediatamente
                $("#modalVisualizzaEvento").modal('hide');
                mostraAlertConferma("Evento salvato con successo", "success");
            } else {
                alert('Errore durante il salvataggio dell\'evento.');
            }
        },
        error: function (xhr, status, error) {
            console.error("Errore nella richiesta AJAX:", status, error);
            alert("Errore nel salvataggio dell'evento.");
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


</script>
</body>
</html>
