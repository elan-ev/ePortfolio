<head>
  <meta charset="utf-8"/><meta charset="utf-8"/>

  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
  <!-- Latest compiled and minified JavaScript -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

</head>

<h1 style="border:none!important;"><?php  echo $seminarTitle; ?></h1>
<hr>

<div class="row">

<?php foreach ($cardInfo as $key): ?>

  <?php
    $link = '/studip/plugins.php/courseware/courseware?cid=r4noa4wa9l7lzhhy287uzb8lq7zvobmf&selected='.$key[id];
    $linkAdmin = $link.'#author';
  ?>

  <div class="col-md-4 card-wrapper">
    <div class="card-inner" style="">

    <h4><?php echo $key[title]; ?></h4>

    <div class="" style="background-color:rgba(0,0,0,0.2);width:100%;height: 150px;">
      Bild
    </div>

    <div class="alert alert-info" style="margin: 20px 0;" role="alert">Warum will ich Lehrerin werden? Welche Staerken will ich einbringen? </div>

    <div class="">
      <b>Freigaben: </b>50 <br>
      <b>Kommentare: </b> 12
    </div>

    <a href="<?php echo $link; ?>"><button type="button" class="btn btn-primary">Anschauen</button></a>

    <?php if($isOwner == true):?>
      <a href="<?php echo $linkAdmin; ?>"><button type="button" class="btn btn-primary">Bearbeiten</button></a>
    <?php endif; ?>
  </div>
</div>

<?php endforeach; ?>

</div>
