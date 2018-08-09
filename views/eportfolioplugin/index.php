
<div class="row">
  <div class="col-md-12">

    <!-- overview area -->
    <!-- <div id="title">
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

    <hr> -->

    <div class="row member-container">

      <?php foreach ($templates as $key):?>

        <?php
          $avatar = CourseAvatar::getAvatar($key[0]);
          $avatarUrl = $avatar->getCustomAvatarUrl('medium');
        ?>

        <div class="col-sm-4 member-single-card">


          <div class="template-user-item">

            <div class="template-user-item-head">

              <div class="template-user-item-headline">
                <?php $template = new Seminar($key[0]); echo $template->getName();?>
                <span class="template-bandage">3</span>
              </div>

              <div class="row">
                <div style="padding:0px;" class="col-sm-6 template-user-item-head-image">
                  <img src="<?php echo $avatarUrl ?>" alt="CourseAvatar">
                </div>
                <div class="col-sm-6 template-infos">

                  <div class="template-infos-single">
                    <?php
                     $icon;
                     switch (EportfolioUser::getStatusOfUserInTemplate($owner, $key[0], $group_id, $cid)) {
                       case 1:
                         $icon = 'status-green';
                         break;
                      case 0:
                        $icon = 'stats-yellow';
                        break;
                      case -1:
                          $icon = 'status-red';
                          break;
                     }
                   ?>
                    <?php echo Icon::create('span-full', $icon); ?> Status
                  </div>

                  <div class="template-infos-single">
                    <?= Icon::create('date', 'clickable') ?>
                    <?php
                      $timestamp = EportfolioGroupTemplates::getDeadline($group_id, $key[0]);
                      echo date('d.m.Y', $timestamp);
                    ?>
                    <span style="margin-left: 20px;" class="template-infos-days-left"><br>(noch <?php echo Eportfoliomodel::getDaysLeft($group_id, $key[0]); ?> Tage)</span>
                  </div>

                  <div class="template-infos-single">
                    <?= Icon::create('activity', 'clickable') ?> 23.05.2018
                  </div>

                </div>
              </div>
            </div>

            <div class="row template-kapitel-info">
              <?php foreach (Eportfoliomodel::getChapters($key[0]) as $chapter):?>
                <?php $current_block_id = Eportfoliomodel::getUserPortfilioBlockId($cid ,$chapter[id]); ?>
                <div class="col-sm-4 member-kapitelname"><?php echo $chapter[title]?></div>
                <div class="col-sm-8">
                  <div class="row member-icons">
                    <div class="col-sm-4">
                      <?php if(Eportfoliomodel::checkKapitelFreigabe($current_block_id)): ?>
                          <?= Icon::create('accept', 'clickable'); ?>
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

            <div class="row template-user-stats-area">
              <div class="col-sm-12">
                <div class="row member-footer-box">
                  <div class="col-sm-4">
                    <div class="member-footer-box-big">
                      <?php echo $sharedChapters = Eportfoliomodel::getNumberOfSharedChaptersOfTemplateFromUser($key[0], $userid, $cid);?>
                      /
                      <?php echo $allChapters = Eportfoliomodel::getNumberOfChaptersFromTemplate($key[0]); ?>
                    </div>
                    <div class="member-footer-box-head">
                      freigegeben
                    </div>
                  </div>
                  <div class="col-sm-4">
                    <div class="member-footer-box-big">
                      <?php echo Eportfoliomodel::getProgressOfUserInTemplate($sharedChapters, $allChapters); ?> %
                    </div>
                    <div class="member-footer-box-head">
                      bearbeitet
                    </div>
                  </div>
                  <div class="col-sm-4">
                    <div class="member-footer-box-big">
                      <?php echo Eportfoliomodel::getNumberOfNotesInTemplateOfUser($key[0], $cid); ?>
                    </div>
                    <div class="member-footer-box-head">
                      Notizen
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="template-user-item-footer">
              <?= \Studip\LinkButton::create('Anschauen', Eportfoliomodel::getLinkOfFirstChapter($key[0], $cid)); ?>
            </div>

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
