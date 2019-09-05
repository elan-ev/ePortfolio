<?= MessageBox::info(_('Alle hier eingetragenen Lehrenden / TutorInnen können auf den Reiter "Portfolio-Arbeit" zugreifen und Einstellungen vornehmen. '
    . 'Studierende haben darauf immer Zugriff, um zu ihrem eigenen Portfolio zu gelangen.')) ?>
<table class="default">
    <colgroup>
        <col width="30%">
        <col width="60%">
    </colgroup>
    <tr>
        <th>Name</th>
        <th></th>
        <th>Aktionen</th>
    </tr>
    <?php foreach ($usersOfGroup as $user): ?>
        <tr>
            <td>
                <?php $userInfo = User::find($user[user_id]); ?>
                <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $userInfo['username']) ?>">
                    <?= Avatar::getAvatar($user['user_id'], $userInfo['username'])->getImageTag(Avatar::SMALL,
                        ['style' => 'margin-right: 5px;border-radius: 30px; width: 25px; border: 1px solid #28497c;', 'title' => htmlReady($userInfo['Vorname'] . " " . $userInfo['Nachname'])]); ?>
                    <?= htmlReady($userInfo['Vorname'] . " " . $userInfo['Nachname']) ?>
                </a>

            </td>
            <td></td>
            <td style="text-align:center;">
                <a onclick="return confirm('Nutzer Berechtigungen entziehen?')"
                   href='<?= $this->controller->url_for('supervisorgroup/deleteUser/' . $groupId . '/' . $user[user_id]) ?>'><?php echo Icon::create('trash', 'clickable'); ?></a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
