<?php $userPortfolioId = EportfolioGroup::getPortfolioIdOfUserInGroup($user->id, $id); ?>
<div class="col-sm-4 member-single-card">
    <?php if ($userPortfolioId): ?>
    <a class="member-link" data-dialog="size=1000px;"
       href="<?= $controller->url_for('showsupervisor/memberdetail/' . $id . '/' . $user->id) ?>">
        <?php endif; ?>
        <div class="member-item">

            <div class="member-notification">
                <?php // echo EportfolioGroup::getAnzahlAnNeuerungen($user, $id);  ?>
            </div>

            <div class="row">
                <div class="col-sm-4">
                    <div class="member-avatar">
                        <?= Avatar::getAvatar($user->id)->getImageTag(
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
                        switch (EportfolioUser::getStatusOfUserInGroup($user->id, $id, $userPortfiloId)) {
                            case 1:
                                $icon = Icon::ROLE_STATUS_GREEN;
                                break;
                            case 0:
                                $icon = Icon::ROLE_STATUS_YELLOW;
                                break;
                            case -1:
                                $icon = Icon::ROLE_STATUS_RED;
                                break;
                        }
                        ?>

                        <?= Icon::create('span-full', $icon);
                        ?><br>
                        <?= _('Studiengang etc') ?>
                        <br><?= sprintf(_('Letzte Änderung: %s'), date('d.m.Y', Eportfoliomodel::getLastOwnerEdit($userPortfolioId))) ?>
                    </div>
                </div>

                <? $missing_vorlage = false; ?>
                <? foreach (EportfolioGroup::getAllMarkedAsFav($id) as $vorlage): ?>
                    <? foreach (Eportfoliomodel::getChapters($vorlage) as $chapter): ?>
                        <? if (!Eportfoliomodel::getUserPortfolioBlockId($userPortfolioId, $chapter['id'])): ?>
                            <? $missing_vorlage = true; break 2 ?>
                        <? endif; ?>
                    <? endforeach; ?>
                <? endforeach; ?>

                <? if ($missing_vorlage) : ?>
                <div class="col-sm-12">
                    <div class="member-content">
                        <div class="row">
                            <div class="verteilen-bandage">
                                <p><?= _('Es wurden noch nicht alle Vorlagen verteilt. ') ?></p>

                                <?php
                                /**
                                 * wegen CSS problemen bei einem Link im Link, vorerst die Lösung über onClick via js
                                 * **/
                                $link = URLHelper::getLink('plugins.php/eportfolioplugin/showsupervisor/createlateportfolio/' . $id . '/' . $user->id, []);
                                ?>

                                <div class="btn-verteilen"
                                     onclick="window.location = '<?php echo $link; ?>'">
                                    <?= \Studip\Button::create(_('Jetzt verteilen!'), 'verteilen', []); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <? endif ?>


                <div class="col-sm-12">
                    <div class="row member-footer-box">
                        <div class="col-sm-4">
                            <div class="member-footer-box-big">
                                <?= EportfolioGroup::getAnzahlFreigegebenerKapitel($user->id, $id); //id soll die gruppenid sein      ?>
                                /
                                <?= EportfolioGroup::getAnzahlAllerKapitel($id); ?>
                            </div>
                            <div class="member-footer-box-head">
                                freigegeben
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="member-footer-box-big">
                                <?= EportfolioGroup::getGesamtfortschrittInProzent($user->id, $id); ?>
                                %
                            </div>
                            <div class="member-footer-box-head">
                                <?= _('bearbeitet') ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="member-footer-box-big">
                                <?= EportfolioGroup::getAnzahlNotizen($user->id, $id); ?>
                            </div>
                            <div class="member-footer-box-head">
                                <?= _('Notizen') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>
