<? if (empty($viewerList) && ($supervisorId == null)) : ?>
    <?= MessageBox::info(_('Es sind derzeit keine Zugriffsrechte in Ihrem Portfolio vergeben.')); ?>
<? else : ?>
    <? if (EportfolioModel::findBySeminarId($cid)->group_id): ?>
        <h1>
                <?= Avatar::getNobody()->getImageTag(Avatar::SMALL,
                    ['style' => 'margin-right: 5px;border-radius: 30px; width: 25px; border: 1px solid #28497c;',
                     'title' => _('Berechtigte für Portfolioarbeit')]); ?>
                <?= _('Berechtigte für Portfolioarbeit') ?>
                <?= tooltipHtmlIcon(
                    _('Folgende Personen befinden sich in dieser Gruppe:') .'<br/><ul><li>'.
                    nl2br(implode("</li><li>", $supervisor_list ?: [_('Bisher niemand')])) .'</li>'
                ) ?>
        </h1>

        <table class="default">
            <thead>
                <tr>
                    <th>Kapitel</th>
                    <th>Freigabe</th>
                </tr>
            </thead>
            <tbody>

            <? foreach ($chapterList as $chapter): ?>
                <tr>
                    <?php $hasAccess = EportfolioFreigabe::getAccess($supervisorId, $chapter['id']); ?>
                    <td>
                        <?= $chapter['title'] ?>
                    </td>

                    <td onClick="setAccess('<?= $chapter['id'] ?>', '<?= $supervisorId ?>', this, '<?= $cid ?>');"
                        class="righttable-inner">
                        <? if ($hasAccess): ?>
                            <span id="icon-<?= $supervisorId . '-' . $chapter['id']; ?>"
                                  class="glyphicon glyphicon-ok"
                                  title='Klick, um Kapitel nicht mehr feizugeben'><?= Icon::create('accept', Icon::ROLE_CLICKABLE); ?></span>
                        <? else : ?>
                            <span id="icon-<?= $supervisorId . '-' . $chapter['id']; ?>"
                                  class="glyphicon glyphicon-remove"
                                  title='Klick, um Kapitel freizugeben'><?= Icon::create('decline', Icon::ROLE_CLICKABLE); ?></span>
                        <? endif; ?>
                    </td>
                </tr>
            <? endforeach; ?>
            </tbody>
        </table>
        <br><br>
    <? endif; ?>

    <? foreach ($course->members as $acc): ?>
        <? if ($acc->user_id == $GLOBALS['user']->id
            || $acc->status != 'user') continue; ?>
        <? $user = User::find($acc->user_id); ?>
        <h1>
            <?= Avatar::getAvatar($acc->user_id)->getImageTag(Avatar::SMALL,
                ['style' => 'margin-right: 5px;border-radius: 30px; width: 25px; border: 1px solid #28497c;',
                 'title' => _('Gruppen-Supervisoren')]); ?>
            <?= $user->getFullname() ?>
            <? if (isset($supervisors[$acc->user_id])) : ?>
                <?= tooltipIcon('Nutzer/in ist in der Gruppe "Berechtigte für Portfolioarbeit" und hat immer mindestens die Zugriffsrechte dieser Gruppe.') ?>
            <? endif ?>

            <span style="display: inline-block; vertical-align: bottom">
                <a href="<?= $controller->url_for('settings/deleteUserAccess/' . $acc->user_id) ?>" onClick="return confirm('<?= _('Sind sie sicher?') ?>')">
                    <?= Icon::create('trash', 'clickable', [
                        'style'   => 'margin-bottom: 0px'
                    ]) ?>
                </a>
            </span>
        </h1>


        <table class="default">
            <thead>
                <tr>
                    <th>Kapitel</th>
                    <th>Freigabe</th>
                </tr>
            </thead>
            <tbody>

            <? foreach ($chapterList as $chapter): ?>
                <tr>
                    <?php $hasAccess = EportfolioFreigabe::getAccess($acc->user_id, $chapter['id']); ?>
                    <td>
                        <?= $chapter['title'] ?>
                    </td>

                    <td onClick="setAccess('<?= $chapter['id'] ?>', '<?= $acc->user_id ?>', this, '<?= $cid ?>');"
                        class="righttable-inner">
                        <? if ($hasAccess): ?>
                            <span id="icon-<?= $acc->user_id . '-' . $chapter['id']; ?>"
                                  class="glyphicon glyphicon-ok"
                                  title='Klick, um Kapitel nicht mehr feizugeben'><?= Icon::create('accept', Icon::ROLE_CLICKABLE); ?></span>
                        <? else : ?>
                            <? if ($hasAccess !=  EportfolioFreigabe::hasAccess($acc->user_id, $chapter['id'])) : ?>
                            <span id="icon-<?= $acc->user_id . '-' . $chapter['id']; ?>"
                                  class="glyphicon glyphicon-remove"
                                  title='Nutzer/in ist in der Gruppe "Berechtigte für Portfolioarbeit" hat dadurch trotzdem Zugriff! Klick, um Kapitel freizugeben'><?= Icon::create('decline', Icon::ROLE_ATTENTION); ?></span>
                            <? else : ?>
                            <span id="icon-<?= $acc->user_id . '-' . $chapter['id']; ?>"
                                  class="glyphicon glyphicon-remove"
                                  title='Klick, um Kapitel freizugeben'><?= Icon::create('decline', Icon::ROLE_CLICKABLE); ?></span>
                              <? endif ?>
                        <? endif; ?>
                    </td>
                </tr>
            <? endforeach; ?>
            </tbody>
        </table>
        <br><br>
    <? endforeach; ?>

    <!-- Legende -->
    <div class="legend">
        <ul>
            <li>
                <?= Icon::create('accept', 'clickable'); ?>  Person / Rechtegruppe hat Zugriff auf das Kapitel
            </li>
            <li>
                <?= Icon::create('decline', 'clickable'); ?> Person / Rechtegruppe hat keinen Zugriff auf das Kapitel
            </li>

        </ul>
    </div>

    <script type="text/javascript">
        function setAccess(id, viewerId, obj, cid) {
            var status = !$(obj).children('span').hasClass('glyphicon-ok');

            $.ajax({
                type: "POST",
                url: STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/settings/setAccess'),
                data: {
                    user_id: viewerId,
                    seminar_id: cid,
                    chapter_id: id,
                    status: status
                }
            }).done(function(data) {
                // check, if a MessageBox is already displayed
                if($("#layout_content > *:first").hasClass("messagebox")) {
                    $("#layout_content > *:first").remove();
                }
                // display MessageBox
                $("#layout_content").prepend(data);
                //only change access_icon if succeded
                if($("#layout_content > *:first").hasClass("messagebox_success")) {
                    change_access_icon(obj, id, cid, status);
                } else {
                    change_access_icon(obj, id, cid, !status);
                }
            });
            // show Ajax Indicator while waiting for response
            $(obj).empty().append('<?=Assets::img("ajax_indicator_small.gif")?>')
        }

        function change_access_icon(obj, id, cid, status) {
            if (status) {
                $(obj).empty().append('<span id="icon-' + id + '-' + cid + '" \
                                        class="glyphicon glyphicon-ok" \
                                        title="Klick, um Kapitel nicht mehr feizugeben"> \
                                        <?= Icon::create('accept', Icon::ROLE_CLICKABLE); ?></span>');
            } else {
                $(obj).empty().append('<span id="icon-' + id + '-' + cid + '" \
                                        class="glyphicon glyphicon-remove" \
                                        title="Klick, um Kapitel feizugeben"> \
                                        <?= Icon::create('decline', Icon::ROLE_CLICKABLE); ?></span>');
            }
        }
    </script>
<? endif ?>
