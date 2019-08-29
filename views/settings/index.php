<? if (empty($viewerList) && ($supervisorId == null)) : ?>
    <?= MessageBox::info('Es sind derzeit keine Zugriffsrechte in Ihrem Portfolio vergeben.'); ?>
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
                             'title' => _('Gruppen-Supervisoren')]); ?>
                        <?= _('Gruppen-Supervisoren') ?>
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
                <? if ($acc->user_id = $GLOBALS['user']->id) continue; ?>
                <? $user = User::find($acc->user_id); ?>
                <tr style="background-color: lightblue;">
                    <td>
                        <?= Avatar::getAvatar($acc->user_id)->getImageTag(Avatar::SMALL,
                            ['style' => 'margin-right: 5px;border-radius: 30px; width: 25px; border: 1px solid #28497c;',
                             'title' => _('Gruppen-Supervisoren')]); ?>
                        <?= $user->getFullname() ?>
                    </td>
                    <td>
                        <a href="<?= $controller->url_for('settings/deleteUserAccess/' . $acc->user_id) ?>">
                            <?= Icon::create('trash', 'clickable') ?>
                        </a>
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

    <script type="text/javascript"
            src="<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'plugins_packages/uos/EportfolioPlugin/assets/js/eportfolio.js'; ?>"></script>
    <script type="text/javascript">

        var cid = '<?php echo $cid; ?>';

        $(document).ready(function () {


            $('#deleteModal').on('shown.bs.modal', function () {
                $('#deleteModal').focus()
            })

            // Portfolio Informationen ï¿½ndern
            $('#portfolio-info-trigger').click(function () {
                $(this).toggleClass('show-info-not');
                $('#portfolio-info-saver').toggleClass('show-info');
                $('.portfolio-info-wrapper').toggleClass('show-info');
                $('.portfolio-info-wrapper-current').toggleClass('show-info-not');
            })

            $('#portfolio-info-saver').click(function () {
                $(this).toggleClass('show-info');
                $('#portfolio-info-trigger').toggleClass('show-info-not');
                $('.portfolio-info-wrapper').toggleClass('show-info');
                $('.portfolio-info-wrapper-current').toggleClass('show-info-not');

                var valName = $("#name-input").val();
                var valBeschreibung = $("#beschreibung-input").val();

                $.ajax({
                    type: "POST",
                    url: "/studip/plugins.php/eportfolioplugin/settings?cid=" + cid,
                    data: {'saveChanges': 1, 'Name': valName, 'Beschreibung': valBeschreibung},
                    success: function (data) {
                        $('.wrapper-name').empty().append('<span>' + valName + '</span>');
                        $('.wrapper-beschreibung').empty().append('<span>' + valBeschreibung + '</span>');
                    }
                });

            })

            //Search Supervisor
            $('#inputSearchSupervisor').keyup(function () {
                var val = $("#inputSearchSupervisor").val();
                var url = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/livesearch');

                $.ajax({
                    type: "POST",
                    url: url,
                    dataType: "json",
                    data: {
                        'val': val,
                        'status': 'dozent',
                        'searchSupervisor': 1,
                    },
                    success: function (json) {
                        $('#searchResult').empty();
                        _.map(json, output);
                        console.log(json);

                        function output(n) {
                            $('#searchResult').append('<div onClick="setSupervisor(&apos;' + n.userid + '&apos;)" class="searchResultItem">' + n.Vorname + ' ' + n.Nachname + '<span class="pull-right glyphicon glyphicon-plus" aria-hidden="true"></span></div>');
                        }
                    }
                });
            });

            //Search Viewer
            $('#inputSearchViewer').keyup(function () {
                var val = $("#inputSearchViewer").val();
                var url = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/livesearch');

                var values = _.words(val);

                $.ajax({
                    type: "POST",
                    url: url,
                    dataType: "json",
                    data: {
                        'val': values,
                        'searchViewer': 1,
                        'cid': cid,
                    },
                    success: function (json) {
                        $('#searchResultViewer').empty();
                        _.map(json, output);
                        console.log(json);

                        function output(n) {
                            console.log(n.userid);
                            $('#searchResultViewer').append('<div onClick="setViewer(&apos;' + n.userid + '&apos;)" class="searchResultItem">' + n.Vorname + ' ' + n.Nachname + '<span class="pull-right glyphicon glyphicon-plus" aria-hidden="true"></span></div>');
                        }
                    },
                    error: function (json) {
                        console.log(json.responsetext);
                        $('#searchResultViewer').empty();
                        _.map(json, output);

                        function output(n) {
                            $('#searchResultViewer').append('<div onClick="setViewer(&apos;' + n.userid + '&apos;)" class="searchResultItem">' + n.Vorname + ' ' + n.Nachname + '<span class="pull-right glyphicon glyphicon-plus" aria-hidden="true"></span></div>');
                        }
                    }
                });
            });

        });

        function deleteUserAccess(userId, seminar_id, obj) {
            $(obj).empty().append('<i style="color: #24437c;" class="fa fa-circle-o-notch fa-spin fa-fw"></i>');
            var url = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/settings');
            console.log(userId);
            $.ajax({
                type: "POST",
                url: url,
                data: {
                    'action': 'deleteUserAccess',
                    'userId': userId,
                    'seminar_id': seminar_id,
                },
                success: function (data) {
                    console.log(data);
                    $(obj).parents('tr').fadeOut();
                }
            });
        }

        function setAccess(id, viewerId, obj, cid) {
            var status = $(obj).children('span').hasClass('glyphicon-ok');
            var url = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/settings/setAccess/' + viewerId + '/' + cid + '/' + id + '/' + !status);
            $.ajax({
                type: "POST",
                url: url,
                success: function (data) {
                    if (status === false) {
                        $(obj).empty().append('<span class="glyphicon glyphicon-ok"><?= Icon::create('accept', Icon::ROLE_CLICKABLE); ?></span>');
                    } else {
                        $(obj).empty().append('<span class="glyphicon glyphicon-remove"><?=Icon::create('decline', Icon::ROLE_CLICKABLE); ?></span>');
                    }

                }
            });
        }

    </script>
<? endif ?>
