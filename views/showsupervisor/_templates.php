<table id="table_templates" class="default collapsable tablesorter">
    <caption>
        <?= $title ?>

        <? if (!$hide_add) : ?>
        <span class="actions">
            <a data-dialog="size=auto;reload-on-close" href="<?= $controller->url_for('show/createvorlage') ?>">
            <?= Icon::create('add', 'clickable')->asImg(20, tooltip2(_('Neue Vorlage erstellen')) + ['style' => 'cusros: pointer']) ?>
                </a>
        </span>
        <? endif ?>
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
            <th class="sorter-text"><?= _('Abgabedatum') ?></th>
            <th data-sorter="false"><?= _('Aktionen') ?></th>
            <!--<th data-sorter="false"><?= _('Anzeigen') ?></th>-->
        </tr>
    </thead>

    <tbody>
        <? foreach ($portfolios as $portfolio): ?>
            <tr>
                <td>
                    <a href="<?= URLHelper::getLink('plugins.php/courseware/courseware', [
                        'cid'       => $portfolio->id,
                        'return_to' => Context::getId()
                    ]); ?>">
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
                    <?php
                        $actionMenu = ActionMenu::get();
                        $actionMenu->addLink(
                            PluginEngine::getLink($this->plugin, [], 'showsupervisor/updatevorlage/' . $portfolio->id),
                            _('Portfolio-Titel und Beschreibung bearbeiten'),
                            Icon::create('edit', 'clickable'),
                            ['data-dialog' => 'size=auto;reload-on-close']
                        );
                        $actionMenu->addLink(
                            URLHelper::getUrl('plugins.php/courseware/courseware', [
                               'cid'         => $portfolio->id,
                               'return_to'   => 'overview'
                           ]),
                            _('Portfolio-Vorlage bearbeiten'),
                            Icon::create('edit', 'clickable')
                        );
                        if ($member && !$groupHasTemplate) {
                            $actionMenu->addLink(
                                PluginEngine::getLink($this->plugin, [], 'showsupervisor/createportfolio/' . $portfolio->id),
                                _('Portfolio-Vorlage an Gruppenmitglieder verteilen.'),
                                Icon::create('share', 'clickable'),
                                ['data-confirm' => _('Vorlage an Teilnehmende verteilen')]
                            );
                        }
                        /* favs not yet supported
                        if ($member && $groupHasTemplate){
                            if (EportfolioGroup::checkIfMarkedAsFav($id, $portfolio->id) == 0){
                                $actionMenu->addLink(
                                    PluginEngine::getLink($this->plugin, [], 'showsupervisor/addAsFav/' . $id . '/' . $portfolio->id),
                                    _('Portfolio-Vorlage als Favorit markieren),
                                    Icon::create('visibility-invisible', 'clickable')

                                );
                            } else {
                                $actionMenu->addLink(
                                    PluginEngine::getLink($this->plugin, [], 'showsupervisor/deleteAsFav/' . $id . '/' . $portfolio->id),
                                    _('Portfolio-Vorlage als Favorit markieren),
                                    Icon::create('visibility-invisible', 'attention')

                                );
                            }
                        }
                         */
                    ?>
                    <?= $actionMenu->render() ?>
                </td>
            </tr>
        <? endforeach; ?>
    </tbody>
</table>


<? if (empty($portfolios)) : ?>
    <?= MessageBox::info($missing_text); ?>
<? endif ?>

<br><br>
