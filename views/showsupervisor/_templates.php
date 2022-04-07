<table id="table_templates" class="default collapsable tablesorter">
    <caption>
        <?= $title ?>

        <? if (!$hide_add) : ?>
            <span class="actions">
            <a data-dialog="size=auto;reload-on-close"
               href="<?= URLHelper::getLink('plugins.php/eportfolioplugin/show/createvorlage') ?>">
                <?= Icon::create('add')
                    ->asImg(20, tooltip2(_('Neue Vorlage erstellen'))
                        + ['style' => 'cusros: pointer'])
                ?>
            </a>
        </span>
        <? endif ?>
    </caption>
    <colgroup>
        <col style="width: 30%">
        <col style="width: 30%">
        <col style="width: 10%">
        <col style="width: 15%">
        <col style="width: 15%">
        <col style="width: 10%">
    </colgroup>
    <thead>
        <tr class="sortable">
            <th><?= _('Titel der Vorlage') ?></th>
            <th><?= _('Beschreibung') ?></th>
            <? if ($hasTemplate): ?>
                <th><?= _('Anlagedatum') ?></th>
                <th data-sorter="false"><?= _('Details') ?></th>
                <th class="sorter-text"><?= _('Abgabedatum') ?></th>
            <? else: ?>
                <th></th>
                <th></th>
                <th></th>
            <? endif ?>
            <th data-sorter="false"><?= _('Aktionen') ?></th>
        </tr>
    </thead>

    <? if (empty($portfolios)) : ?>
</table>
<?= MessageBox::info($missing_text); ?>
<? else: ?>
    <?
    if ($hasTemplate) {
        $portfolios = EportfolioGroupTemplates::getGroupTemplateInformation($groupId, $portfolios);
    }
    ?>
    <tbody>
        <? foreach ($portfolios as $portfolio): ?>
            <? if ($hasTemplate) {
                $owner = $portfolio['portfolio']->getParticipantStatus($GLOBALS['user']->id) == 'dozent';
            } else {
                $owner = true;
            }
            ?>
            <tr>
                <td>
                    <? if (!$owner) : ?>
                        <?= htmlReady($hasTemplate ? $portfolio['portfolio']->name : $portfolio->name) ?>
                    <? else : ?>
                        <a href="<?= URLHelper::getLink('plugins.php/courseware/courseware', [
                            'cid'       => $hasTemplate ? $portfolio['portfolio']->id : $portfolio->id,
                            'return_to' => Context::getId()
                        ]); ?>">
                            <?= htmlReady($hasTemplate ? $portfolio['portfolio']->name : $portfolio->name) ?>
                        </a>
                    <? endif ?>
                </td>
                <td>
                    <?= htmlReady($hasTemplate ? $portfolio['portfolio']->beschreibung : $portfolio->beschreibung) ?>
                </td>
                <? if ($hasTemplate): ?>
                    <td>
                        <?= htmlReady(date('d.m.Y', $portfolio['portfolio']->mkdate)) ?>
                    </td>
                    <td>
                        <div title="<?= _('Verteilt von') ?>">
                            <?= Icon::create('own-license') ?>
                            <?= $portfolio['creatorName'] ?>
                        </div>
                        <div title="Verteilt am">
                            <?= Icon::create('share') ?>
                            <?= date('d.m.Y', $portfolio['distributionDate']); ?>
                        </div>
                    </td>
                    <td>
                        <span style="display:none"><?= $portfolio['deadline'] ?: 1 ?></span>
                        <? if (!$owner) : ?>
                            <? if ($portfolio['deadline']) : ?>
                                <?= sprintf(_('Abgabetermin: %s'), date('d.m.Y', $portfolio['deadline'])) ?>
                            <? endif ?>
                        <? elseif ($portfolio['deadline']): ?>
                            <div>
                                <a data-dialog="size=auto;"
                                   href="<?= URLHelper::getLink('plugins.php/eportfolioplugin/showsupervisor/templatedates/' . $groupId . '/' . $portfolio['portfolio']->id) ?>">
                                    <?= Icon::create('date', Icon::ROLE_CLICKABLE) ?>
                                    <?= sprintf(_('Abgabetermin: %s'), date('d.m.Y', $portfolio['deadline'])) ?>
                                </a>
                            </div>
                        <? else: ?>
                            <div title="<?= _('Abgabetermin bearbeiten') ?>">
                                <a data-dialog="size=auto;"
                                   href="<?= URLHelper::getLink('plugins.php/eportfolioplugin/showsupervisor/templatedates/' . $groupId . '/' . $portfolio['portfolio']->id) ?>">
                                    <?= Icon::create('date', Icon::ROLE_CLICKABLE) ?>
                                    <?= _('Kein Abgabetermin') ?>
                                </a>
                            </div>
                        <? endif ?>
                    </td>
                <? else : ?>
                    <td></td>
                    <td></td>
                    <td></td>
                <? endif ?>
                <td style="text-align: center;">
                    <?
                    $actionMenu = ActionMenu::get();

                    if ($owner) {
                        $actionMenu->addLink(
                            PluginEngine::getLink($this->plugin, [], 'showsupervisor/updatevorlage/' . ($hasTemplate ? $portfolio['portfolio']->id : $portfolio->id)),
                            _('Portfolio-Titel und Beschreibung bearbeiten'),
                            Icon::create('edit', 'clickable'),
                            ['data-dialog' => 'size=auto;reload-on-close']
                        );

                        if (!EportfolioGroupTemplates::isDistributed($hasTemplate ? $portfolio['portfolio']->id : $portfolio->id)) {
                            $actionMenu->addLink(
                                PluginEngine::getLink($this->plugin, [], 'showsupervisor/deleteportfolio/' . ($hasTemplate ? $portfolio['portfolio']->id : $portfolio->id)),
                                _('Portfolio-Vorlage löschen'),
                                Icon::create('remove', 'clickable'),
                                ['data-confirm' => 'Möchten Sie diese Vorlage wirklich unwiderruflich löschen?']
                            );
                        }
                    }

                    if ($member && !$hasTemplate) {
                        $actionMenu->addLink(
                            URLHelper::getUrl('plugins.php/courseware/courseware', [
                                'cid'       => $hasTemplate ? $portfolio['portfolio']->id : $portfolio->id,
                                'return_to' => Context::getId()
                            ]),
                            _('Portfolio-Vorlage bearbeiten'),
                            Icon::create('edit', 'clickable')
                        );
                        $actionMenu->addLink(
                            PluginEngine::getLink($this->plugin, [], 'showsupervisor/createportfolio/' . ($hasTemplate ? $portfolio['portfolio']->id : $portfolio->id)),
                            _('Portfolio-Vorlage an Teilnehmende verteilen.'),
                            Icon::create('share', 'clickable'),
                            ['data-confirm' => _('Sobald diese Vorlage verteilt ist, können Sie zwar weitere Änderungen an der Vorlage vornehmen,
                                                    diese werden jedoch nicht in die Ansicht der Studierenden übernommen!'),
                            ]
                        );
                    }

                    if ($member && $hasTemplate) {
                        $actionMenu->addLink(
                            PluginEngine::getLink($this->plugin, [], 'showsupervisor/createportfolio/' . ($hasTemplate ? $portfolio['portfolio']->id : $portfolio->id)),
                            _('Vorlage erneut verteilen'),
                            Icon::create('share', 'clickable'),
                            ['data-confirm' => _('Sie sollten eine Vorlage nur im Notfall erneut verteilen! Alle Inhalte werden ERNEUT hinzugefügt und schlimmstenfalls gedoppelt! Sind sie sicher?'),
                            ]
                        );
                    }
                    ?>
                    <?= $actionMenu->render() ?>
                </td>
            </tr>
        <? endforeach; ?>
    </tbody>
    </table>
<? endif ?>

<br><br>
