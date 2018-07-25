<div class="row">
  <div class="col-sm-2 member-avatar">
    <?= Avatar::getAvatar($user, $userInfo['username'])->getImageTag(Avatar::SMALL,array('style' => 'margin-right: 0px; border-radius: 75px; height: 75px; width: 75px; border: 1px solid #28497c;', 'title' => htmlReady($userInfo['Vorname']." ".$userInfo['Nachname']))); ?>
  </div>
  <div class="col-sm-5">
      <div class="member-name-detail">
        <?php echo $vorname . " " . $nachname; ?>
      </div>
      <div class="member-subname">
        Status: <span class="member-status-label">.</span> <br>
        Studiengang: Medieninformatik <br>
        Portfoliogruppe: Testgruppe<br>
        Letzte �nnderung am: 23.05.2018
      </div>
  </div>
  <div class="col-sm-5">
    <div class="row row member-footer-box-detail">
      <div class="col-sm-4">
        <div class="member-footer-box-big-detail">
          <?php echo $AnzahlFreigegebenerKapitel ?> / <?php echo $AnzahlAllerKapitel; ?>
        </div>
        <div class="member-footer-box-head">
          freigegeben
        </div>
      </div>
      <div class="col-sm-4">
        <div class="member-footer-box-big-detail">
          <?php echo $GesamtfortschrittInProzent; ?> %
        </div>
        <div class="member-footer-box-head">
          bearbeitet
        </div>
      </div>
      <div class="col-sm-4">
        <div class="member-footer-box-big-detail">
          <?php echo $AnzahlNotizen; ?>
        </div>
        <div class="member-footer-box-head">
          Notizen
        </div>
      </div>
    </div>
  </div>
</div>

<div class="member-contant-detail">

  <div class="row member-containt-head-detail">
    <div class="col-sm-4">Kapitelname</div>
    <div class="col-sm-8">
      <div class="row member-content-icons">
        <div class="col-sm-2">Freigegeben</div>
        <div class="col-sm-2">Anliegen</div>
        <div class="col-sm-2">Resonanz</div>
        <div class="col">Aktionen</div>
      </div>
    </div>
  </div>

  <?php foreach ($chapters as $kapitel):?>
    <?php $subchapter = Eportfoliomodel::getSubChapters($kapitel['id']); ?>

    <div class="row member-content-single-line">
      <div class="col-sm-4 member-content-single-line-ober"><?php echo $kapitel['title'] ?></div>
      <div class="col-sm-8">
        <div class="row" style="text-align: center;">
          <div class="col-sm-2">
            <?php if(Eportfoliomodel::checkKapitelFreigabe($kapitel['id'])): ?>
              <?= Icon::create('accept'); ?>
            <?php else: ?>
              <?= Icon::create('accept', 'inactive'); ?>
            <?php endif; ?>
          </div>
          <div class="col-sm-2">
            <?php if (Eportfoliomodel::checkSupervisorNotiz($kapitel['id']) == true): ?>
              <?= Icon::create('file', 'clickable'); ?>
            <?php else: ?>
              <?= Icon::create('file', 'inactive'); ?>
            <?php endif; ?>
          </div>
          <div class="col-sm-2">
          <?php if (Eportfoliomodel::checkSupervisorResonanz($kapitel['id'])):?>
            <?= Icon::create('forum');  ?>
          <?php else: ?>
            <?= Icon::create('forum', 'inactive'); ?>
          <?php endif; ?>
          </div>
          <div class="col member-aktionen-detail">
            <a href="<?php echo URLHelper::getLink("plugins.php/courseware/courseware?cid=" . $portfolio_id); ?>">Anschauen</a>
            <a href="<?php echo URLHelper::getLink("plugins.php/courseware/courseware?cid=" . $portfolio_id); ?>">Feedback geben</a>
          </div>
        </div>
      </div>

      <?php foreach ($subchapter as $unterkapitel): ?>
        <div class="col-sm-4 member-content-unterkapitel"><?php echo $unterkapitel['title']; ?></div>
        <div class="col-sm-8">
          <div class="row member-content-icons">
            <div class="col-sm-2"></div>
            <div class="col-sm-2">
              <?php if (Eportfoliomodel::checkSupervisorNotizInUnterKapitel($unterkapitel['id'])):?>
                <?= Icon::create('file', 'clickable'); ?>
              <?php else: ?>
                <?= Icon::create('file', 'inactive'); ?>
              <?php endif; ?>
            </div>
            <div class="col-sm-2">
              <?php if(Eportfoliomodel::checkSupervisorResonanzInSubchapter($unterkapitel['id'])):?>
                <?= Icon::create('forum');  ?>
              <?php else:?>
                <?= Icon::create('forum', 'inactive'); ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

  <?php endforeach; ?>

  <!-- <span class="label-selber">Eigenes</span -->

</div>
