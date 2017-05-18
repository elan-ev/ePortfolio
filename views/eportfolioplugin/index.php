<!-- HEAD START -->

<head>
  <meta charset="utf-8"/><meta charset="utf-8"/>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
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

    span img {
      margin-bottom: 5px;
      cursor: pointer;
    }

  </style>
</head>

<!-- HEAD END -->

<div class="row">
  <div class="col-md-12">

    <!-- overview area -->
    <div id="title">
      <h3 style="border:none!important;">
        <?php  echo $seminarTitle; ?>
        <?php if($isOwner == true):?><span style="margin-left: 10px;"><?php echo Icon::create('edit', 'inactive', array('onclick' => 'toggleChangeInput()'));?></span><?php endif; ?>
      </h3>
    </div>

    <?php if($isOwner == true):?>
      <div id="title_changer" style="display: none;">
        <h3 style="border:none!important;"><input name="name" value="<?php echo $seminarTitle; ?>"><span style="margin-left: 10px;"><?php echo Icon::create('accept', 'clickable', array('onclick' => 'saveTitle()')) ?></span></h3>
      </div>
    <?php endif; ?>

    <hr>
    <?php $img = eportfoliopluginController::getImg($cid);
      $img = json_decode($img);
      $imgcount = 0;
     ?>

    <div class="row">

      <?php foreach ($cardInfo as $key): ?>

        <?php
          $link = URLHelper::getLink('plugins.php/courseware/courseware', array('cid' => $cid, 'selected' => $key[id]));
          //$link = '/studip/plugins.php/courseware/courseware?cid='.$cid.'&selected='.$key[id];
          $linkAdmin = $link.'#author';
        ?>

        <div data-blockid="<?php echo $key[id]; ?>" class="col-md-4 card-wrapper">
          <div class="card-inner" style="">

          <h4><?php echo $key[title]; ?></h4>

          <?php $theurl = $img[$imgcount]; $imgcount++; ?>

          <div class="" style="min-height: 220px;background-image: url('<?php echo $theurl; ?>'); background-size: cover;">
            &nbsp;
          </div>

          <!-- <div class="alert alert-info" style="margin: 20px 0;" role="alert">Warum will ich Lehrerin werden? Welche Staerken will ich einbringen? </div> -->

          <div class="">

            <?php //if($isOwner == true):?>
            <div class="avatar-wrapper">
              <?php $checkViewer = eportfoliopluginController::getChapterViewer($cid, $key[id]);?>
              <?php
              $counter = 0;
              foreach($checkViewer as $viewer => $viewerValue):?>
                <?php if($isOwner == true):?>
                  <div class="avatar-container"><?= Avatar::getAvatar($viewerValue)->getImageTag(Avatar::SMALL) ?></div>
                  <?php $counter++; ?>
                <?php endif; ?>
              <?php endforeach; ?>

            </div>
          <?php //endif; ?>

            <br>
            <?php if($isOwner == true):?>
              <b>Freigaben: <?php echo $counter; ?></b><br>
            <?php endif; ?>
            <!--<b>Kommentare: 12</b><br>-->
            <br>

          </div>
          <a href="<?php echo $link; ?>"><?= \Studip\Button::create('Anschauen', 'anschauen', array('type' => 'button')); ?></a>

          <?php if($isOwner == true):?>
            <a href="<?php echo $linkAdmin; ?>"><?= \Studip\Button::create('Bearbeiten', 'anschauen', array('type' => 'button')); ?></a>
          <?php endif; ?>
        </div>
      </div>

      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php if($isOwner == true):?>

  <?= \Studip\Button::create('Portfolio löschen', 'klickMichButton', array('data-toggle' => 'modal', 'data-target' => '#deleteModal', 'type' => 'button')); ?>

  <!-- Modal LÃ¶schen -->
  <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">Portfolio löschen</h4>
        </div>
        <div class="modal-body" id="modalDeleteBody">

          <p id="deleteText" style="margin-bottom:30px;">
            Sind Sie sich sicher, dass Sie das Portfolio <b><?php echo $title; ?></b> löschen wollen?</br>
            Alle Daten werden hierdurch <b>unwiderruflich</b> gelöscht und koennen nicht wiederhergestellt werden.
          </p>

          <div class="deleteSuccess">
            <div><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></div>
            <p>
              Portfolio <b><?php echo $title; ?></b> gelöscht
            </p>
          </div>
            <?= \Studip\Button::create('Portfolio löschen', 'klickMichButton', array('id' => 'deletebtn', 'onClick' => 'deletePortfolio()', 'type' => 'button')); ?>
        </div>
      </div>
    </div>
  </div>

<?php endif; ?>
<script type="text/javascript" src="/studip/plugins_packages/uos/EportfolioPlugin/assets/js/eportfolio.js"></script>
<script>
  var cid = '<?php echo $cid; ?>'

  function toggleChangeInput(){
    $('#title').css('display', 'none');
    $('#title_changer').css('display', 'block');
  }

  function saveTitle(){
    var text = $('#title_changer input').val();

    $.ajax({
      type: 'post',
      url: STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/eportfolioplugin'),
      data: {
        title: text,
        titleChanger: 1,
        cid: '<?php echo $_GET["cid"]; ?>'
      },
      success: function (data){
        $('#title h3').html(text);
        $('#title_changer').css('display', 'none');
        $('#title').css('display', 'block');
      }
    });

  }

</script>
