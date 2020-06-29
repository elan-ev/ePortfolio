<? $userPortfolioId = EportfolioModel::getPortfolioIdOfUserInGroup($user->id, $groupId); ?>
<tr>
    <td>
        <? if ($userPortfolioId): ?>
            <a class="member-link" data-dialog="size=1000px;"
                href="<?= URLHelper::getLink('plugins.php/eportfolioplugin/showsupervisor/memberdetail/' . $groupId . '/' . $user->id) ?>">
        <? endif; ?>

        <?= Avatar::getAvatar($user->id, $user->getFullname())->getImageTag(
            Avatar::SMALL, [
                'title' => htmlReady($user->getFullname()),
                'style' => 'margin-right: 5px'
            ]); ?>

        <?= htmlReady($user->getFullname()) ?>

        <? if ($userPortfolioId): ?>
            </a>
        <? endif ?>
    </td>
    <td>
        <? $icon = "";
        switch (EportfolioUser::getStatusOfUserInGroup($groupId, $userPortfolioId, $GLOBALS['user']->id)) {
            case 1:
                $icon = Icon::ROLE_STATUS_GREEN;
                break;
            case 0:
                $icon = Icon::ROLE_STATUS_YELLOW;
                break;
            case -1:
                $icon = Icon::ROLE_STATUS_RED;
                break;
        } ?>
        <?= Icon::create('span-full', $icon); ?>
    </td>

    <td>
        <?= $portfolioSharedChapters = EportfolioFreigabe::sharedChapters(
            $this->course_id, EportfolioGroupTemplates::getUserChapterInfos($groupId, $userPortfolioId)
        ); ?>
        /
        <?= $portfolioChapters ?>
    </td>

    <td>
        <?= EportfolioUser::getGesamtfortschrittInProzent($portfolioSharedChapters, $portfolioChapters); ?>
        %
    </td>

    <td>
        <?= EportfolioUser::getAnzahlNotizen($userPortfolioId); ?>
    </td>

    <td>
        <?= $this->render_partial('showsupervisor/_studycourse.php', [
            'studycourses' => new SimpleCollection(UserStudyCourse::findByUser($user->id)),
        ]) ?>
    </td>

    <td>
    <? if (EportfolioGroupTemplates::checkMissingTemplate($groupId, $userPortfolioId, $portfolioChapters)) : ?>
        <? $link = URLHelper::getLink('plugins.php/eportfolioplugin/showsupervisor/createlateportfolio/'
            . $groupId . '/' . $user->id . '/' . $userPortfolioId, []); ?>

        <?= \Studip\LinkButton::create(_('Fehlende Vorlagen jetzt verteilen'), $link, []); ?>

    <? endif ?>
    </td>
</tr>

<!-- <br><?= sprintf(_('Letzte Änderung: %s'), date('d.m.Y', EportfolioModel::getLastOwnerEdit($userPortfolioId))) ?> -->
