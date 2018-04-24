<?php

  $images = array(
    "https://www.arbeitstipps.de/wp-content/uploads/2010/06/leere-blatt-syndrom-mangelnde-kreativitaet-tipps.jpg",
    "https://www.pointer.de/bilder/teaser_top/2374lernen_bibliothek_studium.jpg",
    "https://www.maz-online.de/var/storage/images/maz/lokales/teltow-flaeming/sorge-um-unterrichtsausfall-trotz-neuer-lehrer/262589062-1-ger-DE/Sorge-um-Unterrichtsausfall-trotz-neuer-Lehrer_pdaArticleWide.jpg",
    "https://www.daad.de/medien/ausland/symbole/fittosize_558_314_3de6fbc25ed35bc4e67ac128c2c40130_abschlussfeier_by_thomas_koelsch_pixelio.jpg",
    "https://p5.focus.de/img/fotos/origs2589632/6655443606-w630-h354-o-q75-p5/schule-lehrer.jpg",
    "https://p5.focus.de/img/fotos/origs1094264/3255449779-w630-h354-o-q75-p5/schule-lernen.jpg",
    "https://www.km.bayern.de/bilder/km_absatz/foto/6667_0710_bibliotheken_partner_der_schule_455.jpg",
    "https://www.pointer.de/bilder/teaser_top/2374lernen_bibliothek_studium.jpg",
  );

 ?>

<!-- HEAD END -->

<div class="row">
  <div class="col-md-12">

    <!-- overview area -->
    <div id="title">
      <h3 style="border:none!important;">
        <?php  echo $seminarTitle; ?>
        <?php  echo ' - Dieses Portfolio gehört ' . $owner['Vorname'] . ' ' . $owner['Nachname']; ?>
        <?php if($isOwner == true || $canEdit == true):?><span title='Titel ändern' style="margin-left: 10px;"><?php echo Icon::create('edit', 'inactive', array('onclick' => 'toggleChangeInput()'));?></span><?php endif; ?>
      </h3>
    </div>

    <?php if($isOwner == true || $canEdit == true):?>
      <div id="title_changer" style="display: none;">
        <h3 style="border:none!important;"><input name="name" value="<?php echo $seminarTitle; ?>"><span style="margin-left: 10px;"><?php echo Icon::create('accept', 'clickable', array('onclick' => 'saveTitle()')) ?></span></h3>
      </div>
    <?php endif; ?>

    <hr>

    <div class="row">

      <?php $imageNumber = 0; ?>
      <?php foreach ($cardInfo as $key): ?>

        <?php if(EportfolioFreigabe::hasAccess($userid, $cid, $key[id]) || $isVorlage): ?>
        <?php
         
            $link = URLHelper::getLink('plugins.php/courseware/courseware', array('cid' => $cid, 'selected' => $key[id]));
            //$link = '/studip/plugins.php/courseware/courseware?cid='.$cid.'&selected='.$key[id];
            $linkAdmin = $link.'#author';
        ?>

        <div data-blockid="<?php echo $key[id]; ?>" class="col-md-4 card-wrapper">
          <div class="card-inner" style="">

          <h4><?php echo $key[title]; ?></h4>

          <?php $theurl = $img[$imgcount]; $imgcount++; ?>

          <div class="" style="min-height: 220px;background-image: url('<?php echo $images[$imageNumber]; ?>'); background-size: cover;">
            &nbsp;
          </div>

          <?php $imageNumber++; ?>

          <!-- <div class="alert alert-info" style="margin: 20px 0;" role="alert">Warum will ich Lehrerin werden? Welche Staerken will ich einbringen? </div> -->

          <div class="">

            <?php if($isOwner == true):?>
            <?php //if($isOwner == true):?>
            <div class="avatar-wrapper">
              <?php $viewers = EportfolioFreigabe::getUserWithAccess($cid, $key[id]);?>
              <?php
              $counter = 0;
              foreach($viewers as $viewer):?>
                <?php $viewer = new User($viewer->user_id); ?>
                  <?php if(!$viewer->vorname):?>
                    <div class="avatar-container"><?= Avatar::getAvatar('nobody')->getImageTag(Avatar::SMALL, array('title' => 'Gruppen-Supervisoren')) ?></div>
                  <?php else: ?>
                    <div class="avatar-container"><?= Avatar::getAvatar($viewer->user_id)->getImageTag(Avatar::SMALL, array('title' => $viewer->vorname . ' ' . $viewer->nachname)) ?></div>
                  <?php endif; ?>
                  <?php $counter++; ?>
                
              <?php endforeach; ?>

            </div>
              <?php endif; ?>
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
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
</div>




<script type="text/javascript" src="<?php echo $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'plugins_packages/uos/EportfolioPlugin/assets/js/eportfolio.js'; ?>"></script>
<script>
  var cid = '<?php echo $cid; ?>'

  function toggleChangeInput(){
    $('#title').css('display', 'none');
    $('#title_changer').css('display', 'block');
  }

  function saveTitle(){
    var text = $('#title_changer input').val().replace(/<\/?[^>]+(>|$)/g, "");;

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


