<? $userPortfolioId = EportfolioGroup::getPortfolioIdOfUserInGroup($user->id, $groupId); ?>

<div class="col-sm-4 member-single-card">
    <? if ($userPortfolioId): ?>
    <a class="member-link" data-dialog="size=1000px;"
       href="<?= URLHelper::getLink('plugins.php/eportfolioplugin/showsupervisor/memberdetail/' . $groupId . '/' . $user->id) ?>">
    <? endif; ?>
        <div class="member-item">
            <div class="row">
                <div class="col-sm-4">
                    <div class="member-avatar">
                        <?= Avatar::getAvatar($user->id, $user->getFullname())->getImageTag(
                            Avatar::MEDIUM,
                            ['style' => 'margin-right: 0px; border-radius: 75px; height: 75px; width: 75px; border: 1px solid #28497c;',
                             'title' => htmlReady($user->getFullname())
                            ]); ?>
                    </div>
                </div>
                <div class="col-sm-8">
                    <div class="member-name">
                        <?= htmlReady($user->getFullname()) ?>
                    </div>
                    <div class="member-subname">
                        <?= _('Status:') ?>
                        <? $icon = "";
                        switch (EportfolioUser::getStatusOfUserInGroup($groupId, $userPortfolioId, $GLOBALS['user']->id)) {
                            case 1:
                                $icon = Icon::ROLE_STATUS_GREEN;
                                break;
                            case 0:
                                $icon = Icon::ROLE_STATUS_YELLOW;
                                break;
                            case -1:
                                $icon = Icon::ROLE_STATUS_RED;
                                break;
                        } ?>
                        <?= Icon::create('span-full', $icon);
                        ?><br>
                        <?= $this->render_partial('showsupervisor/_studycourse.php', [
                            'studycourses' => new SimpleCollection(UserStudyCourse::findByUser($user->id)),
                        ]) ?>
                        <br><?= sprintf(_('Letzte Änderung: %s'), date('d.m.Y', Eportfoliomodel::getLastOwnerEdit($userPortfolioId))) ?>
                    </div>
                </div>

                <? if (EportfolioGroupTemplates::checkMissingTemplate($groupId, $userPortfolioId, $portfolioChapters)) : ?>
                <div class="col-sm-12">
                    <div class="member-content">
                        <div class="row">
                            <div class="verteilen-bandage">
                                <p><?= _('Es wurden noch nicht alle Vorlagen verteilt. ') ?></p>

                                <?php
                                /**
                                 * wegen CSS problemen bei einem Link im Link, vorerst die Lösung über onClick via js
                                 * **/
                                $link = URLHelper::getLink('plugins.php/eportfolioplugin/showsupervisor/createlateportfolio/' . $groupId . '/' . $user->id . '/' . $userPortfolioId, []);
                                ?>

                                <div class="btn-verteilen"
                                     onclick="window.location = '<?= $link ?>'">
                                    <?= \Studip\Button::create(_('Jetzt verteilen!'), 'verteilen', []); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <? else: ?>
                <div class="col-sm-12">
                    <div class="row member-footer-box">
                        <div class="col-sm-4">
                            <div class="member-footer-box-big">
                                <?= $portfolioSharedChapters = EportfolioUser::portfolioSharedChapters(
                                    $userPortfolioId, EportfolioGroupTemplates::getUserChapterInfos($groupId, $userPortfolioId)
                                ); ?>
                                /
                                <?= $portfolioChapters ?>
                            </div>
                            <div class="member-footer-box-head">
                                freigegeben
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="member-footer-box-big">
                                <?= EportfolioUser::getGesamtfortschrittInProzent($portfolioSharedChapters, $portfolioChapters); ?>
                                %
                            </div>
                            <div class="member-footer-box-head">
                                <?= _('bearbeitet') ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="member-footer-box-big">
                                <?= EportfolioUser::getAnzahlNotizen($userPortfolioId); ?>
                            </div>
                            <div class="member-footer-box-head">
                                <?= _('Notizen') ?>
                            </div>
                        </div>
                    </div>
                </div>
                <? endif ?>

            </div>
        </div>
    </a>
</div>
