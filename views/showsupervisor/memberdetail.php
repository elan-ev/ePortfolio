<!-- Header with Studentinformation and Portfolio Overview -->
<div class="row">
    <div class="col-sm-2 member-avatar">
        <?= Avatar::getAvatar($user_id, $user->username)
            ->getImageTag(Avatar::MEDIUM, [
                'style' => 'margin-right: 0px; border-radius: 75px; height: 75px; width: 75px; border: 1px solid #28497c;',
                'title' => htmlReady($user->Vorname . " " . $user->Nachname)
        ]); ?>
    </div>
    <div class="col-sm-5">
        <div class="member-name-detail">
            <?= htmlReady($user->getFullname()) ?>
        </div>
        <div class="member-subname">
            <?= $this->render_partial('showsupervisor/_studycourse.php', [
                'studycourses' => new SimpleCollection(UserStudyCourse::findByUser($user->id)),
            ]) ?>
            <br>
            <?= "Letzte Änderung: ".date('d.m.Y', Eportfoliomodel::getLastOwnerEdit($portfolio_id)) ?>
        </div>
        <a href="<?= URLHelper::getURL('dispatch.php/messages/write?rec_uname=' .$user->username) ?>" target="_blank">
            Nachricht schicken
        </a>
    </div>
    <div class="col-sm-5">
        <div class="row row member-footer-box-detail">
            <div class="col-sm-4">
                <div class="member-footer-box-big-detail">
                    <?= $portfolioSharedChapters ?> / <?= $chapterCount; ?>
                </div>
                <div class="member-footer-box-head">
                    freigegeben
                </div>
            </div>
            <div class="col-sm-4">
                <div class="member-footer-box-big-detail">
                    <?= EportfolioUser::getGesamtfortschrittInProzent($portfolioSharedChapters, $chapterCount); ?> %
                </div>
                <div class="member-footer-box-head">
                    bearbeitet
                </div>
            </div>
            <div class="col-sm-4">
                <div class="member-footer-box-big-detail">
                    <?= $notesCount; ?>
                </div>
                <div class="member-footer-box-head">
                    Notizen
                </div>
            </div>
        </div>
    </div>
</div>

<!-- list of all chapters displaying status, access, notes and feedback -->
<div class="member-contant-detail">
    <!-- table-header -->
    <div class="row member-containt-head-detail">
        <div class="col-sm-4">Kapitelname</div>
        <div class="col-sm-8">
            <div class="row member-content-icons">
                <div class="col-sm-2">Status</div>
                <div class="col-sm-2">Freigabe</div>
                <div class="col-sm-2">Notiz</div>
                <div class="col-sm-2">Feedback</div>
                <div class="col">Aktionen</div>
            </div>
        </div>
    </div>

    <!-- display information for every chapter and subchapter -->
    <? foreach ($chapterInfos as $kapitel): ?>
        <? $hasAccess = EportfolioFreigabe::hasAccess($GLOBALS['user']->id, $portfolio_id, $kapitel['id']) ?>
        <div class="row member-content-single-line <?= $kapitel['template_title'] ? '' : 'unlinked' ?>">
            <div class="col-sm-4 member-content-single-line-ober">
                <?= $kapitel['title'] ?>

                <? if (!$kapitel['template_title']) : // chapter does not belong to a template ?>
                    <?= tooltipIcon('Dieses Kapitel stammt nicht aus einer Vorlage!') ?>
                <? endif ?>
            </div>
            <div class="col-sm-8">
                <div class="row" style="text-align: center;">
                    <div class="col-sm-2">
                        <?
                        $status = EportfolioUser::getStatusOfChapter($kapitel);
                        switch ($status) {
                            case 2:
                                $icon = "inactive";
                                break;
                            case 1:
                                $icon = "status-green";
                                break;
                            case 0:
                                $icon = "status-yellow";
                                break;
                            case -1:
                                $icon = "status-red";
                                break;
                        }
                        ?>
                        <?= Icon::create('span-full', $icon); ?>
                    </div>
                    <div class="col-sm-2" style="text-align: center;">
                        <? if ($kapitel['shareDate']): ?>
                            <? if ($lastVisit  < $kapitel['shareDate']): ?>
                                <?= Icon::create('accept+new', 'status-green'); ?>
                            <? else: ?>
                                <?= Icon::create('accept', 'status-green'); ?>
                            <? endif; ?>
                        <? else: ?>
                            <?= Icon::create('decline', 'status-red'); ?>
                        <? endif; ?>
                    </div>
                    <div class="col-sm-2"></div>
                    <div class="col-sm-2"></div>
                    <div class="col member-aktionen-detail">
                        <? if ($hasAccess): ?>
                            <a href="<?= URLHelper::getLink("plugins.php/courseware/courseware?cid=" . $portfolio_id
                                    . "&selected=" . $kapitel['id'] . '&return_to=' . Context::getId()); ?>"
                                target="_blank"
                            >
                                Anschauen
                            </a>
                        <? else : ?>
                            Nicht freigegeben
                            <?= tooltipIcon("Das Anschauen ist nicht möglich, da der Nutzer dieses Kapitel noch nicht freigegeben hat") ?>
                        <? endif ?>
                    </div>
                </div>
            </div>

            <!-- display information for subchapters | 76 queries-->
            <? foreach (Eportfoliomodel::getSubChapters($kapitel['id']) as $unterkapitel): ?>
                <div class="col-sm-4 member-content-unterkapitel">
                    <?= $unterkapitel['title']; ?>
                </div>
                <div class="col-sm-8">
                    <div class="row member-content-icons">
                        <div class="col-sm-2"></div>
                        <div class="col-sm-2"></div>
                        <div class="col-sm-2">
                            <? if ($subchapterNotes = Eportfoliomodel::checkSupervisorNoteInSubchapter($unterkapitel['id'])): ?>
                                <?= Icon::create('file', 'clickable', [
                                    'title' => 'Notiz vorhanden'
                                ]); ?>
                            <? else: ?>
                                <?= Icon::create('file', 'inactive', [
                                    'title' => 'Keine Notiz hinterlegt'
                                ]); ?>
                            <? endif; ?>
                        </div>
                        <div class="col-sm-2">
                            <? if (Eportfoliomodel::checkSupervisorResonanzInSubchapter($unterkapitel['id'])): ?>
                                <?= Icon::create('forum'); ?>
                            <? else: ?>
                                <?= Icon::create('forum', 'inactive'); ?>
                            <? endif; ?>
                        </div>
                        <div class="col member-aktion-detail">
                            <? if ($subchapterNotes): ?>
                                <a href="<?= URLHelper::getLink("plugins.php/courseware/courseware?cid=" . $portfolio_id . "&selected=" . $unterkapitel['id'] . '&return_to=' . Context::getId()); ?>">
                                    Notiz beantworten
                                </a>
                            <? endif; ?>
                        </div>
                    </div>
                </div>
            <? endforeach; ?>
        </div>
    <? endforeach; ?>
    </div>
</div>
