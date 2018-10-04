<div class="row">
  <div class="col-sm-2 member-avatar">
    <?= Avatar::getAvatar($user_id, $userInfo['username'])->getImageTag(Avatar::MEDIUM,array('style' => 'margin-right: 0px; border-radius: 75px; height: 75px; width: 75px; border: 1px solid #28497c;', 'title' => htmlReady($userInfo['Vorname']." ".$userInfo['Nachname']))); ?>
  </div>
  <div class="col-sm-5">
      <div class="member-name-detail">
        <?php echo $vorname . " " . $nachname; ?>
      </div>
      <div class="member-subname">
        Portfoliogruppe: Testgruppe<br>
        Letzte Änderung: <?= date('d.m.Y', Eportfoliomodel::getLastOwnerEdit($portfolio_id)) ?>
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

<div class="status-area">
  <h3>Status des Studenten</h3>
  <?php foreach ($templates as $template_id):?>
    <?php
      $template = Course::findById($template_id);
      $deadline = EportfolioGroupTemplates::getDeadline($group_id ,$template_id);
      if ($deadline == 0) {
        $deadlineOutput = 'Kein Abgabedatum';
      } else {
        $deadlineOutput = date('d.m.Y', $deadline);
      }
    ?>
    <div class="status-area-single">
    <?php
        $icon;
        $status = EportfolioUser::getStatusOfUserInTemplate($template_id, $group_id, $portfolio_id);
        switch ($status){
          case 1:
            $icon = 'status-green';
            break;
          case 0:
            $icon = 'status-yellow';
            break;
          case -1:
            $icon = 'status-red';
            break;
        }

        if($deadline == 0){
          $icon = "inactive";
        }
    ?>
    <?php echo Icon::create('span-full', $icon); ?> <b><?php echo $template->name ?></b> <?php echo $deadlineOutput ?>
    <span class="template-infos-days-left">
      <?php if (!$deadline == 0) {
        echo "(noch ".Eportfoliomodel::getDaysLeft($group_id, $template_id) ." Tage)";
      } ?>
    </span>
  <?php endforeach; ?>
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
      <div class="col-sm-4 member-content-single-line-ober">
        <?php echo $kapitel['title'] ?>
        <?php if(Eportfoliomodel::isEigenesKapitel($portfolio_id, $group_id, $kapitel['id'])): ?>
          <span class="label-selber">Eigenes</span>
        <?php endif; ?>
      </div>
      <div class="col-sm-8">
        <div class="row" style="text-align: center;">
          <div class="col-sm-2">
            <?php if($statusKapitel = Eportfoliomodel::checkKapitelFreigabe($kapitel['id'])): ?>
              <?php $new_freigabe = LastVisited::chapter_last_visited($kapitel['id'], $user_id) < EportfolioFreigabe::hasAccessSince($supervisorGroupId, $kapitel['id']);?>
              <?php if($new_freigabe): ?>
                <?= Icon::create('accept+new', 'clickable'); ?>
              <?php else: ?>
                <?= Icon::create('accept', 'clickable'); ?>
              <?php endif; ?>
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
        <div class="col-sm-4 member-content-unterkapitel">
          <?php echo $unterkapitel['title']; ?>
          <?php if(!$statusKapitel): ?>
            <?php if (Eportfoliomodel::isEigenesUnterkapitel($unterkapitel['id'])):?>
              <span class="label-selber">Eigenes</span>
            <?php endif; ?>
          <?php endif; ?>
        </div>
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
    </div>
  <?php endforeach; ?>
  <!-- <span class="label-selber">Eigenes</span -->

</div>
