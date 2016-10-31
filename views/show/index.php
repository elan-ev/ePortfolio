<?php

  $userId = $GLOBALS["user"]->id;
  $perm = get_global_perm($userId);

  $havePerm = array("root", "dozent", "admin");
  if (in_array($perm, $havePerm)){
    exit("Sie haben keine Berechtigung diese Seite zu betrachten");
  }

?>


<head>
  <meta charset="utf-8"/><meta charset="utf-8"/>

  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
  <!-- Latest compiled and minified JavaScript -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

</head>

<div class="row">

  <div class="col-md-12">

    <div class="jumbotron" style="border-radius: 10px;">
      <div class="container" style="padding: 0 50px;">

        <h1 id="headline_uebersicht"></h1>

        <p>Hier finden Sie alle ePortfolios, die Sie angelegt hast oder die andere für Sie freigegeben haben.</p>
        <p><a class="btn btn-primary btn-lg" href="#" role="button" style="background-color: #33578c; color: #fff;">Mehr Informationen</a></p>
      </div>
    </div>

  </div>

</div>

<hr>

<div class="row">
  <div class="col-md-12">

    <h4>�bersicht meiner Portfolios <span id="labelMyPortfolio" class="badge"></span></h4>

    <!-- Banner Success Display when created -->
    <div class="alert alert-success createPortfolioBanner" role="alert">Portfolio <span id="createPortfolioName"></span> wurde erstellt</div>

    <table data-link="row" class="rowlink table  portfolioOverview">
      <tr class="tr-head">
        <th>
          Portfolio-Name
        </th>
        <th>
          Beschreibung
        </th>
        <th>
          Freigaben
        </th>
      </tr>

    </table>
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <button data-toggle="modal" data-target="#myModal" type="button" name="button" class="btn btn-success" id="newPortfolio" style="margin-bottom: 30px;"><i class="fa fa-plus" aria-hidden="true"></i> Neues Portfolio erstellen</button>
  </div>
</div>

<hr>

<div class="row">
  <div class="col-md-12">
    <h4>F�r Sie sichtbare Portfolios <span id="labelAccess" class="badge"></span></h4>

    <table  data-link="row" class=" rowlink table  viewportfolioOverview">
      <tr class="tr-head">
        <th>
          Portfolio-Name
        </th>
        <th>
          Beschreibung
        </th>
        <th>
          Besitzer
        </th>
      </tr>

    </table>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Neues Portfolio erstellen</h4>
      </div>
      <div class="modal-body">
        <!-- Input Form  -->

        <form id="createForm" method="post">
          <div class="form-group">
            <label for="PortfolioName">Portfolio Name</label>
            <input type="Text" class="form-control" id="PortfolioName" placeholder="Portfolio Name" name="name">
          </div>
          <div class="form-group">
            <label for="Beschreibung">Beschreibung</label>
            <input type="text" class="form-control" id="Beschreibung" placeholder="Beschreibung des Portfolios" name="text">
          </div>

          <!-- Error msg -->
          <div class="alert alert-danger createPortfolioBanner" role="alert" id="createBannerAlert">Bitte fuellen Sie alle Felder aus</div>

          <button type="submit" class="btn btn-success">Erstellen</button>
        </form>

        <!-- Form Ende  -->
      </div>
    </div>
  </div>
</div>

<script type="text/javascript" src="/studip/plugins_packages/Universitaet Osnabrueck/EportfolioPlugin/assets/js/eportfolio.js"></script>
<script>

  $( document ).ready(function() {
    var nameNewCreatePortfolio;

    updatePortfolioTable();
    updateAccessTable();
    createNewPortfolio();

  });

  function updater() {
    deleteOldTableRows();
    updatePortfolioTable();
  }

  //Trigger Modal
  $('#myModal').on('shown.bs.modal', function () {
    $('#myInput').focus()
  })

  // Statische Sitebar
  // Widget - Navigation
  $('.sidebar').append('<div class="sidebar-widget widgetCustom1"><div class="sidebar-widget-header">Navigation</div></div>');
  $('.widgetCustom1').append('<ul class="widget-list widget-links sidebar-navigation customLinkList1"></ul>');
  $('.customLinkList1').append('<li><a>Einstellungen</a></li>');
  $('.customLinkList1').append('<li><a>Portfolios verwalten</a></li>');

  //Widget - Freunde
  $('.sidebar').append('<div class="sidebar-widget widgetCustom2"><div class="sidebar-widget-header">Freunde</div></div>');
  $('.widgetCustom2').append('<ul class="widget-list widget-links sidebar-navigation customLinkList2"></ul>');
  $('.customLinkList2').append('<li><a>Testperson 1</a></li>');
  $('.customLinkList2').append('<li><a>Testperson 2</a></li>');
  $('.customLinkList2').append('<li><a>Testperson 3</a></li>');


</script>
