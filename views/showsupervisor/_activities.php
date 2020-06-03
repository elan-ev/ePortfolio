<? if (!sizeof($activities)) : ?>
    <h1>
        <?= _('Keine Aktivitäten') ?>
    </h1>
<? else : ?>
<? foreach ($activities as $activity): ?>
    <div class="single-activity">
        <div class="row">
            <div class="col-sm-7 single-activity-info">
                <div class="row">
                    <div class="">

                    <? if ($activity->type == "freigabe"):?>
                        <?= Icon::create('accept', 'clickable');  ?>
                    <? endif; ?>

                    <? if ($activity->type == "aenderung"):?>
                        <?= Icon::create('accept+new', 'clickable');  ?>
                    <? endif; ?>

                    <? if ($activity->type == "notiz"):?>
                        <?= Icon::create('file', 'clickable');  ?>
                    <? endif; ?>

                    <?= Avatar::getAvatar($activity->user_id, User::find($activity->user_id)->username)->getImageTag(Avatar::MEDIUM, array('style' => 'margin-right: 0px; border-radius: 35px; position: relative; left: -9px; top: 3px; border: 3px solid #f5f6f6;', 'title' => htmlReady($userInfo['Vorname']." ".$userInfo['Nachname']))); ?>

                    </div>
                    <div class="" style="line-height: 36px;">
                        <b><?= User::find($activity->user_id)->vorname . ' ' . User::find($activity->user_id)->nachname?>: </b> <? echo $activity->message; ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-4" style="line-height: 36px; text-align: center;">
                <? echo date('d.m.Y - G:i', $activity->mk_date); ?>
                <div class="" style="float: right; position: relative; top: 3px; ">
                    <? if (EportfolioFreigabe::hasAccess($GLOBALS['user']->id, $activity->eportfolio_id, $activity->block_id)) : ?>
                        <a href="<? echo $activity->link; ?>">
                            <?= Icon::create('link-intern'); ?>
                        </a>
                    <? else : ?>
                        <?= Icon::create('decline', Icon::ROLE_STATUS_RED, [
                            'title' => _('Sie haben keinen Zugriff (mehr) auf dieses Kapitel in diesem Portfolio.')
                        ]); ?>
                    <? endif ?>
                </div>
            </div>
        </div>
    </div>
<? endforeach; ?>
<? endif ?>
