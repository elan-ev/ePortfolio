


<head>
  <meta charset="utf-8"/><meta charset="utf-8"/>

  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
  <!-- Latest compiled and minified JavaScript -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

  <style media="screen">

    .supervisor-btn {
      position: absolute;
      right: 50px;
      top: -2px;
      padding: 8px;
      color: #fff;
      background-color: #28497c;
      font-size: 20px;
      border-top: 2px solid rgba(255,255,255,0.3);
    }

    .supervisor-btn a {
      color: #fff;
    }

  </style>

</head>


<!-- Supervisor Button -->

<?php if($linkId == 'noId'): ?>

  <script type="text/javascript">
  //$('.helpbar-container').prepend('<div class="supervisor-btn"><a href="showsupervisor?id=<?php echo $linkId; ?>"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></a></div>');
  </script>

<?php endif; ?>


<!-- End Supervisor Button -->

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

<?php if ($perm == "dozent"):?>
<div class="row">
  <div class="col-md-12">
    <table class="default">
      <caption>Meine Vorlagen</caption>
      <colgroup>
        <col width="30%">
        <col width="60%">

      </colgroup>
      <thead>
        <tr class="sortable">
          <th>Portfolio-Name</th>
          <th>Beschreibung</th>

        </tr>
      </thead>
      <tbody>
        <?php $temps = ShowController::getTemplates();
          foreach ($temps as $key):?>

          <tr>
            <td><?php echo $key["temp_name"] ?></td>
            <td><?php echo $key["description"] ?></td>
          </tr>

        <?php endforeach; ?>
      </tbody>
    </table>

    <hr>
  </div>
</div>
<?php endif; ?>

<div class="row">
  <div class="col-md-12">

    <?php ?>
    <!-- Banner Success Display when created -->
    <div class="alert alert-success createPortfolioBanner" role="alert">Portfolio <span id="createPortfolioName"></span> wurde erstellt</div>

    <table class="default">
      <caption>Meine Portfolios</caption>
      <colgroup>
        <col width="30%">
        <col width="50%">
        <col width="10%" style="text-align: center;">
        <col width="10%" style="text-align: center;">
      </colgroup>
      <thead>
        <tr class="sortable">
          <th>Portfolio-Name</th>
          <th>Beschreibung</th>
          <th style="text-align: center;">Freigaben</th>
          <th style="text-align: center;">Aktionen</th>
        </tr>
      </thead>
      <tbody>
        <?php $countPortfolios = 0; ?>
        <?php $myportfolios = ShowController::getMyPortfolios(); ?>
        <?php foreach ($myportfolios as $portfolio): ?>
          <?php $thisPortfolio = new Seminar($portfolio);
                $countPortfolios++; ?>
          <tr class=''>
            <td><a href="<?php echo URLHelper::getLink('plugins.php/eportfolioplugin/eportfolioplugin', array('cid' => $portfolio)); ?>"><?php echo $thisPortfolio->getName(); ?></a></td>
            <td><?php echo ShowController::getCourseBeschreibung($portfolio); ?></td>
            <td style="text-align: center;"><?php echo ShowController::countViewer($portfolio); ?></td>
            <td style="text-align: center;"><a href="<?php echo URLHelper::getLink('plugins.php/eportfolioplugin/eportfolioplugin', array('cid' => $portfolio)); ?>"><?php echo Icon::create('edit', 'clickable') ?></a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>

      <script type="text/javascript">

        function PortfolioHeadline(i) {
          var one = "Mein Portfolio";
          var two = "Meine Portfolios"

          if (i <= 1) {
            $('#headline_uebersicht').text('Mein Portfolio');
          } else {
            $('#headline_uebersicht').text('Meine Portfolios');
          }
        }

        PortfolioHeadline(<?php echo $countPortfolios; ?>);

      </script>

    </table>
  </div>
</div>


<div class="row">
  <div class="col-md-6">
    <?= \Studip\Button::create('Eigenes Portfolio erstellen', 'klickMichButton', array('data-toggle' => 'modal', 'data-target' => '#myModal', 'id' => "newPortfolio")); ?>
  </div>
</div>

<hr>

<div class="row">
  <div class="col-md-12">
    <table  class="default">
      <caption>Sichtbare Portfolios</caption>
      <colgroup>
        <col width="30%">
        <col width="60%">
        <col width="10%">
      </colgroup>
      <thead>
        <tr class="sortable">
          <th>Portfolio-Name</th>
          <th>Beschreibung</th>
          <th>Besitzer</th>
        </tr>
      </thead>
      <tbody>
      <?php $myAccess = ShowController::getAccessPortfolio(); ?>
      <?php foreach ($myAccess as $portfolio): ?>
        <?php $thisPortfolio = new Seminar($portfolio); ?>
        <tr class='insert_tr'>
          <td><a href='<?php echo URLHelper::getLink('plugins.php/eportfolioplugin/eportfolioplugin', array('cid' => $portfolio)); ?>'><?php echo $thisPortfolio->getName(); ?></a></td>
          <td></td>
          <td>
            <?php
              print_r(ShowController::getOwnerName($thisPortfolio->getId()));
             ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- <div class="">

  <h4>Meine Gruppen</h4>



</div> -->

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
          <div class="alert alert-danger createPortfolioBanner" role="alert" id="createBannerAlert">Bitte füllen Sie alle Felder aus</div>

          <?= \Studip\Button::create('Erstellen', 'Button', array('type' => 'submit')); ?>
        </form>

        <!-- Form Ende  -->
      </div>
    </div>
  </div>
</div>
<script type="text/javascript" src="<?php echo URLHelper::getLink("plugins_packages/uos/EportfolioPlugin/assets/js/eportfolio.js"); ?>"></script>
<script>

  $( document ).ready(function() {
    var nameNewCreatePortfolio;

    // updatePortfolioTable();
    // updateAccessTable();
    createNewPortfolio();

  });

  function updater() {
    //deleteOldTableRows();
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
  //$('.customLinkList1').append('<li><a>Einstellungen</a></li>');
  //$('.customLinkList1').append('<li><a>Portfolios verwalten</a></li>');

  <?php if($linkId == 'noId'): ?>
    console.log("no supervisor");
  <?php elseif ($linkId):?>
    $('.customLinkList1').append('<li><a href="showsupervisor?id=<?php echo $linkId; ?>">Supervisoransicht</a></li>');
  <?php endif; ?>

  //Widget - Freunde
  //$('.sidebar').append('<div class="sidebar-widget widgetCustom2"><div class="sidebar-widget-header">Freunde</div></div>');
  //$('.widgetCustom2').append('<ul class="widget-list widget-links sidebar-navigation customLinkList2"></ul>');
  //$('.customLinkList2').append('<li><a>Testperson 1</a></li>');
  //$('.customLinkList2').append('<li><a>Testperson 2</a></li>');
  //$('.customLinkList2').append('<li><a>Testperson 3</a></li>');


</script>
