<!-- HEAD START -->

<head>
  <meta charset="utf-8"/><meta charset="utf-8"/>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>

<!-- HEAD END -->

<h2>Einstellungen</h2>

<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal">Portfolio loeschen</button>

<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Portfolio loeschen</h4>
      </div>
      <div class="modal-body" id="modalDeleteBody">

        <p id="deleteText" style="margin-bottom:30px;">
          Sind Sie sich sicher, dass Sie das Portfolio <b><?php echo $title; ?></b> loeschen wollen?</br>
          Alle Daten werden hierdurch <b>unwiderruflich</b> geloescht und koennen nicht wiederhergestellt werden.
        </p>

        <div class="deleteSuccess">
          <div><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></div>
          <p>
            Portfolio <b><?php echo $title; ?></b> geloescht
          </p>
        </div>

        <button type="button" onClick="deletePortfolio();" id="deletebtn" class="btn btn-danger">Portfolio loeschen</button>

      </div>
    </div>
  </div>
</div>

<script type="text/javascript" src="/studip/plugins_packages/Universitaet Osnabrueck/EportfolioPlugin/assets/js/eportfolio.js"></script>
<script type="text/javascript">

  var cid = '<?php echo $cid; ?>';

  $( document ).ready(function() {


    $('#deleteModal').on('shown.bs.modal', function () {
      $('#deleteModal').focus()
    })

  });

</script>
