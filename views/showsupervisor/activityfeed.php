<div class="activity-feed-container">

    <h1>
        <? if ($countActivities >= 1) : ?>
            <?= $countActivities ?>
        <? else : ?>
            <?= 'Keine' ?>
        <? endif ?>

        <?= _('Neue Aktivitäten') ?>
    </h1>

    <?php foreach ($activities as $activity): ?>
        <?php if ($activity->is_new): ?>
            <div class="single-activity">
                <div class="row">
                    <div class="col-sm-7 single-activity-info">
                        <div class="row">
                            <div class="">
                                <?php $user = User::find($activity->user_id) ?>
                                <? if ($activity->type == "freigabe"): ?>
                                    <?= Icon::create('accept') ?>
                                <? endif ?>
                                <? if ($activity->type == "aenderung"): ?>
                                    <?= Icon::create('accept+new') ?>
                                <? endif ?>
                                <? if ($activity->type == "notiz"): ?>
                                    <?= Icon::create('file') ?>
                                <? endif ?>

                                <?= Avatar::getAvatar($activity->user_id, $user->username)->getImageTag(Avatar::MEDIUM, ['style' => 'margin-right: 0px; border-radius: 35px; position: relative; left: -9px; top: 3px; border: 3px solid #f5f6f6;', 'title' => htmlReady($userInfo['Vorname'] . " " . $userInfo['Nachname'])]); ?>

                            </div>
                            <div class="" style="line-height: 36px;">
                                <b><?= htmlReady($user->getFullName()) ?>: </b> <?= htmlReady($activity->message) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4" style="line-height: 36px; text-align: center;">
                        <?php echo date('d.m.Y - G:i', $activity->mk_date); ?>
                        <div class="" style="float: right; position: relative; top: 3px; ">
                            <a href="<?php echo $activity->link; ?>">
                                <?= Icon::create('link-intern'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    <?php endforeach; ?>
    <h1>
        <?= _('Alte Aktivitäten') ?>
    </h1>

    <?php foreach ($activities as $activity): ?>
        <?php if (!$activity->is_new): ?>
            <div class="single-activity">
                <div class="row">
                    <div class="col-sm-7 single-activity-info">
                        <div class="row">
                            <div class="">

                                <?php if ($activity->type == "freigabe"): ?>
                                    <?= Icon::create('accept', 'clickable'); ?>
                                <?php endif; ?>

                                <?php if ($activity->type == "aenderung"): ?>
                                    <?= Icon::create('accept+new', 'clickable'); ?>
                                <?php endif; ?>

                                <?php if ($activity->type == "notiz"): ?>
                                    <?= Icon::create('file', 'clickable'); ?>
                                <?php endif; ?>

                                <?= Avatar::getAvatar($activity->user_id, User::find($activity->user_id)->username)->getImageTag(Avatar::MEDIUM, ['style' => 'margin-right: 0px; border-radius: 35px; position: relative; left: -9px; top: 3px; border: 3px solid #f5f6f6;', 'title' => htmlReady($userInfo['Vorname'] . " " . $userInfo['Nachname'])]); ?>

                            </div>
                            <div class="" style="line-height: 36px;">
                                <b><?= User::find($activity->user_id)->vorname . ' ' . User::find($activity->user_id)->nachname ?>
                                    : </b> <?php echo $activity->message; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4" style="line-height: 36px; text-align: center;">
                        <?php echo date('d.m.Y - G:i', $activity->mk_date); ?>
                        <div class="" style="float: right; position: relative; top: 3px; ">
                            <a href="<?php echo $activity->link; ?>">
                                <?= Icon::create('link-intern'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    <?php endforeach; ?>

</div>
