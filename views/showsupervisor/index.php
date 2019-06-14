<div>
    <div id="wrapper_table_tamplates" style="margin-top: 30px;">

        <table id="table_templates" class="default collapsable tablesorter">
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
                <col width="15%">
                <col width="15%">
                <col width="10%">
                <col width="5%">
            </colgroup>
            <thead>
                <tr class="sortable">
                    <th><?= _('Titel der Vorlage') ?></th>
                    <th><?= _('Beschreibung') ?></th>
                    <th><?= _('Anlagedatum') ?></th>
                    <th data-sorter="false"><?= _('Details') ?></th>
                    <th><?= _('Abgabedatum') ?></th>
                    <th data-sorter="false"><?= _('Aktionen') ?></th>
                    <th data-sorter="false"><?= _('Anzeigen') ?></th>
                </tr>
            </thead>

            <tbody>
                <? foreach ($portfolios as $portfolio): ?>
                    <tr>
                        <td>
                            <a href="<?= URLHelper::getLink('plugins.php/courseware/courseware', ['cid' => $portfolio->id]); ?>">
                                <?= htmlReady($portfolio->getFullName()) ?>
                            </a>
                        </td>
                        <td><?= htmlReady($portfolio->beschreibung) ?></td>
                        <td>
                            <span style="display:none"><?= $portfolio->mkdate ?></span>
                            <?= htmlReady(date('d.m.Y', $portfolio->mkdate)) ?>
                        </td>
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


                            <? endif ?>
                        </td>
                        <td>
                        <?php if (EportfolioGroupTemplates::checkIfGroupHasTemplate($id, $portfolio->id)): ?>
                            <? $timestamp = EportfolioGroupTemplates::getDeadline($id, $portfolio->id) ?>
                            <span style="display:none"><?= $timestamp ?: 1 ?></span>
                            <? if ($timestamp): ?>
                            <div>
                                <a data-dialog="size=auto;"
                                   href="<?= $controller->url_for('showsupervisor/templatedates/' . $id . '/' . $portfolio->id) ?>">
                                    <?= Icon::create('date', Icon::ROLE_CLICKABLE) ?>
                                    <?= sprintf(_('Abgabetermin: %s'), date('d.m.Y', $timestamp)) ?>
                                </a>
                            </div>
                            <? else: ?>
                            <div title="<?= _('Abgabetermin bearbeiten') ?>">
                                <a data-dialog="size=auto;"
                                   href="<?= $controller->url_for('showsupervisor/templatedates/' . $id . '/' . $portfolio->id) ?>">
                                    <?= Icon::create('date', Icon::ROLE_CLICKABLE) ?>
                                    <?= _('Kein Abgabetermin') ?>
                                </a>
                            </div>
                            <? endif ?>
                        <? else : ?>
                        <span style="display:none">0</span>
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
                                    <?= Icon::create('share', Icon::ROLE_CLICKABLE, tooltip2(_('Portfolio-Vorlage an Gruppenmitglieder verteilen.')) + ['cursor' => 'pointer']) ?>
                                </a>
                            <? else: ?>
                                <?= Icon::create('check-circle', Icon::ROLE_CLICKABLE, tooltip2(_('Vorlage wurde in dieser Gruppe bereits verteilt.'))) ?>
                            <? endif ?>
                        </td>
                        <td style="text-align: center;">
                            <? if ($member && $groupHasTemplate): ?>
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
                    <?= $this->render_partial('showsupervisor/_member', compact('user', 'id')) ?>
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
        <li><?php echo Icon::create('forum', 'clickable'); ?> Feedback gegeben</li>

        <li>
            <?= Icon::create('span-full', Icon::ROLE_STATUS_GREEN); ?>
            <?= Icon::create('span-full', Icon::ROLE_STATUS_YELLOW); ?>
            <?= Icon::create('span-full', Icon::ROLE_STATUS_RED); ?>

            Diese Status-Icons geben an, wie gut die Person in der Zeit liegt (bei Abgabeterminen)
            <br> und ob alle Aufgaben bearbeitet wurden.
        </li>
    </ul>
</div>


<script type="text/javascript">

        jQuery(function () {
            jQuery("#table_templates").tablesorter({
                sortList: [[0,0]],
                cssAsc: 'sortasc',
                cssDesc: 'sortdesc'
            });
        });

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
