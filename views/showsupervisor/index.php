<div>
    <div id="wrapper_table_tamplates" style="margin-top: 30px;">

        <table id="table_templates" class="default">
            <caption>
                <?= _('Portfolio Vorlagen')?>
                <span class="actions">
                    <a data-dialog="size=auto;reload-on-close" href="<?= $controller->url_for('show/createvorlage') ?>">
                    <?= Icon::create('add', 'clickable')->asImg(20, tooltip2(_('Neue Vorlage erstellen')) + ['style' => 'cusros: pointer']) ?>
                        </a>
                </span>
            </caption>
            <colgroup>
                <col width="30%">
                <col width="30%">
                <col width="10%">
                <col width="30%">
                <col width="10%">
                <col width="5%">
            </colgroup>
            <thead>
                <tr class="sortable">
                    <th><?= _('Titel der Vorlage') ?></th>
                    <th><?= _('Beschreibung') ?></th>
                    <th><?= _('Erstellt') ?></th>
                    <th><?= _('Details') ?></th>
                    <th><?= _('Aktionen') ?></th>
                    <th><?= _('Anzeigen') ?></th>
                </tr>
            </thead>

            <tbody>
                <? foreach ($portfolios as $portfolio): ?>
                    <tr>
                        <td><?= htmlReady($portfolio->getFullName()) ?></td>
                        <td><?= htmlReady($portfolio->beschreibung) ?></td>
                        <td><?= htmlReady(date('d.m.Y', $portfolio->mkdate)) ?></td>
                        <td>
                            <?php if (EportfolioGroupTemplates::checkIfGroupHasTemplate($id, $portfolio->id)): ?>
                                <div title="<?= _('Verteilt von') ?>">
                                    <?= Icon::create('own-license') ?>
                                    <?= EportfolioGroupTemplates::getCreatorName($id, $portfolio->id); ?>
                                </div>
                                <div title="Verteilt am">
                                    <?= Icon::create('share') ?>
                                    <?= date('d.m.Y', EportfolioGroupTemplates::getWannWurdeVerteilt($id, $portfolio->id)); ?>
                                </div>
                                <div>
                                <? if ($timestamp = EportfolioGroupTemplates::getDeadline($id, $portfolio->id)): ?>
                                    <a data-dialog="size=1000px;"
                                       href="<?= $controller->url_for('showsupervisor/templatedates/' . $id . '/' . $portfolio->id) ?>">
                                        <?= Icon::create('date', Icon::ROLE_CLICKABLE) ?>
                                        <?= sprintf(_('Abgabetermin: %s'), date('d.m.Y', $timestamp)) ?>
                                    </a>
                                    </div>
                                <? else: ?>
                                    <div title="<?= _('Abgabetermin bearbeiten') ?>">
                                        <a data-dialog="size=1000px;"
                                           href="<?= $controller->url_for('showsupervisor/templatedates/' . $id . '/' . $portfolio->id) ?>">
                                            <?= Icon::create('date', Icon::ROLE_CLICKABLE) ?>
                                            <?= _('Kein Abgabetermin') ?>
                                        </a>
                                    </div>
                                <? endif ?>
                            <? endif ?>
                        </td>
                        <td style="text-align: center;">
                            <?php $groupHasTemplate = EportfolioGroupTemplates::checkIfGroupHasTemplate($id, $portfolio->id)?>
                            <a href="<?= URLHelper::getLink('plugins.php/courseware/courseware', ['cid' => $portfolio->id]); ?>">
                                <?= Icon::create('edit', Icon::ROLE_CLICKABLE, ['title' => sprintf(_('Portfolio-Vorlage bearbeiten.'))]) ?>
                            </a>
                           
                            <? if ($member && !$groupHasTemplate): ?>
                                <a data-confirm="<?= _('Vorlage an Teilnehmende verteilen') ?>"
                                   href="<?= $controller->url_for('showsupervisor/createportfolio/' . $portfolio->id) ?>">
                                    <?= Icon::create('add', Icon::ROLE_CLICKABLE, tooltip2(_('Portfolio-Vorlage an Gruppenmitglieder verteilen.')) + ['cursor' => 'pointer']) ?>
                                </a>
                            <? else: ?>
                                <?= Icon::create('check-circle', Icon::ROLE_CLICKABLE, tooltip2(_('Vorlage wurde in dieser Gruppe bereits verteilt.'))) ?>
                            <? endif ?>
                        </td>
                        <td style="text-align: center;">
                            <? if ($member && !$groupHasTemplate): ?>
                                <? if (EportfolioGroup::checkIfMarkedAsFav($id, $portfolio->id) == 0): ?>
                                    <a href="<?= $controller->url_for('showsupervisor/addAsFav/' . $id . '/' . $portfolio->id); ?>">
                                        <?= Icon::create('visibility-invisible', Icon::ROLE_CLICKABLE) ?>
                                    </a>
                                <? else: ?>
                                    <a href="<?= $controller->url_for('showsupervisor/deleteAsFav/' . $id . '/' . $portfolio->id); ?>">
                                        <?= Icon::create('visibility-visible', Icon::ROLE_ATTENTION) ?>
                                    </a>
                                <? endif ?>
                            <? endif ?>
                        </td>
                    </tr>
                <? endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <? if (empty($groupTemplates)): ?>
        <h4><?= _('Gruppenmitglieder') ?></h4>
        
        <? if (!$member): ?>
            <?= MessageBox::info('Es sind noch keine Nutzer in der der Gruppe eingetragen'); ?>
        <? else: ?>
            <table class="default">
                <colgroup>
                    <col width="30%">
                    <col width="60%">
                </colgroup>
                <tr>
                    <th>Name</th>
                    <th></th>
                    <th>Aktionen</th>
                </tr>
                <?php foreach ($member as $user): ?>
                    <tr>
                        <td>
                            <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $user->username) ?>">
                                <?= Avatar::getAvatar($user->id)->getImageTag(Avatar::SMALL,
                                    [
                                        'style' => 'margin-right: 5px; border-radius: 25px; width: 25px; border: 1px solid #28497c;',
                                        'title' => htmlReady($user->getFullname())
                                    ]) ?>
                                <?= htmlReady($user->getFullname()) ?>
                            </a>
                        </td>
                        <td></td>
                        <td style="text-align:center;">
                            <a href="<?= $controller->url_for(sprintf('showsupervisor/deleteUserFromGroup/%s/%s', $user->id, $id)) ?>">
                                <?= Icon::create('trash', 'clickable', ['title' => sprintf(_('Nutzer aus Gruppe austragen'))]) ?>
                            </a>
                    </tr>
                <? endforeach; ?>
            </table>
        
        <? endif; ?>
    
    <? else: ?>
        <div class="grid-container">
            <div class="row member-container">
                <?php foreach ($member as $user): ?>
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
                                        <div class="row member-links">
                                            <div class="col-sm-4"><?= Icon::create('mail', 'clickable'); ?></div>
                                            <div class="col-sm-4"><?= Icon::create('eportfolio', 'clickable'); ?></div>
                                            <div class="col-sm-4"><?= Icon::create('accept', 'clickable'); ?></div>
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
                                    <div class="col-sm-12">
                                        
                                        <?php $favVorlagen = EportfolioGroup::getAllMarkedAsFav($id); ?>
                                        <div class="member-content">
                                            <div class="row">
                                                <?php $x = 0; ?>
                                                <? foreach ($favVorlagen as $vorlage): ?>
                                                    <? foreach (Eportfoliomodel::getChapters($vorlage) as $chapter): ?>
                                                        <?php $current_block_id = Eportfoliomodel::getUserPortfilioBlockId($userPortfolioId, $chapter['id']); ?>
                                                        
                                                        <? if ($current_block_id): ?>
                                                            <div class="col-sm-4 member-kapitelname"><?= $chapter['title'] ?></div>
                                                            <div class="col-sm-8">
                                                                <div class="row member-icons">
                                                                    <div class="col-sm-4">
                                                                        <? if (Eportfoliomodel::checkKapitelFreigabe($current_block_id)): ?>
                                                                            <? $new_freigabe = LastVisited::chapter_last_visited($current_block_id, $user->id) < EportfolioFreigabe::hasAccessSince($supervisorGroupId, $current_block_id); ?>
                                                                            <? if ($new_freigabe): ?>
                                                                                <?= Icon::create('accept+new', 'clickable'); ?>
                                                                            <? else: ?>
                                                                                <?= Icon::create('accept', 'clickable'); ?>
                                                                            <? endif ?>
                                                                        <? else: ?>
                                                                            <?= Icon::create('decline', 'inactive'); ?>
                                                                        <? endif ?>
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                            <?= Icon::create('file', 'inactive'); ?>
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <? if (Eportfoliomodel::checkSupervisorResonanz($current_block_id) == true): ?>
                                                                            <?= Icon::create('forum', 'clickable'); ?>
                                                                        <? endif ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <? else: ?>
                                                            <?php $x++; ?>
                                                        <? endif; ?>
                                                        
                                                        <? if ($x == 1): ?>
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
                                                        <? endif; ?>
                                                    <? endforeach; ?>
                                                <? endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
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
                <? endforeach; ?>
            </div>
        </div>
    <? endif; ?>
</div>

<!-- Legende -->
<div class="legend">
    <ul>
        <li><?php echo Icon::create('decline', 'inactive'); ?> Kapitel/Impuls noch nicht freigeschaltet</li>
        <li><?php echo Icon::create('accept', 'clickable'); ?> Kapitel/Impuls freigeschaltet</li>
        <li><?php echo Icon::create('accept+new', 'clickable'); ?></i>  Kapitel freigeschaltet und Änderungen seit ich
            das letzte mal reingeschaut habe
        </li>
        <li><?php echo Icon::create('file', 'inactive'); ?> keine Supervisionsanliegen freigeschaltet</li>
        <li><?php echo Icon::create('file', 'clickable'); ?> Supervisionsanliegen freigeschaltet</li>
        <li><?php echo Icon::create('forum', 'clickable'); ?> Resonanz gegeben</li>
    </ul>
</div>


<script type="text/javascript">

    function deleteUserFromGroup(userid, obj) {
        var deleteThis = $(obj).parents('tr');
        var tdParent = $(obj).parents('td');
        var urlDeleteUser = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/showsupervisor');

        $(obj).parents('td').append('<i style="color: #24437c;" class="fa fa-circle-o-notch fa-spin fa-fw"></i>');
        $(obj).remove();


        $.ajax({
            type: "POST",
            url: urlDeleteUser,
            data: {
                action: 'deleteUserFromGroup',
                userId: userid,
                seminar_id: '<?php echo $id ?>',
            },
            success: function (data) {
                $(deleteThis).fadeOut();
            }
        });
    }

</script>
