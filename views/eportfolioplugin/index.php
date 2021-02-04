<div class="row">
    <div class="col-md-12">

        <div class="row member-container">
            <? foreach ($templates as $template_id => $chapters): ?>

                <? $sharedChapterCnt = EportfolioFreigabe::sharedChapters($group_id, [$template_id => $chapters]) ?>
                <? $supervisorNotesCnt = EportfolioModel::countSupervisorNotiz(array_keys(array_column($chapters, NULL, 'id'))) ?>

                <div class="col-sm-4 member-single-card">
                    <div class="template-user-item">
                        <div class="template-user-item-head">

                            <div class="template-user-item-headline">
                                <?= Seminar::getInstance($template_id)->getName() ?>
                            </div>

                            <? $deadline = EportfolioGroupTemplates::getDeadline($group_id, $template_id) ?>

                            <div class="row">
                                <? switch (EportfolioUser::getStatusOfUserInTemplate($deadline, $sharedChapterCnt, count($chapters))) {
                                    case 1:
                                        $icon = 'status-green';
                                        $title = _('Alle Kapitel wurden freigegeben.');
                                        break;
                                    case 0:
                                        $icon = 'status-yellow';
                                        $title = _('Die Deadline nähert sich.');
                                        break;
                                    case -1:
                                        $icon = 'status-red';
                                        $title = _('Die Deadline ist überschritten!');
                                        break;
                                    default:
                                        $icon = 'inactive';
                                        $title = _('Keine Deadline vorhanden.');
                                } ?>

                                <div class="template-infos-single" title="<?= $title ?>">
                                    <?= _('Status: ') ?>
                                    <?= Icon::create('span-full', $icon) ?>
                                </div>

                                <div class="template-infos-single" title="Verteilt am" style="margin-left: 100px;">
                                    <?= Icon::create('activity', 'clickable') ?>
                                    <?= date('d.m.Y', EportfolioGroupTemplates::getWannWurdeVerteilt($group_id, $template_id)) ?>
                                </div>
                            </div>

                            <div class="template-infos-single">
                                <?= Icon::create('date', 'clickable') ?>
                                <?= $deadline ? date('d.m.Y', $deadline) : _("kein Abgabedatum") ?>

                                <? if ($deadline >= time()) : ?>
                                    <span class="template-infos-days-left">
                                    <?= $deadline ? "(noch " . EportfolioModel::getDaysLeft($deadline) . " Tage)" : "" ?>
                                    </span>
                                <? endif ?>
                            </div>
                        </div>

                        <div class="row template-kapitel-info">
                            <? foreach ($chapters as $chapter): ?>
                                <div class="col-sm-4 member-kapitelname"><?= $chapter['title'] ?></div>
                                <div class="col-sm-8">
                                    <div class="row member-icons">
                                        <div class="col-sm-4">
                                            <? if (EportfolioFreigabe::getAccess($supervisorgroup->id, $chapter['id'])): ?>
                                                <?= Icon::create('accept', 'status-green', [
                                                    'title' => 'Freigabe erteilt'
                                                ]); ?>
                                            <? else: ?>
                                                <?= Icon::create('accept', 'inactive', [
                                                    'title' => 'Freigabe nicht erteilt'
                                                ]); ?>
                                            <? endif ?>
                                        </div>
                                        <div class="col-sm-4">
                                            <? if (EportfolioModel::checkSupervisorNotiz($chapter['id'])): ?>
                                                <a href="<?= URLHelper::getLink('plugins.php/courseware/courseware?cid=' . $cid . '&selected=' . $chapter['id']) ?>">
                                                    <?= Icon::create('file', 'clickable', [
                                                        'title' => 'Notiz vorhanden'
                                                    ]) ?>
                                                </a>
                                            <? else: ?>
                                                <?= Icon::create('file', 'inactive', [
                                                    'title' => 'Keine Notiz an Lehrende erstellt'
                                                ]); ?>
                                            <? endif ?>
                                        </div>
                                        <div class="col-sm-4">
                                            <? if (EportfolioModel::checkSupervisorResonanz($chapter['id'])): ?>
                                                <a href="<?= URLHelper::getLink('plugins.php/courseware/courseware?cid=' . $cid . '&selected=' . $chapter['id']) ?>">
                                                    <?= Icon::create('forum', 'clickable', [
                                                        'title' => 'Feedback vorhanden'
                                                    ]) ?>
                                                </a>
                                            <? else: ?>
                                                <?= Icon::create('forum', 'inactive', [
                                                    'title' => 'Noch kein Feedback vorhanden'
                                                ]); ?>
                                            <? endif ?>
                                        </div>
                                    </div>
                                </div>
                            <? endforeach ?>
                        </div>

                        <div class="row template-user-stats-area">
                            <div class="col-sm-12">
                                <div class="row member-footer-box">
                                    <div class="col-sm-4">
                                        <div class="member-footer-box-big">
                                            <?= $sharedChapterCnt ?>
                                            /
                                            <?= count($chapters) ?>
                                        </div>
                                        <div class="member-footer-box-head">
                                            freigegeben
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="member-footer-box-big">
                                            <?= EportfolioUser::getGesamtfortschrittInProzent($sharedChapterCnt, count($chapters)) ?>
                                            %
                                        </div>
                                        <div class="member-footer-box-head">
                                            bearbeitet
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="member-footer-box-big">
                                            <?= $supervisorNotesCnt ?>
                                        </div>
                                        <div class="member-footer-box-head">
                                            Notizen
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="template-user-item-footer">
                            <?= \Studip\LinkButton::create('Anschauen', EportfolioModel::getLinkOfFirstChapter($template_id, $cid)) ?>
                        </div>

                    </div>
                </div>
            <? endforeach ?>

        </div>
    </div>
</div>
