<?php
session_start();

include 'librerie/Database.php';
include 'librerie/metodi.php';

$db = new Database();

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Prenotazioni - Nirvana</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="bootstrap-5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }

      .b-example-divider {
        width: 100%;
        height: 3rem;
        background-color: rgba(0, 0, 0, .1);
        border: solid rgba(0, 0, 0, .15);
        border-width: 1px 0;
        box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
      }

      .nav-scroller {
        position: relative;
        z-index: 2;
        height: 2.75rem;
        overflow-y: hidden;
      }

      .nav-scroller .nav {
        display: flex;
        flex-wrap: nowrap;
        padding-bottom: 1rem;
        margin-top: -1px;
        overflow-x: auto;
        text-align: center;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
      }
    </style>
</head>
<body>
  <!-- Loader -->
<div id="loader" class="d-none position-fixed top-0 start-0 w-100 h-100 bg-light bg-opacity-75 d-flex justify-content-center align-items-center" style="z-index: 1050;">
  <div class="spinner-border" role="status">
      <span class="visually-hidden">Loading...</span>
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
                    <a class="nav-link active" aria-current="page" href="gestione_prenotazioni.php">
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
                    <a class="nav-link" href="statistiche_carrelli.php">Statistiche Carrelli</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4" id="containerMain">
    <div class="card">
        <div class="card-header">
            Elenco Prenotazioni
        </div>
        <div class="card-body">
            <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                <input type="radio" class="btn-check" name="btnradio" id="btnradio1" autocomplete="off" checked>
                <label class="btn btn-outline-dark" for="btnradio1">Non Confermati</label>

                <input type="radio" class="btn-check" name="btnradio" id="btnradio2" autocomplete="off">
                <label class="btn btn-outline-dark" for="btnradio2">Confermati</label>
            </div>
            <div id="alertContainer" class="mt-3"></div>

            <div class="table-responsive">
              <table class="table table-responsive table-striped mt-3" id="tabellaNonConfermati">
                  <thead>
                      <tr>
                          <th scope="col">#</th>
                          <th scope="col">Nome</th>
                          <th scope="col">Cognome</th>
                          <th scope="col">Telefono</th>
                          <th scope="col">Data</th>
                          <th scope="col">Ora</th>
                          <th scope="col">Persone</th>
                          <th scope="col">Dettaglio</th>
                      </tr>
                  </thead>
                  <tbody id="elencoPrenotazioni">
                      <!-- I dati delle prenotazioni non confermate verranno inseriti qui -->
                  </tbody>
              </table>
            </div>

            <table class="table table-striped mt-3 d-none" id="tabellaConfermati">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nome</th>
                        <th scope="col">Cognome</th>
                        <th scope="col">Telefono</th>
                        <th scope="col">Data</th>
                        <th scope="col">Ora</th>
                        <th scope="col">Persone</th>
                        <th scope="col">Dettaglio</th>
                    </tr>
                </thead>
                <tbody id="elencoPrenotazioniConfermati">
                    <!-- I dati delle prenotazioni confermate verranno inseriti qui -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal per i dettagli della prenotazione -->
<div class="modal fade" id="modalDettagli" tabindex="-1" aria-labelledby="modalDettagliLabel" aria-hidden="true">
  <div class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="modalDettagliLabel">Dettagli Prenotazione</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="formDettagli" class="m-3">
              
                <div class="row mb-3">
                    <!-- Nome -->
                    <div class="col-md-6">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" readonly>
                    </div>
                    
                    <!-- Cognome -->
                    <div class="col-md-6">
                        <label for="cognome" class="form-label">Cognome</label>
                        <input type="text" class="form-control" id="cognome" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <!-- Telefono -->
                    <div class="col-md-6">
                        <label for="telefono" class="form-label">Telefono</label>
                        <input type="text" class="form-control" id="telefono" readonly>
                    </div>
                    
                    <!-- Email -->
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <!-- Data -->
                    <div class="col-md-4">
                        <label for="data" class="form-label">Data</label>
                        <input type="text" class="form-control" id="data" readonly>
                    </div>
                    
                    <!-- Ora -->
                    <div class="col-md-4">
                        <label for="ora" class="form-label">Ora</label>
                        <input type="text" class="form-control" id="ora" readonly>
                    </div>

                    <!-- Numero Persone -->
                    <div class="col-md-4">
                        <label for="numero_persone" class="form-label">Persone</label>
                        <input type="text" class="form-control" id="numero_persone" readonly>
                    </div>
                </div>

                <!-- Note -->
                <div class="mb-3">
                    <label for="note" class="form-label">Note</label>
                    <textarea class="form-control" id="note" rows="3" readonly></textarea>
                </div>

                <!-- Data Creazione -->
                <div class="mb-3">
                    <label for="data_creazione" class="form-label">Data Creazione Prenotazione</label>
                    <input type="text" class="form-control" id="data_creazione" readonly>
                </div>

                <input type="hidden" id="id_prenotazione" value="">
              </form>
        </div>

          <div class="modal-footer d-flex justify-content-center">
            <button type="button" class="btn btn-warning me-2" onclick="apriModalRifiutaPrenotazione()">Rifiuta</button>
            <button type="button" class="btn btn-danger me-2" onclick="apriModalEliminaPrenotazione()">Elimina</button>
            <button type="button" class="btn btn-success" onclick="apriModalConfermaPrenotazione()">Conferma</button>
        </div>
      </div>
  </div>
</div>

<!-- Modal di conferma per Conferma Prenotazione -->
<div class="modal fade" id="modalConfermaPrenotazione" tabindex="-1" aria-labelledby="modalConfermaPrenotazioneLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="modalConfermaPrenotazioneLabel">Conferma Prenotazione</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              Sei sicuro di voler confermare la prenotazione?
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
              <button type="button" class="btn btn-success" onclick="confermaPrenotazione()">Sì, conferma</button>
          </div>
      </div>
  </div>
</div>

<!-- Modal di conferma per Elimina Prenotazione -->
<div class="modal fade" id="modalEliminaPrenotazione" tabindex="-1" aria-labelledby="modalEliminaPrenotazioneLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="modalEliminaPrenotazioneLabel">Elimina Prenotazione</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              Sei sicuro di voler eliminare la prenotazione?
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
              <button type="button" class="btn btn-danger" onclick="eliminaPrenotazione()">Sì, elimina</button>
          </div>
      </div>
  </div>
</div>

<!-- Modal di conferma per Rifiuta Prenotazione -->
<div class="modal fade" id="modalRifiutaPrenotazione" tabindex="-1" aria-labelledby="modalRifiutaPrenotazioneLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="modalRifiutaPrenotazioneLabel">Rifiuta Prenotazione</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              Sei sicuro di voler rifiutare la prenotazione? Questa azione eliminerà la prenotazione.
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
              <button type="button" class="btn btn-warning" onclick="rifiutaPrenotazione()">Sì, rifiuta</button>
          </div>
      </div>
  </div>
</div>

<!-- Modal per i dettagli della prenotazione CONFERMATA (solo visualizzazione) -->
<div class="modal fade" id="modalDettagliConfermato" tabindex="-1" aria-labelledby="modalDettagliConfermatoLabel" aria-hidden="true">
  <div class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="modalDettagliConfermatoLabel">Dettagli Prenotazione Confermata</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="formDettagliConfe" class="m-3">
              
                <div class="row mb-3">
                    <!-- Nome -->
                    <div class="col-md-6">
                        <label for="nomeConfe" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nomeConfe" readonly>
                    </div>
                    
                    <!-- Cognome -->
                    <div class="col-md-6">
                        <label for="cognomeConfe" class="form-label">Cognome</label>
                        <input type="text" class="form-control" id="cognomeConfe" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <!-- Telefono -->
                    <div class="col-md-6">
                        <label for="telefonoConfe" class="form-label">Telefono</label>
                        <input type="text" class="form-control" id="telefonoConfe" readonly>
                    </div>
                    
                    <!-- Email -->
                    <div class="col-md-6">
                        <label for="emailConfe" class="form-label">Email</label>
                        <input type="email" class="form-control" id="emailConfe" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <!-- Data -->
                    <div class="col-md-4">
                        <label for="dataConfe" class="form-label">Data</label>
                        <input type="text" class="form-control" id="dataConfe" readonly>
                    </div>
                    
                    <!-- Ora -->
                    <div class="col-md-4">
                        <label for="oraConfe" class="form-label">Ora</label>
                        <input type="text" class="form-control" id="oraConfe" readonly>
                    </div>

                    <!-- Numero Persone -->
                    <div class="col-md-4">
                        <label for="numero_personeConfe" class="form-label">Persone</label>
                        <input type="text" class="form-control" id="numero_personeConfe" readonly>
                    </div>
                </div>

                <!-- Note -->
                <div class="mb-3">
                    <label for="noteConfe" class="form-label">Note</label>
                    <textarea class="form-control" id="noteConfe" rows="3" readonly></textarea>
                </div>

                <!-- Data Creazione -->
                <div class="mb-3">
                    <label for="data_creazioneConfe" class="form-label">Data Creazione Prenotazione</label>
                    <input type="text" class="form-control" id="data_creazioneConfe" readonly>
                </div>

                <!-- Data Conferma -->
                <div class="mb-3">
                    <label for="data_modificaConfe" class="form-label">Data Conferma</label>
                    <input type="text" class="form-control" id="data_modificaConfe" readonly>
                </div>

              </form>
        </div>
      </div>
  </div>
</div>

<div class="container"></div>
  <footer class="py-3 my-4">
    <ul class="nav justify-content-center border-bottom pb-3 mb-3">
      <li class="nav-item"><a href="index.html" class="nav-link px-2 text-body-secondary">Home</a></li>
      <li class="nav-item"><a href="menu.php" class="nav-link px-2 text-body-secondary">Menu</a></li>
      <li class="nav-item"><a href="blog.php" class="nav-link px-2 text-body-secondary">Eventi</a></li>
      <li class="nav-item"><a href="contatti.html" class="nav-link px-2 text-body-secondary">Contatti</a></li>
    </ul>
    <p class="text-center text-body-secondary">&copy; 2024 Nirvana Pub Pizzeria</p>
  </footer>
</div>

<script src="bootstrap-5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
  $(document).ready(function() {
        elencoPrenotazioni();
        elencoPrenotazioniConfermate();
        numeroPrenotazioniDaConfermare();
        numeroConfermare();

        $("#btnradio1").change(function() {
            $("#tabellaNonConfermati").removeClass("d-none");
            $("#tabellaConfermati").addClass("d-none");
        });

        $("#btnradio2").change(function() {
            $("#tabellaNonConfermati").addClass("d-none");
            $("#tabellaConfermati").removeClass("d-none");
        });
    });

function elencoPrenotazioni() {
    $.ajax({
        type: "POST",
        url: 'action.php?_action=elencoPrenotazioni',
        dataType: 'json',
        success: function (result) {
            if (result.status === 1) {
                const elencoPrenotazioni = $("#elencoPrenotazioni");
                elencoPrenotazioni.empty();
                counter = 0;
                result.data.elencoPrenotazioni.forEach(prenotazione => {
                    counter++;
                    const prenotazioneRow = `
                        <tr>
                            <td>${counter}</td>
                            <td>${prenotazione.nome}</td>
                            <td>${prenotazione.cognome}</td>
                            <td>${prenotazione.telefono}</td>
                            <td>${prenotazione.data_prenotazione}</td>
                            <td>${prenotazione.ora_prenotazione}</td>
                            <td>
                                <span class="badge bg-primary" style="font-size: 1.1em; padding: 8px 12px;">
                                    <i class="bi bi-people-fill"></i> ${prenotazione.numero_persone} 
                                    ${prenotazione.numero_persone == 1 ? 'persona' : 'persone'}
                                </span>
                            </td>
                            <td><button class="btn btn-success btn-sm" onclick="visualizzaDettagli(${prenotazione.id_prenotazione})">Apri prenotazione</button></td>
                        </tr>
                    `;
                    elencoPrenotazioni.append(prenotazioneRow);
                });
            } else {
                $("#elencoPrenotazioni").html("<tr><td colspan='8' class='text-center'>Nessuna prenotazione da confermare</td></tr>");
            }
        },
        error: function (xhr, status, error) {
            console.error("Errore nella richiesta AJAX:", status, error);
            alert("Si è verificato un errore. Riprova più tardi.");
        }
    });
}

function elencoPrenotazioniConfermate() {
    $.ajax({
        type: "POST",
        url: 'action.php?_action=elencoPrenotazioniConfermate',
        dataType: 'json',
        success: function (result) {
            if (result.status === 1) {
                const elencoPrenotazioniConfermati = $("#elencoPrenotazioniConfermati");
                elencoPrenotazioniConfermati.empty();
                counter = 0;
                result.data.elencoPrenotazioniConfermate.forEach(prenotazione => {
                    counter++;
                    const prenotazioneRow = `
                        <tr>
                            <td>${counter}</td>
                            <td>${prenotazione.nome}</td>
                            <td>${prenotazione.cognome}</td>
                            <td>${prenotazione.telefono}</td>
                            <td>${prenotazione.data_prenotazione}</td>
                            <td>${prenotazione.ora_prenotazione}</td>
                            <td>
                                <span class="badge bg-success" style="font-size: 1.1em; padding: 8px 12px;">
                                    <i class="bi bi-people-fill"></i> ${prenotazione.numero_persone} 
                                    ${prenotazione.numero_persone == 1 ? 'persona' : 'persone'}
                                </span>
                            </td>
                            <td><button class="btn btn-info btn-sm" onclick="visualizzaDettagliConfermato(${prenotazione.id_prenotazione})">Visualizza</button></td>
                        </tr>
                    `;
                    elencoPrenotazioniConfermati.append(prenotazioneRow);
                });
            } else {
                $("#elencoPrenotazioniConfermati").html("<tr><td colspan='8' class='text-center'>Nessuna prenotazione confermata</td></tr>");
            }
        },
        error: function (xhr, status, error) {
            console.error("Errore nella richiesta AJAX:", status, error);
            alert("Si è verificato un errore. Riprova più tardi.");
        }
    });
}

function visualizzaDettagli(id_prenotazione) {
    $.ajax({
        type: "POST",
        url: 'action.php?_action=visualizzaDettagliPrenotazione',
        data: { id_prenotazione: id_prenotazione },
        dataType: 'json',
        success: function (result) {
            if (result.status === 1) {
                const dettaglio = result.data.dettaglio;
                $("#nome").val(dettaglio.nome);
                $("#cognome").val(dettaglio.cognome);
                $("#telefono").val(dettaglio.telefono);
                $("#email").val(dettaglio.email);
                $("#data").val(dettaglio.data_prenotazione);
                $("#ora").val(dettaglio.ora_prenotazione);
                $("#numero_persone").val(dettaglio.numero_persone);
                $("#note").val(dettaglio.note || 'Nessuna nota');
                $("#data_creazione").val(dettaglio.data_creazione);
                $("#id_prenotazione").val(id_prenotazione);
                
                $("#modalDettagli").modal('show');
            } else {
                alert("Errore nel recupero dei dettagli della prenotazione: " + result.message);
            }
        },
        error: function (xhr, status, error) {
            console.error("Errore nella richiesta AJAX:", status, error);
            alert("Si è verificato un errore. Riprova più tardi.");
        }
    });
}

function visualizzaDettagliConfermato(id_prenotazione) {
    $.ajax({
        type: "POST",
        url: 'action.php?_action=visualizzaDettagliPrenotazione',
        data: { id_prenotazione: id_prenotazione },
        dataType: 'json',
        success: function (result) {
            if (result.status === 1) {
                const dettaglio = result.data.dettaglio;
                $("#nomeConfe").val(dettaglio.nome);
                $("#cognomeConfe").val(dettaglio.cognome);
                $("#telefonoConfe").val(dettaglio.telefono);
                $("#emailConfe").val(dettaglio.email);
                $("#dataConfe").val(dettaglio.data_prenotazione);
                $("#oraConfe").val(dettaglio.ora_prenotazione);
                $("#numero_personeConfe").val(dettaglio.numero_persone);
                $("#noteConfe").val(dettaglio.note || 'Nessuna nota');
                $("#data_creazioneConfe").val(dettaglio.data_creazione);
                $("#data_modificaConfe").val(dettaglio.data_modifica || 'Non disponibile');
                
                $("#modalDettagliConfermato").modal('show');
            } else {
                alert("Errore nel recupero dei dettagli della prenotazione: " + result.message);
            }
        },
        error: function (xhr, status, error) {
            console.error("Errore nella richiesta AJAX:", status, error);
            alert("Si è verificato un errore. Riprova più tardi.");
        }
    });
}

function apriModalConfermaPrenotazione() {
    $("#modalConfermaPrenotazione").modal('show');
}

function apriModalEliminaPrenotazione() {
    $("#modalEliminaPrenotazione").modal('show');
}

function apriModalRifiutaPrenotazione() {
    $("#modalRifiutaPrenotazione").modal('show');
}

function confermaPrenotazione() {
    let id_prenotazione = $("#id_prenotazione").val();
    $("#modalDettagli").modal('hide');
    $("#modalConfermaPrenotazione").modal('hide');
    $("#loader").removeClass("d-none");

    $.ajax({
        type: "POST",
        url: 'action.php?_action=confermaPrenotazione',
        data: { id_prenotazione: id_prenotazione },
        dataType: 'json',
        success: function (result) {
            $("#loader").addClass("d-none");
            
            if (result.status == 1) {
                elencoPrenotazioni();
                elencoPrenotazioniConfermate();
                numeroPrenotazioniDaConfermare();
                mostraAlertConferma("Prenotazione confermata con successo!", "success");
            } else {
                alert("Errore nella conferma della prenotazione: " + result.message);
            }
        },
        error: function (xhr, status, error) {
            $("#loader").addClass("d-none");
            console.error("Errore nella richiesta AJAX:", status, error);
            alert("Si è verificato un errore. Riprova più tardi.");
        }
    });
}

function eliminaPrenotazione() {
    let id_prenotazione = $("#id_prenotazione").val();
    $("#modalDettagli").modal('hide');
    $("#modalEliminaPrenotazione").modal('hide');
    $("#loader").removeClass("d-none");

    $.ajax({
        type: "POST",
        url: 'action.php?_action=eliminaPrenotazione',
        data: { id_prenotazione: id_prenotazione },
        dataType: 'json',
        success: function (result) {
            $("#loader").addClass("d-none");
            
            if (result.status == 1) {
                elencoPrenotazioni();
                elencoPrenotazioniConfermate();
                numeroPrenotazioniDaConfermare();
                mostraAlertConferma("Prenotazione eliminata con successo!", "success");
            } else {
                alert("Errore nell'eliminazione della prenotazione: " + result.message);
            }
        },
        error: function (xhr, status, error) {
            $("#loader").addClass("d-none");
            console.error("Errore nella richiesta AJAX:", status, error);
            alert("Si è verificato un errore. Riprova più tardi.");
        }
    });
}

function rifiutaPrenotazione() {
    let id_prenotazione = $("#id_prenotazione").val();
    $("#modalDettagli").modal('hide');
    $("#modalRifiutaPrenotazione").modal('hide');
    $("#loader").removeClass("d-none");

    $.ajax({
        type: "POST",
        url: 'action.php?_action=eliminaPrenotazione',
        data: { id_prenotazione: id_prenotazione },
        dataType: 'json',
        success: function (result) {
            $("#loader").addClass("d-none");
            
            if (result.status == 1) {
                elencoPrenotazioni();
                elencoPrenotazioniConfermate();
                numeroPrenotazioniDaConfermare();
                mostraAlertConferma("Prenotazione rifiutata con successo!", "warning");
            } else {
                alert("Errore nel rifiuto della prenotazione: " + result.message);
            }
        },
        error: function (xhr, status, error) {
            $("#loader").addClass("d-none");
            console.error("Errore nella richiesta AJAX:", status, error);
            alert("Si è verificato un errore. Riprova più tardi.");
        }
    });
}

function mostraAlertConferma(message, type) {
    let alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    $("#alertContainer").append(alertHtml);
    
    $('html, body').animate({
        scrollTop: $("#containerMain").offset().top
    }, 500);
    
    setTimeout(() => {
        $(".alert").alert('close');
    }, 5000);
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

function numeroConfermare() {
    $.ajax({
        type: "POST",
        url: 'action.php?_action=numeroConfermare',
        dataType: 'json',
        success: function (result) {
            if (result.status === 1) {
                $("#badgeOrdini").text(result.data.numero);
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
