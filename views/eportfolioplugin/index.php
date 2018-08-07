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

    <div class="row member-container">

      <?php foreach ($templates as $key):?>

        <div class="col-sm-4 member-single-card">

          <div class="member-item">
            <h3>
              <?php $template = new Seminar($key[0]); echo $template->getName();?>
              <span class="template-bandage">3</span>
            </h3>

            <div class="template-infos">
              <?= Icon::create('date', 'clickable') ?>
              <b>Abgabedatum: </b>
              <?php
                $timestamp = Eportfoliomodel::getDeadline($group_id, $key[0]);
                echo date('d.m.Y', $timestamp);
              ?>
              <span class="template-infos-days-left">(noch <?php echo Eportfoliomodel::getDaysLeft($group_id, $key[0]); ?> Tage)</span>
              <br>
              <?= Icon::create('activity', 'clickable') ?>
              <b>Letzte Änderung: </b> 23.05.2018
            </div>

            <div class="row template-kapitel-info">
              <?php foreach (Eportfoliomodel::getChapters($key[0]) as $chapter):?>
                <?php $current_block_id = Eportfoliomodel::getUserPortfilioBlockId($userPortfolioId ,$chapter[id]); ?>
                <div class="col-sm-4 member-kapitelname"><?php echo $chapter[title]?></div>
                <div class="col-sm-8">
                  <div class="row member-icons">
                    <div class="col-sm-4">
                      <?php if(Eportfoliomodel::checkKapitelFreigabe($current_block_id)): ?>
                        <?php $new_freigabe = LastVisited::chapter_last_visited($current_block_id, $user) < EportfolioFreigabe::hasAccessSince($supervisorGroupId, $current_block_id);?>
                        <?php if($new_freigabe): ?>
                          <?= Icon::create('accept+new', 'clickable'); ?>
                        <?php else: ?>
                          <?= Icon::create('accept', 'clickable'); ?>
                        <?php endif; ?>
                      <?php else: ?>
                        <?= Icon::create('accept', 'inactive'); ?>
                      <?php endif; ?>
                    </div>
                    <div class="col-sm-4">
                      <?php if (Eportfoliomodel::checkSupervisorNotiz($current_block_id) == true): ?>
                        <?= Icon::create('file', 'clickable'); ?>
                      <?php else: ?>
                        <?= Icon::create('file', 'inactive'); ?>
                      <?php endif; ?>
                    </div>
                    <div class="col-sm-4">
                      <?php if (Eportfoliomodel::checkSupervisorResonanz($current_block_id) == true): ?>
                        <?= Icon::create('forum', 'clickable');?>
                      <?php else: ?>
                        <?= Icon::create('forum', 'inactive'); ?>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <?= \Studip\LinkButton::create('Anschauen',  'http://example.org', array('data-dialog' => '1', 'data-hallo' => 'welt')); ?>

          </div>
        </div>
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
