<!-- HEAD START -->

<head>
  <meta charset="utf-8"/><meta charset="utf-8"/>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>

<!-- HEAD END -->

<div class="row">
  <div class="col-md-3">

    <!-- sidebar -->
    <h4>Reflexionsimpulse</h4>
    <ul class="list-group">
      <?php foreach ($cardInfo as $key): ?>
        <a href="#" class="list-group-item" data-toggle="collapse" data-target="#<?php echo $key[id]; ?>" data-parent="#menu">
          <?php echo $key[title];?>
          <?php if($key[section]):?>
            <span class="glyphicon glyphicon glyphicon-chevron-down pull-right" aria-hidden="true"></span>
          <?php endif; ?>
         </a>

        <div id="<?php echo $key[id]; ?>" class="sublinks collapse">
          <?php foreach ($key[section] as $section):?>
            <a class="list-group-item small"><span class="glyphicon glyphicon-chevron-right"></span> <?php echo $section[title]; ?></a>
          <?php endforeach; ?>
        </div>

      <?php endforeach; ?>
    </ul>

    <h4>Teilnehmer</h4>
    <div class="panel list-group">
     <a href="#" class="list-group-item" data-toggle="collapse" data-target="#sm" data-parent="#menu">Supervisoren<span class="glyphicon glyphicon glyphicon-chevron-down pull-right" aria-hidden="true"></span></a>
     <div id="sm" class="sublinks collapse">
      <a class="list-group-item small"><span class="glyphicon glyphicon-chevron-right"></span> Marcel Kipp</a>
      <a class="list-group-item small"><span class="glyphicon glyphicon-chevron-right"></span> Max Mustermann</a>
     </div>
     <a href="#" class="list-group-item" data-toggle="collapse" data-target="#m" data-parent="#menu">Zuschauer <span class="label label-info"><?php echo $viewerCounter; ?></span><span class="glyphicon glyphicon glyphicon-chevron-down pull-right" aria-hidden="true"></span></a>
     <div id="m" class="sublinks collapse">

      <?php foreach ($viewerList as $viewerId):?>
        <?php $viewer = UserModel::getUser($viewerId); ?>
        <a class="list-group-item small"><span class="glyphicon glyphicon-chevron-right"></span><?php echo $viewer[Vorname].' '.$viewer[Nachname]; ?></a>
      <?php endforeach; ?>

     </div>
    </div>

    <h4>Einstellungen</h4>
    <div class="panel list-group">
     <a href="/studip/plugins.php/eportfolioplugin/settings?cid=<?php echo $cid; ?>" class="list-group-item">Portfolioeinstellungen</a>
    </div>

  </div>

  <div class="col-md-9">

    <!-- overview area -->

    <h1 style="border:none!important;"><?php  echo $seminarTitle; ?></h1>
    <hr>

    <div class="row">
      <?php foreach ($cardInfo as $key): ?>

        <?php
          $link = '/studip/plugins.php/courseware/courseware?cid='.$cid.'&selected='.$key[id];
          $linkAdmin = $link.'#author';
        ?>

        <div class="col-md-4 card-wrapper">
          <div class="card-inner" style="">

          <h4><?php echo $key[title]; ?></h4>

          <div class="" style="background-color:rgba(0,0,0,0.2);width:100%;height: 150px;">
            &nbsp;
          </div>

          <div class="alert alert-info" style="margin: 20px 0;" role="alert">Warum will ich Lehrerin werden? Welche Staerken will ich einbringen? </div>

          <div class="">
            <b>Freigaben: </b>

            <?php echo $numChapterViewer[$key[id]][number]; ?>

            <div class="avatar-wrapper">
              <?php foreach ($numChapterViewer[$key[id]][user] as $viewer):?>
                <div class="avatar-container"><?= Avatar::getAvatar($viewer)->getImageTag(Avatar::SMALL, tooltip2($viewer)) ?></div>
              <?php endforeach; ?>
            </div>

            <br>
            <b>Kommentare: </b> 12
          </div>

          <?php $model = UserModel::getUser('205f3efb7997a0fc9755da2b535038da');
                echo $model[Vorname].' '.$model[Nachname];
            ?>

          <a href="<?php echo $link; ?>"><button type="button" class="btn btn-primary">Anschauen</button></a>

          <?php if($isOwner == true):?>
            <a href="<?php echo $linkAdmin; ?>"><button type="button" class="btn btn-primary">Bearbeiten</button></a>
          <?php endif; ?>
        </div>
      </div>

      <?php endforeach; ?>
    </div>
  </div>
</div>
