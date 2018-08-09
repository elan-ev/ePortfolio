<div class="activity-feed-container">

  <div class="activity-feed-header">
    <span class="activity-feed-label">

      <?php if($countActivities >= 1){
          echo $countActivities;
      } else {
        echo "Keine";
      } ?>

    Neue Aktivitäten</span>
    <div class="activity-feed-line"></div>
  </div>

  <?php foreach ($activities as $activity): ?>
    <?php if ($activity->is_new):?>
    <div class="single-activity">
      <div class="row">
        <div class="col-sm-7 single-activity-info">
          <div class="row">
            <div class="">

              <?php if ($activity->type == "freigabe"):?>
                <?= Icon::create('accept', 'clickable');  ?>
              <?php endif; ?>

              <?php if ($activity->type == "aenderung"):?>
                <?= Icon::create('accept+new', 'clickable');  ?>
              <?php endif; ?>

              <?php if ($activity->type == "notiz"):?>
                <?= Icon::create('file', 'clickable');  ?>
              <?php endif; ?>

              <?= Avatar::getAvatar($user, $userInfo['username'])->getImageTag(Avatar::MEDIUM,array('style' => 'margin-right: 0px; border-radius: 35px; position: relative; left: -9px; top: 3px; border: 3px solid #f5f6f6;', 'title' => htmlReady($userInfo['Vorname']." ".$userInfo['Nachname']))); ?>

            </div>
            <div class="" style="line-height: 36px;">
              <b>Max Mustermann: </b> <?php echo $activity->message; ?>
            </div>
          </div>
          </div>
          <div class="col-sm-4" style="line-height: 36px; text-align: center;">
            <?php echo date('d.m.Y', $activity->mk_date); ?>
            <div class="" style="float: right; position: relative; top: 3px; ">
              <a href="<?php echo $activity->link; ?>">
                <?= Icon::create('link-intern'); ?>
              </a>
            </div>
          </div>
        </div>
      </div>

      <?php endif; ?>
  <?php endforeach; ?>


  <div class="activity-feed-header" style="margin-top: 20px;">
    <span class="activity-feed-label-alt">Alte Aktivitäten</span>
    <div class="activity-feed-line"></div>
  </div>

  <?php foreach ($activities as $activity): ?>
    <?php if (!$activity->is_new):?>
    <div class="single-activity">
      <div class="row">
        <div class="col-sm-7 single-activity-info">
          <div class="row">
            <div class="">

              <?php if ($activity->type == "freigabe"):?>
                <?= Icon::create('accept', 'clickable');  ?>
              <?php endif; ?>

              <?php if ($activity->type == "aenderung"):?>
                <?= Icon::create('accept+new', 'clickable');  ?>
              <?php endif; ?>

              <?php if ($activity->type == "notiz"):?>
                <?= Icon::create('file', 'clickable');  ?>
              <?php endif; ?>

              <?= Avatar::getAvatar($user, $userInfo['username'])->getImageTag(Avatar::MEDIUM,array('style' => 'margin-right: 0px; border-radius: 35px; position: relative; left: -9px; top: 3px; border: 3px solid #f5f6f6;', 'title' => htmlReady($userInfo['Vorname']." ".$userInfo['Nachname']))); ?>

            </div>
            <div class="" style="line-height: 36px;">
              <b>Max Mustermann: </b> <?php echo $activity->message; ?>
            </div>
          </div>
          </div>
          <div class="col-sm-4" style="line-height: 36px; text-align: center;">
            <?php echo date('d.m.Y', $activity->mk_date); ?>
            <div class="" style="float: right; position: relative; top: 3px; ">
              <a href="<?php echo $activity->link; ?>">
                <?= Icon::create('link-intern'); ?>
              </a>
            </div>
          </div>
        </div>
      </div>

      <?php endif; ?>
  <?php endforeach; ?>

</div>
