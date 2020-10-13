<h1 id="headline_uebersicht">
    <?= Avatar::getAvatar($GLOBALS['user']->id, $GLOBALS['user']->username)->getImageTag(Avatar::MEDIUM,
        [
                'style' => 'margin-right: 5px;border-radius: 35px; height:36px; width:36px; border: 1px solid #28497c;',
                'title' => htmlReady($GLOBALS['user']->getFullName())
        ]); ?>
    <?= ngettext('Mein Portfolio', 'Meine Portfolios', count($my_portfolios)); ?>
    <span>
        <?= _('Hier finden Sie alle ePortfolios, die Sie angelegt haben oder die andere f&uuml;r Sie freigegeben haben.') ?>
    </span>
</h1>


<? if ($isDozent): ?>
    <table class="default">
        <colgroup>
            <col style="width: 30%">
            <col style="width:60%">
            <col style="width: 120px">
        </colgroup>
        <caption>
            <?= _('Portfolio Vorlagen') ?>
            <span class='actions'>
                <a data-dialog="size=auto;reload-on-close" href="<?= $controller->url_for('show/createvorlage') ?>">
            <? $params = tooltip2(_("Neue Vorlage erstellen")); ?>
            <? $params['style'] = 'cursor: pointer'; ?>
            <?= Icon::create('add', Icon::ROLE_CLICKABLE, $params)?>
       </span>
        </caption>
        <thead>
            <tr class="sortable">
                <th><?= _('Name') ?></th>
                <th><?= _('Beschreibung') ?></th>
                <th class="actions"><?= _('Aktionen') ?></th>
            </tr>
        </thead>
        <tbody>
            <? foreach ($vorlagen as $portfolio): ?>
                <tr>
                    <td>
                        <a href="<?= URLHelper::getUrl('plugins.php/courseware/courseware', [
                            'cid'         => $portfolio->id,
                            'return_to'   => 'overview'
                        ]); ?>"
                           title="<?= _('Portfolio-Vorlage bearbeiten') ?>">
                            <?= htmlReady($portfolio->getFullName()); ?>
                        </a>
                    </td>
                    <td><?= htmlReady($portfolio->beschreibung)?></td>
                    <td class="actions">
                        <?php
                        $actionMenu = ActionMenu::get();
                        $actionMenu->addLink(
                            $controller->url_for('show/updatevorlage/' . $portfolio->id),
                            _('Portfolio-Titel und Beschreibung bearbeiten'),
                            Icon::create('edit'),
                            ['data-dialog' => 'size=auto;reload-on-close']
                        );
                        $actionMenu->addLink(
                            URLHelper::getUrl('plugins.php/courseware/courseware', [
                                'cid'         => $portfolio->id,
                                'return_to'   => 'overview'
                            ]),
                            _('Portfolio-Vorlage bearbeiten'),
                            Icon::create('edit')
                        );

                        $actionMenu->addLink(
                            $controller->url_for('show/archive/' . $portfolio->id),
                            _('Portfolio-Vorlage archivieren'),
                            Icon::create('archive')
                        );

                        if (!empty(EportfolioGroupTemplates::findBySeminar_id($portfolio->id))) {
                            $actionMenu->addLink(
                                $controller->url_for('show/list_seminars/' . $portfolio->id),
                                _('Verteilt in Veranstaltungen'),
                                Icon::create('info'),
                                ['data-dialog' => 'size=auto']
                            );
                        }
                        ?>
                        <?= $actionMenu->render() ?>
                    </td>
                </tr>
            <? endforeach; ?>
        </tbody>
    </table>

    <? if (empty($vorlagen)) : ?>
        <?= MessageBox::info('Sie haben noch keine Portfolio Vorlagen oder alle Vorlagen sind archiviert.') ?>
    <? endif ?>
<? endif; ?>


<br>
<table class="default">
    <caption><?= _('Meine Portfolios') ?>
        <span class="actions">
          <a data-dialog="size=auto;reload-on-close"
             href="<?= $controller->url_for('show/createportfolio') ?>">
                    <?= Icon::create('add', Icon::ROLE_CLICKABLE, tooltip2(_("Neues Portfolio erstellen")) + ['style' => 'cursor: pointer']) ?>
        </a>
       </span>
    </caption>
    <colgroup>
        <col width="45%">
        <col width="35%">
        <col width="10%">
        <col width="10%">
    </colgroup>
    <thead>
        <tr class="sortable">
            <th><?= _('Portfolio-Name') ?></th>
            <th><?= _('Beschreibung') ?></th>
            <th style="text-align: center;"><?= _('Freigaben') ?></th>
            <th class="actions"><?= _('Aktionen') ?></th>
        </tr>
    </thead>
    <tbody>

        <? foreach ($my_portfolios as $portfolio): ?>
            <tr>
                <td>
                    <a href="<?= URLHelper::getUrl('plugins.php/courseware/courseware', [
                        'cid'         => $portfolio->id,
                        'return_to'   => 'overview'
                    ]); ?>">
                        <?= htmlReady($portfolio->name); ?>
                    </a>
                </td>
                <td>
                    <?= htmlReady($portfolio->beschreibung); ?>
                </td>
                <td style="text-align: center;">
                    <?= sizeof(Course::find($portfolio->id)->members) - 1; ?>
                </td>
                <td class="actions">
                    <a href="<?= URLHelper::getUrl('plugins.php/courseware/courseware', [
                        'cid'         => $portfolio->id,
                        'return_to'   => 'overview'
                    ]); ?>"
                       title="<?= _('Portfolio bearbeiten') ?>"
                    >
                        <?= Icon::create('edit') ?>
                    </a>
                </td>
            </tr>
        <? endforeach; ?>
    </tbody>
</table>
<? if (empty($my_portfolios)) : ?>
    <?= MessageBox::info('Bisher sind keine eigenen Portfolios vorhanden.') ?>
<? endif ?>

<br>
<table class="default">
    <caption><?= _('Für mich freigegebene Portfolios') ?></caption>
    <colgroup>
        <col width="80%">
        <col width="20%">
    </colgroup>
    <thead>
        <tr class="sortable">
            <th><?= _('Portfolio-Name') ?></th>
            <th><?= _('Besitzer/in') ?></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($accessible_portfolios as $portfolio): ?>
            <tr class="insert_tr">
                <td>
                    <a href="<?= URLHelper::getUrl('plugins.php/courseware/courseware', [
                        'cid'         => $portfolio->seminar_id,
                        'return_to'   => 'overview'
                    ]); ?>">
                        <?= $portfolio->seminar->name; ?>
                    </a>
                </td>
                <td>
                    <?= htmlReady(get_fullname($portfolio->owner_id)); ?>
                </td>
            </tr>
        <? endforeach; ?>
    </tbody>
</table>

<? if (empty($accessible_portfolios)) : ?>
    <?= MessageBox::info('Bisher wurden keine Portfolios für Sie freigegeben.') ?>
<? endif ?>


<? if ($isDozent && !empty($archived)): ?>
    <br>
    <table class="default">
        <colgroup>
            <col style="width: 30%">
            <col style="width:60%">
            <col style="width: 120px">
        </colgroup>
        <caption>
            <?= _('Archivierte Portfolio Vorlagen') ?>
        </caption>
        <thead>
            <tr class="sortable">
                <th><?= _('Name') ?></th>
                <th><?= _('Beschreibung') ?></th>
                <th class="actions"><?= _('Aktionen') ?></th>
            </tr>
        </thead>
        <tbody>
            <? foreach ($archived as $portfolio): ?>
                <tr>
                    <td>
                        <a href="<?= URLHelper::getUrl('plugins.php/courseware/courseware', [
                            'cid'         => $portfolio->id,
                            'return_to'   => 'overview'
                        ]); ?>"
                           title="<?= _('Portfolio-Vorlage bearbeiten') ?>">
                            <?= htmlReady($portfolio->getFullName()); ?>
                        </a>
                    </td>
                    <td><?= htmlReady($portfolio->beschreibung)?></td>
                    <td class="actions">
                        <?php
                        $actionMenu = ActionMenu::get();
                        $actionMenu->addLink(
                            URLHelper::getUrl('plugins.php/courseware/courseware', [
                                'cid'         => $portfolio->id,
                                'return_to'   => 'overview'
                            ]),
                            _('Portfolio-Vorlage bearbeiten'),
                            Icon::create('edit')
                        );

                        $actionMenu->addLink(
                            $controller->url_for('show/unarchive/' . $portfolio->id),
                            _('Portfolio-Vorlage wiederherstellen'),
                            Icon::create('archive')
                        );

                        if (!empty(EportfolioGroupTemplates::findBySeminar_id($portfolio->id))) {
                            $actionMenu->addLink(
                                $controller->url_for('show/list_seminars/' . $portfolio->id),
                                _('Verteilt in Veranstaltungen'),
                                Icon::create('info'),
                                ['data-dialog' => 'size=auto']
                            );
                        }
                        ?>
                        <?= $actionMenu->render() ?>
                    </td>
                </tr>
            <? endforeach; ?>
        </tbody>
    </table>
<? endif; ?>
