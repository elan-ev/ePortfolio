<h1 id="headline_uebersicht">
    <?= Avatar::getAvatar($user->id, $userInfo['username'])->getImageTag(Avatar::MEDIUM,
        ['style' => 'margin-right: 5px;border-radius: 35px; height:36px; width:36px; border: 1px solid #28497c;', 'title' => htmlReady($userInfo['Vorname'] . " " . $userInfo['Nachname'])]); ?>
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
            <span class='actions'> <a data-dialog="size=auto;reload-on-close"
                                      href="<?= PluginEngine::getLink($this->plugin, [], 'show/createvorlage') ?>">
            <? $params = tooltip2(_("Neue Vorlage erstellen")); ?>
            <? $params['style'] = 'cursor: pointer'; ?>
            <?= Icon::create('add', 'clickable')->asImg(20, $params) ?>
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
            foreach ($courses as $portfolio):?>
                <tr>
                    <td>
                        <a href="<?= URLHelper::getUrl('plugins.php/courseware/courseware', [
                            'cid'         => $portfolio->id,
                            'return_to'   => 'overview'
                        ]); ?>"
                           title="<?= _('Portfolio-Vorlage bearbeiten') ?>">
                           <?= $portfolio->getFullName(); ?>
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
                                Icon::create('edit', 'clickable')
                            );

                            if (sizeof(EportfolioGroupTemplates::findBySeminar_id($portfolio->id))) {
                                $actionMenu->addLink(
                                     PluginEngine::getLink($this->plugin, [], 'show/list_seminars/' . $portfolio->id),
                                    _('Verteilt in Veranstaltungen'),
                                    Icon::create('info', 'clickable'),
                                    ['data-dialog' => '1']
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
<? if (empty($courses)) : ?>
    <?= MessageBox::info('Sie haben noch keine Portfolio Vorlagen.') ?>
<? endif ?>

<br>
<table class="default">
    <caption><?= _('Meine Portfolios') ?>
        <span class="actions">
          <a data-dialog="size=auto;reload-on-close"
             href="<?= PluginEngine::getLink($this->plugin, [], 'show/createportfolio') ?>">
                    <?= Icon::create('add', 'clickable')->asImg(20, tooltip2(_("Neues Portfolio erstellen")) + ['style' => 'cursor: pointer']) ?>
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
                        <?= Icon::create('edit', 'clickable') ?>
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
