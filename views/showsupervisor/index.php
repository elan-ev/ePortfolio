<div>
    <?= $this->render_partial('showsupervisor/_templates', [
        'title'             => _('Verteilte Vorlagen'),
        'missing_text'      => _('Es werden bisher keine der vorhandenen Vorlagen verwendet.'),
        'hide_add'          => true,
        'portfolios'        => $distributedPortfolios,
        'hasTemplate'  => true
    ]) ?>

    <?= $this->render_partial('showsupervisor/_templates', [
        'title'        => _('Verfügbare Vorlagen'),
        'missing_text' => _('Keine Vorlagen vorhanden oder alle Vorlagen sind verteilt oder archiviert.'),
        'portfolios'   => array_udiff($portfolios, $distributedPortfolios, function($portfolio, $distributedPortfolio) {;
            if($portfolio->id === $distributedPortfolio->id) {
                return 0;
            } elseif($portfolio->id > $distributedPortfolio->id) {
                return 1;
            } else {
                return -1;
            }
        })
    ]) ?>

    <? if (empty($distributedPortfolios)): ?>
        <? if (!$member): ?>
            <?= MessageBox::info('Es sind noch keine Nutzer in der Veranstaltung eingetragen'); ?>
        <? else: ?>
            <table class="default">
                <caption><?= _('Gruppenmitglieder') ?></caption>
                <tr>
                    <th>Name</th>
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
                    </tr>
                <? endforeach; ?>
            </table>
        <? endif; ?>

    <? else: ?>
        <? if (sizeof($member) > 30) : ?>
            <table class="default">
                <caption>Teilnehmende</caption>

                <colgroup>
                    <col width="20%">
                    <col width="5%">
                    <col width="10%">
                    <col width="10%">
                    <col width="10%">
                    <col width="25%">
                    <col width="20%">
                </colgroup>

                <thead>
                    <th>Nachname, Vorname</th>
                    <th>Status</th>
                    <th>Freigaben</th>
                    <th>Bearbeitet</th>
                    <th>Notizen</th>
                    <th>Studiengang</th>
                    <th></th>
                </thead>

                <tbody>
                    <?php foreach ($member as $user): ?>
                        <?= $this->render_partial('showsupervisor/_member_tablerow', compact('user', 'groupId', 'portfolioChapters')) ?>
                    <? endforeach; ?>
                </tbody>
            </table>

        <? else : ?>
            <span style="color: #3c434e;font-size: 1.4em;">
                Teilnehmende
            </span>

            <div class="grid-container">
                <div class="row member-container">
                    <?php foreach ($member as $user): ?>
                        <?= $this->render_partial('showsupervisor/_member', compact('user', 'groupId', 'portfolioChapters')) ?>
                    <? endforeach; ?>
                </div>
            </div>
        <? endif ?>
    <? endif; ?>
</div>

<!-- Legende -->
<div class="legend">
    <ul>
        <li><?= Icon::create('decline', 'inactive'); ?> Kapitel/Impuls noch nicht freigeschaltet</li>
        <li><?= Icon::create('accept', 'clickable'); ?> Kapitel/Impuls freigeschaltet</li>
        <li><?= Icon::create('accept+new', 'clickable'); ?></i>  Kapitel/Impuls freigeschaltet und Änderungen seit Ihrem letzten Besuch</li>
        <li><?= Icon::create('file', 'inactive'); ?> keine Supervisionsanliegen freigeschaltet</li>
        <li><?= Icon::create('file', 'clickable'); ?> Supervisionsanliegen freigeschaltet</li>
        <li><?= Icon::create('forum', 'clickable'); ?> Feedback gegeben</li>

        <li>
            <?= Icon::create('span-full', Icon::ROLE_STATUS_GREEN); ?>
            <?= Icon::create('span-full', Icon::ROLE_STATUS_YELLOW); ?>
            <?= Icon::create('span-full', Icon::ROLE_STATUS_RED); ?>
            Diese Status-Icons geben an, wie gut der Lernende bei Abgabeterminen in der Zeit liegt.
            Wenn für keine Vorlage eine Deadline gesetzt wurde, wird das Status-Icon immer grün anzeigen.
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
</script>
