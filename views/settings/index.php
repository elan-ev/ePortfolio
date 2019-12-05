<? if (empty($viewerList) && ($supervisorId == null)) : ?>
    <?= MessageBox::info(_('Es sind derzeit keine Zugriffsrechte in Ihrem Portfolio vergeben.')); ?>
<? else : ?>
    <table class="default">
        <tr class="sortable">
            <th><?= _('Name') ?></th>
            <th></th>
            <? foreach ($chapterList as $chapter): ?>
                <th>
                    <?= htmlReady($chapter['title']) ?>
                </th>
            <? endforeach; ?>
        </tr>
        <tbody>
            <?php if (Eportfoliomodel::findBySeminarId($cid)->group_id): ?>
                <tr style="background-color: lightblue;">
                    <td>
                        <?= Avatar::getNobody()->getImageTag(Avatar::SMALL,
                            ['style' => 'margin-right: 5px;border-radius: 30px; width: 25px; border: 1px solid #28497c;',
                             'title' => _('Berechtigte für Portfolioarbeit')]); ?>
                        <?= _('Berechtigte für Portfolioarbeit') ?>
                    </td>
                    <td></td>

                    <? foreach ($chapterList as $chapter): ?>
                        <?php $hasAccess = EportfolioFreigabe::hasAccess($supervisorId, $cid, $chapter['id']); ?>
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
                    <? endforeach; ?>
                </tr>
            <? endif; ?>

            <? foreach (EportfolioUser::findBySQL('seminar_id = ?', [$cid]) as $acc): ?>
                <? if ($acc->user_id == $GLOBALS['user']->id) continue; ?>
                <? $user = User::find($acc->user_id); ?>
                <tr style="background-color: lightblue;">
                    <td>
                        <?= Avatar::getAvatar($acc->user_id)->getImageTag(Avatar::SMALL,
                            ['style' => 'margin-right: 5px;border-radius: 30px; width: 25px; border: 1px solid #28497c;',
                             'title' => _('Gruppen-Supervisoren')]); ?>
                        <?= $user->getFullname() ?>
                    </td>
                    
                    <td onClick="deleteUserAccess('<?= $acc->user_id ?>', '<?= $cid ?>', this);" class="righttable-inner">
                        <span><?= Icon::create('trash', 'clickable') ?></span>
                    </td>

                    <? foreach ($chapterList as $chapter): ?>
                        <?php $hasAccess = EportfolioFreigabe::hasAccess($acc->user_id, $cid, $chapter['id']); ?>
                        <td onClick="setAccess('<?= $chapter['id'] ?>', '<?= $acc->user_id ?>', this, '<?= $cid ?>');"
                            class="righttable-inner">
                            <? if ($hasAccess): ?>
                                <span id="icon-<?= $acc->user_id . '-' . $chapter['id']; ?>"
                                      class="glyphicon glyphicon-ok"
                                      title='Klick, um Kapitel nicht mehr feizugeben'><?= Icon::create('accept', Icon::ROLE_CLICKABLE); ?></span>
                            <? else : ?>
                                <span id="icon-<?= $acc->user_id . '-' . $chapter['id']; ?>"
                                      class="glyphicon glyphicon-remove"
                                      title='Klick, um Kapitel freizugeben'><?= Icon::create('decline', Icon::ROLE_CLICKABLE); ?></span>
                            <? endif; ?>
                        </td>
                    <? endforeach; ?>
                </tr>
            <? endforeach; ?>
        </tbody>
    </table>

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
        function deleteUserAccess(userId, seminar_id, obj) {
            $.ajax({
                type: "POST",
                url: STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/settings/deleteUserAccess'),
                data: {
                    'userId': userId
                },
                success: function () {
                    $(obj).parents('tr').fadeOut();
                }
            });
        }

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
