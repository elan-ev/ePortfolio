<h1 id="headline_uebersicht">
    <?= Avatar::getAvatar($GLOBALS['user']->id, $GLOBALS['user']->username)->getImageTag(Avatar::MEDIUM,
        [
                'style' => 'margin-right: 5px;border-radius: 35px; height:36px; width:36px; border: 1px solid #28497c;',
                'title' => htmlReady($GLOBALS['user']->getFullName())
        ]); ?>
    <?= ngettext('Mein Portfolio', 'Meine Portfolios', $countPortfolios); ?>
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
            <?php $courses = Eportfoliomodel::getPortfolioVorlagen();
            $courses = array_filter($courses, function($course) use ($id) {
                return empty(EportfolioArchive::find($course->id));
            });
            foreach ($courses as $portfolio): ?>
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

    <? if (empty($courses)) : ?>
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
        <col style="width: 30%">
        <col style="width: 30%">
        <col>
        <col>
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

        <?php $myportfolios = Eportfoliomodel::getMyPortfolios(); ?>
        <?php foreach ($myportfolios as $portfolio): ?>
            <tr>
                <td>
                    <a href="<?= URLHelper::getUrl('plugins.php/courseware/courseware', [
                        'cid'         => $portfolio->id,
                        'return_to'   => 'overview'
                    ]); ?>">
                        <?= $portfolio->name; ?>
                    </a>
                </td>
                <td><?= htmlReady($portfolio->beschreibung); ?></td>
                <td style="text-align: center;">
                    <?= ShowController::countViewer($portfolio->id); ?>
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
<? if (empty($myportfolios)) : ?>
    <?= MessageBox::info('Bisher sind keine eigenen Portfolios vorhanden.') ?>
<? endif ?>

<br>
<table class="default">
    <caption><?= _('Für mich freigegebene Portfolios') ?></caption>
    <colgroup>
        <col width="30%">
        <col width="60%">
        <col width="10%">
    </colgroup>
    <thead>
        <tr class="sortable">
            <th><?= _('Portfolio-Name') ?></th>
            <th><?= _('Beschreibung') ?></th>
            <th><?= _('Besitzer') ?></th>
        </tr>
    </thead>
    <tbody>
        <? $myAccess = ShowController::getAccessPortfolio(); ?>
        <? foreach ($myAccess as $portfolio): ?>
            <tr class="insert_tr">
                <td>
                    <a href="<?= URLHelper::getUrl('plugins.php/courseware/courseware', [
                        'cid'         => $portfolio->id,
                        'return_to'   => 'overview'
                    ]); ?>">
                        <?= $portfolio->name; ?>
                    </a>
                </td>
                <td></td>
                <td>
                    <?= ShowController::getOwnerName($portfolio->id); ?>
                </td>
            </tr>
        <? endforeach; ?>
    </tbody>
</table>

<? if (empty($myAccess)) : ?>
    <?= MessageBox::info('Bisher wurden keine Portfolios für Sie freigegeben.') ?>
<? endif ?>

<? $courses = Eportfoliomodel::getPortfolioVorlagen();
$courses = array_filter($courses, function($course) use ($id) {
    return !empty(EportfolioArchive::find($course->id));
}); ?>
<? if ($isDozent && !empty($courses)): ?>
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
            <? foreach ($courses as $portfolio): ?>
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

    <? if (empty($courses)) : ?>
        <?= MessageBox::info('Sie haben noch keine Portfolio Vorlagen.') ?>
    <? endif ?>
<? endif; ?>
