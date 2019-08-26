<table id="table_templates" class="default collapsable tablesorter">
    <caption>
        <?= $title ?>
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
            <th class="sorter-text"><?= _('Abgabedatum') ?></th>
            <th data-sorter="false"><?= _('Aktionen') ?></th>
            <!--<th data-sorter="false"><?= _('Anzeigen') ?></th>-->
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
                    <a href="<?= URLHelper::getLink('plugins.php/courseware/courseware', ['cid' => $portfolio->id, 'return_to' => Context::getId()]); ?>">
                        <?= Icon::create('edit', Icon::ROLE_CLICKABLE, ['title' => sprintf(_('Portfolio-Vorlage bearbeiten.'))]) ?>
                    </a>

                    <? if ($member && !$groupHasTemplate): ?>
                        <a data-confirm="<?= _('Vorlage an Teilnehmende verteilen') ?>"
                           href="<?= $controller->url_for('showsupervisor/createportfolio/' . $portfolio->id) ?>">
                            <?= Icon::create('share', Icon::ROLE_CLICKABLE, tooltip2(_('Portfolio-Vorlage an Gruppenmitglieder verteilen.')) + ['cursor' => 'pointer']) ?>
                        </a>
                    <? endif ?>
                </td>
                <!--
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
                </td>-->
            </tr>
        <? endforeach; ?>
    </tbody>
</table>


<? if (empty($portfolios)) : ?>
    <?= MessageBox::info($missing_text); ?>
<? endif ?>

<br><br>
