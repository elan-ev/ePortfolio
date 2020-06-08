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
            <?= MessageBox::info(_('Es sind noch keine Nutzer in der Veranstaltung eingetragen')); ?>
        <? else: ?>
            <table class="default">
                <caption><?= _('Gruppenmitglieder') ?></caption>
                <tr>
                    <th><?= _('Name')?></th>
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
        <? if (is_array($member) && count($member) > 30) : ?>
            <table class="default sortable-table">
                <caption><?= _('Teilnehmende')?></caption>
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
                    <th data-sorter="text"><?= _('Nachname, Vorname')?></th>
                    <th><?= _('Status')?></th>
                    <th><?= _('Freigaben')?></th>
                    <th data-sorter="number"><?= _('Bearbeitet')?></th>
                    <th><?= _('Notizen')?></th>
                    <th><?= _('Studiengang')?></th>
                    <th></th>
                </thead>
                <tbody>
                    <?php foreach ($member as $user): ?>
                        <?= $this->render_partial('showsupervisor/_member_tablerow', compact('user', 'groupId', 'portfolioChapters')) ?>
                    <? endforeach; ?>
                </tbody>
            </table>
        <? else : ?>
            <h2><?= _('Teilnehmende')?></h2>

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
        <li><?= Icon::create('decline', Icon::ROLE_INACTIVE) ?> <?= _('Kapitel/Impuls noch nicht freigeschaltet')?></li>
        <li><?= Icon::create('accept') ?> <?= _('Kapitel/Impuls freigeschaltet')?></li>
        <li><?= Icon::create('accept+new'); ?>  <?= _('Kapitel/Impuls freigeschaltet und Änderungen seit Ihrem letzten Besuch')?></li>
        <li><?= Icon::create('file', Icon::ROLE_INACTIVE); ?> <?= _('keine Supervisionsanliegen freigeschaltet')?></li>
        <li><?= Icon::create('file'); ?> <?= _('Supervisionsanliegen freigeschaltet')?></li>
        <li><?= Icon::create('forum'); ?> <?= _('Feedback gegeben')?></li>

        <li>
            <?= Icon::create('span-full', Icon::ROLE_STATUS_GREEN); ?>
            <?= Icon::create('span-full', Icon::ROLE_STATUS_YELLOW); ?>
            <?= Icon::create('span-full', Icon::ROLE_STATUS_RED); ?>
            <?= _('Diese Status-Icons geben an, wie gut der Lernende bei Abgabeterminen in der Zeit liegt.')?>
            <?= _('Wenn für keine Vorlage eine Deadline gesetzt wurde, wird das Status-Icon immer grün anzeigen.')?>
        </li>
    </ul>
</div>
