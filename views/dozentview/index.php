<?php

  $userId = $GLOBALS["user"]->id;
  $perm = get_global_perm($userId);

  $havePerm = array("root", "dozent", "admin");
  if (in_array($perm, $havePerm)){
  } else {
    exit("Sie haben keine Berechtigung diese Seite zu betrachten");
  }

?>

<div class="row">

  <div class="col-md-12">

    <div class="jumbotron" style="border-radius: 10px;">
      <div class="container" style="padding: 0 50px;">

        <h1>Dozentenansicht</h1>

        <p>In der Dozentenansicht finden sie alle Portfolios, auf die sie Zugriff haben.</p>
        <p><a class="btn btn-primary btn-lg" href="#" role="button" style="background-color: #33578c; color: #fff;">Mehr Informationen</a></p>
      </div>
    </div>

  </div>

</div>

<hr>

<div class="row">
  <div class="col-md-12">
    <h4>Für Sie sichtbare Portfolios</h4>

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

        <form action="/studip/plugins.php/eportfolioplugin/create" method="post">
          <div class="form-group">
            <label for="PortfolioName">Portfolio Name</label>
            <input type="Text" class="form-control" id="PortfolioName" placeholder="Portfolio Name" name="name">
          </div>
          <div class="form-group">
            <label for="Beschreibung">Beschreibung</label>
            <input type="text" class="form-control" id="Beschreibung" placeholder="Beschreibung des Portfolios" name="text">
          </div>

          <button type="submit" class="btn btn-success">Erstellen</button>
        </form>

        <!-- Form Ende  -->
      </div>
    </div>
  </div>
</div>


<script>

  //Trigger Modal
  $('#myModal').on('shown.bs.modal', function () {
    $('#myInput').focus()
  })

  //Abfangen GET[]
  function getUrlVars() {
      var vars = {};
      var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
      vars[key] = value;
      });
      return vars;
  }

  //Display Banner mit Portfolio Name
  var seminarName = getUrlVars()["seminarName"];
  if(seminarName) {
    $('#createPortfolioName').append(seminarName);
    $('.createPortfolioBanner').css('display', 'block');

    //Entfernt %20 aus string
    $("#createPortfolioName").text(function(index, text) {
      return text.replace("%20", " ");
    });

  }

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
