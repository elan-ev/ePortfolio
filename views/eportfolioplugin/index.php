<div class="row">
    <div class="col-md-12">

        <div class="row member-container">
            <?php foreach ($templates as $template): ?>

                <?php
                $avatar    = CourseAvatar::getAvatar($template->id);
                $avatarUrl = $avatar->getCustomAvatarUrl(Avatar::MEDIUM);
                $timestamp = EportfolioGroupTemplates::getDeadline($group_id, $template->id);
                ?>

                <div class="col-sm-4 member-single-card">
                    <div class="template-user-item">
                        <div class="template-user-item-head">

                            <div class="template-user-item-headline">
                                <?= $template->getName(); ?>
                            </div>

                            <div class="row">
                                <? if ($avatarUrl) : ?>
                                    <div style="padding:0px;" class="col-sm-6 template-user-item-head-image">
                                        <img src="<?php echo $avatarUrl ?>" alt="CourseAvatar">
                                    </div>
                                <? endif ?>
                                <div class="col-sm-6 template-infos">
                                    <?php
                                    $icon;
                                    switch (EportfolioUser::getStatusOfUserInTemplate($template->id, $group_id, $cid)) {
                                        case 1:
                                            $icon = 'status-green';
                                            $title = _('Die Deadline ist noch nicht überschritten.');
                                            break;
                                        case 0:
                                            $icon = 'status-yellow';
                                            $title = _('Die Deadline nähert sich.');
                                            break;
                                        case -1:
                                            $icon = 'status-red';
                                            $title = _('Die Deadline ist überschritten!');
                                            break;
                                    }

                                    if ($timestamp == 0) {
                                        $icon = 'inactive';
                                        $title = _('Keine Deadline vorhanden.');
                                    }
                                    ?>
                                    <div class="template-infos-single" title="<?= $title ?>">
                                        <?php echo Icon::create('span-full', $icon); ?>
                                        <?= _('Status') ?>
                                    </div>

                                    <div class="template-infos-single">
                                        <?= Icon::create('date', 'clickable') ?>
                                        <?php
                                        if (!$timestamp == 0) {
                                            echo date('d.m.Y', $timestamp);
                                        } else {
                                            echo "kein Abgabedatum";
                                        }

                                        ?>
                                        <span style="margin-left: 20px;" class="template-infos-days-left"><br>
                      <?php if (!$timestamp == 0) {
                          echo "(noch " . Eportfoliomodel::getDaysLeft($group_id, $template->id) . " Tage)";
                      } else {
                          echo "&nbsp;";
                      } ?>
                    </span>
                                    </div>

                                    <div class="template-infos-single" title="Verteilt am">
                                        <?= Icon::create('activity', 'clickable') ?>
                                        <?= date('d.m.Y', EportfolioGroupTemplates::getWannWurdeVerteilt($group_id, $template->id)) ?>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="row template-kapitel-info">
                            <?php foreach (Eportfoliomodel::getChapters($template->id) as $chapter): ?>
                                <?php $current_block_id = Eportfoliomodel::getUserPortfolioBlockId($cid, $chapter['id']); ?>
                                <div class="col-sm-4 member-kapitelname"><?php echo $chapter['title'] ?></div>
                                <div class="col-sm-8">
                                    <div class="row member-icons">
                                        <div class="col-sm-4">
                                            <?php if (Eportfoliomodel::checkKapitelFreigabe($current_block_id)): ?>
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
                                                <?= Icon::create('forum', 'clickable'); ?>
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
                                            <?= $sharedChapters = Eportfoliomodel::getNumberOfSharedChaptersOfTemplateFromUser($template->id, $cid); ?>
                                            /
                                            <?= $allChapters = Eportfoliomodel::getNumberOfChaptersFromTemplate($template->id); ?>
                                        </div>
                                        <div class="member-footer-box-head">
                                            freigegeben
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="member-footer-box-big">
                                            <?= Eportfoliomodel::getProgressOfUserInTemplate($sharedChapters, $allChapters); ?>
                                            %
                                        </div>
                                        <div class="member-footer-box-head">
                                            bearbeitet
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="member-footer-box-big">
                                            <?= Eportfoliomodel::getNumberOfNotesInTemplateOfUser($template->id, $cid); ?>
                                        </div>
                                        <div class="member-footer-box-head">
                                            Notizen
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="template-user-item-footer">
                            <?= \Studip\LinkButton::create('Anschauen', Eportfoliomodel::getLinkOfFirstChapter($template->id, $cid)); ?>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </div>
</div>


<script type="text/javascript"
        src="<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'plugins_packages/uos/EportfolioPlugin/assets/js/eportfolio.js'; ?>"></script>
