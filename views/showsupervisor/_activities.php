<? if (!sizeof($activities)) : ?>
    <?= MessageBox::info(_('Keine Aktivitäten')) ?>
    <br>
<? else : ?>
    <? foreach ($activities as $activity): ?>
        <div class="single-activity">
            <div class="row">
                <div class="col-sm-7 single-activity-info">
                    <div class="row">
                        <div class="">

                            <? if ($activity->type == "freigabe"): ?>
                                <?= Icon::create('accept'); ?>
                            <? endif; ?>

                            <? if ($activity->type == "aenderung"): ?>
                                <?= Icon::create('accept+new'); ?>
                            <? endif; ?>

                            <? if ($activity->type == "notiz"): ?>
                                <?= Icon::create('file'); ?>
                            <? endif; ?>

                            <?= Avatar::getAvatar($activity->user_id, User::find($activity->user_id)->username)->getImageTag(Avatar::MEDIUM, ['style' => 'margin-right: 0px; border-radius: 35px; position: relative; left: -9px; top: 3px; border: 3px solid #f5f6f6;', 'title' => htmlReady($userInfo['Vorname'] . " " . $userInfo['Nachname'])]); ?>

                        </div>
                        <div class="" style="line-height: 36px;">
                            <strong><?= htmlReady(User::find($activity->user_id)->getFullname()) ?></strong> <?= htmlReady($activity->message) ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4" style="line-height: 36px; text-align: center;">
                    <? echo date('d.m.Y - G:i', $activity->mk_date); ?>

                    <? if ($activity->block_id) : ?>
                        <div class="" style="float: right; position: relative; top: 3px; ">
                            <? if (EportfolioFreigabe::hasAccess($GLOBALS['user']->id, $activity->block_id)) : ?>
                                <a href="<?= $activity->link; ?>">
                                    <?= Icon::create('link-intern'); ?>
                                </a>
                            <? else : ?>
                                <?= Icon::create('decline', Icon::ROLE_STATUS_RED, [
                                    'title' => _('Sie haben keinen Zugriff (mehr) auf dieses Kapitel in diesem Portfolio.')
                                ]); ?>
                            <? endif ?>
                        </div>
                    <? endif ?>
                </div>
            </div>
        </div>
    <? endforeach; ?>
<? endif ?>