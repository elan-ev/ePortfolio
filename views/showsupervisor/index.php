<head>
  <meta charset="utf-8"/><meta charset="utf-8"/>

  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
  <!-- Latest compiled and minified JavaScript -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

  <style media="screen">

    .widget-list, .widget-links li {
      position: relative;
    }

    .active-link {
      background-color: #a9b6cb;
      box-shadow: inset 0 0 0 1px #7e92b0;
      color: #fff!important;
    }

    .active-link::before {
      border: 10px solid rgba(126,146,176,0);
      content: "";
      height: 0;
      width: 0;
      position: absolute;
      border-left-color: #7e92b0;
      left: 100%;
      top:50%;
      margin-top: -10px;
    }

  </style>

</head>

<!-- <button type="button" class="btn btn-default" data-toggle="modal" data-target="#myModal">
 Neue Supervisionsgruppe erstellen
</button> -->

<table>

<?php foreach ($groupList as $key): ?>

  <tr>
    <td style="padding-right:30px;">
      <?= Avatar::getAvatar($key)->getImageTag(Avatar::SMALL) ?>
    </td>
    <td>
      <?php $supervisor = UserModel::getUser($key);
          echo $supervisor[Vorname].' '.$supervisor[Nachname];
       ?>
    </td>
    <td>
      <a href="#" onClick="getUserData('<?php echo $key; ?>');">
        <span style="margin:0px 30px;" class="glyphicon glyphicon-log-in" aria-hidden="true"></span>
      </a>
    </td>
  </tr>

<?php endforeach; ?>


</table>

<?php if(!$id): ?>

  <div class="panel panel-primary">
  <div class="panel-heading">
    Gruppen erstellen
  </div>
  <div class="panel-body">Aktuell haben Sie noch keine Gruppen erstellt. Bitte erstellen Sie zunächst ein Gruppe um mit der Verwaltung fortzufahren</div>
</div>

<?php endif; ?>

<!-- add person buton / MultiPersonSearch -->
<?php if($id) {
  print_r($mp);
}  ?>

<?php

$sf = new QuickSearch("username", "username");
$sf->withButton();
print $sf->render();


  ?>


<div id="myModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Neue Supervisionsgruppe erstellen</h4>
      </div>
      <div class="modal-body">

        <form id="createGroupForm">

          <label>
            <span class="required">Name</span>
            <input type="text" name="name" id="wizard-name" size="75" maxlength="254" value="" required="" aria-required="true" aria-invalid="true">
          </label>

        <label>
          <span>Beschreibung</span>
          <textarea name="description" id="wizard-description" cols="75" rows="4"></textarea>
        </label>

      </form>


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onClick="createGroup();">Save changes</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="userInfoModel" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"></h4>
      </div>
      <div class="modal-body">

        <div id="dataOutputer">

        </div>


      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<script type="text/javascript">
$('#myModal').on('shown.bs.modal', function () {
$('#myInput').focus()
})

function createGroup(){
  $.ajax({
    type: "POST",
    url: "/studip/plugins.php/eportfolioplugin/showsupervisor?create=1",
    data: $("#createGroupForm").serialize(),
    success: function(data) {
      // alert(data);
      // var neu = $("#createGroupForm").serialize();
      var id = data;
      // console.log(id);
      window.document.location.href = "/studip/plugins.php/eportfolioplugin/showsupervisor?id="+id;
    }
  });
}

function getUserData(id){
  $.ajax({
    type: "POST",
    url: "/studip/plugins.php/eportfolioplugin/ajaxsupervisor?userId="+id,
    dataType: 'JSON',
    success: function(data) {
      $('#userInfoModel').modal('toggle');
      console.log(data);
      _.forEach(data, function(value){
        console.log(value);
      });
    }
  });
}

</script>
