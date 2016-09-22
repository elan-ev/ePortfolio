
<head>
  <meta charset="utf-8"/><meta charset="utf-8"/>

  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
  <!-- Latest compiled and minified JavaScript -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

  <style media="screen">

    .list-group-item:hover {
      cursor: pointer;
      background-color: #33568b;
      color: #fff;
    }

    .badge {
      background-color: #33568b;
      border: 1px solid #fff;
      font-size: 13px;
      border-radius: 20px;
    }

    .start_overview_inner {
      border: 1px solid #ddd;
      border-radius: 10px;
      padding: 5px;
    }

    .start_overview_heading {
      font-size: 20px;
      text-align: right;
      padding: 20px 0 0;
      font-weight: bold;
    }

    .start_overview_info {
      padding: 20px 0;
    }

  </style>

</head>

<div class="row">

  <!-- <div class="col-md-2">
    <ul class="list-group">
      <li class="list-group-item">Meine Portfolios</li>
      <li class="list-group-item">Freunde</li>
      <li class="list-group-item"><span class="badge">4</span> Max Muster</li>
      <li class="list-group-item"><span class="badge">0</span> Marcel Kipp</li>
      <li class="list-group-item"><span class="badge">1</span> Heinrich Heine</li>
      <li class="list-group-item"><span class="badge">5</span> Paul Paulus</li>
      <li class="list-group-item"><span class="badge">0</span> Steffen Steil</li>
    </ul>
  </div> -->

  <div class="col-md-12">

    <div class="jumbotron" style="border-radius: 10px;">
      <div class="container" style="padding: 0 50px;">

        <h1>Meine Portfolios</h1>

        <p>Hier findest Du alle ePortfolios, die Du angelegt hast oder die andere für dich freigegeben haben.</p>
        <p><a class="btn btn-primary btn-lg" href="#" role="button" style="background-color: #33578c; color: #fff;">Mehr Informationen</a></p>
      </div>
    </div>

  </div>

</div>

<div class="row">
  <div class="col-md-12">
    <table class="table table-striped portfolioOverview">
      <tr>
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

$('#myModal').on('shown.bs.modal', function () {
  $('#myInput').focus()
})

</script>
