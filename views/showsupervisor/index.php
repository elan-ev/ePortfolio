<div>
    <?= $this->render_partial('showsupervisor/_templates', [
        'title'        => _('Verteilte Vorlagen'),
        'missing_text' => _('Es werden bisher keine der vorhandenen Vorlagen verwendet.'),
        'hide_add'     => true,
        'portfolios'   => array_filter($portfolios, function($portfolio) use ($id) {
            return EportfolioGroupTemplates::checkIfGroupHasTemplate($id, $portfolio->id);
        })
    ]) ?>

    <?= $this->render_partial('showsupervisor/_templates', [
        'title'        => _('Verfügbare Vorlagen'),
        'missing_text' => _('Keine Vorlagen vorhanden oder alle Vorlagen sind verteilt oder archiviert.'),
        'portfolios'   => array_filter($portfolios, function($portfolio) use ($id) {
            return !EportfolioGroupTemplates::checkIfGroupHasTemplate($id, $portfolio->id);
        })
    ]) ?>

    <? if (empty($groupTemplates)): ?>
        <h4><?= _('Gruppenmitglieder') ?></h4>

        <? if (!$member): ?>
            <?= MessageBox::info('Es sind noch keine Nutzer in der Gruppe eingetragen'); ?>
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
        <span style="color: #3c434e;font-size: 1.4em;">
            Teilnehmende
        </span>
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
        jQuery("table.tablesorter").tablesorter({
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
