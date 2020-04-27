<div>
    <div class="showstudent" style='max-width:900px'>
        <? if (!$portfolio_id): ?>
            <h1>Generelle Hinweise</h1>
                <div class="showstudent_textblock">
                    Dein Dozent/Deine Dozentin wird im Verlauf der Veranstaltung Inhalte verteilen,
                    welche Dir in Form eines ePortfolios zur Verfügung gestellt werden.
                </div>
                <div class="showstudent_textblock">
                    Sobald erste Inhalte Verteilt wurden, findest Du hier eine Übersicht, sowie einen Direktlink in Dein Portfolio. <br>
                    Ausserdem findest Du eine Gesamtliste Deiner Portfolios in Deinem Profil unter
                    <a href="<?= URLHelper::getLink('plugins.php/eportfolioplugin/show') ?>"> ePortfolios</a>. <br>
                    <? if ($groupTemplates): ?>
                    <br>
                    <b>Es wurden schon Vorlagen in dieser Veranstaltung verteilt:<br>
                    <a href="<?= URLHelper::getLink('plugins.php/eportfolioplugin/showstudent/createlateportfolio/' . $group_id . '/' . $userid, []) ?>"> Portfolio anlegen</a> <br>
                    </b><br>
                    <? endif ?>
                    Weitere Details zur Portfolioarbeit erklären wir im folgenden Video:
                </div>
            </div>
        <? else: ?>
            <div class="showstudent_textblock" style="margin:15px;">
                <a style="float:left" title='Mein Portfolio' href ='<?= URLHelper::getLink('seminar_main.php?auswahl=' . $portfolio_id, ['return_to' => Context::getId()]) ?>'>
                    <?=Icon::create('eportfolio', 'clickable', ['size' => 200])?>
                </a>

                    Klicke auf das Portfolio-Symbol um direkt in dein eigenes Portfolio zu wechseln.<br>
                    <br>
                    Dies ist im Rahmen dieser Veranstaltung deine individuelle Arbeitsmappe.<br>
                    Vorlagen und Arbeitsblätter, welche von Dozierenden verteilt werden, landen direkt in deinem eigenen ePortfolio.
                    Um deine Arbeitsergebnisse mit deinem Dozenten/deiner Dozentin zu teilen, musst du die Zugriffsrechte explizit freigeben.<br>
                    <br>
                    Falls du in mehreren Veranstaltungen mit Portfolios arbeitest, findest du eine Gesamtliste deiner Portfolios in deinem Profil
                    unter <a href="<?= URLHelper::getLink('plugins.php/eportfolioplugin/show') ?>"> ePortfolios</a>. <br>
                    <br>
                    Weitere Details zur Portfolioarbeit erfährst du im Video weiter unten.
            </div>
        <? endif ?>

        <? if ($groupTemplates): ?>
        <div class="showstudent_verteilteVorlagen"></div>
            <h1> Verteilte Vorlagen
                <?= Icon::create('info-circle', 'clickable', [
                'title' => 'Diese Vorlagen wurden bereits in der Veranstaltung verteilt'
                ])?>
            </h1>
            <? foreach ($groupTemplates as $template):?>
                <div>
                    <?= htmlReady($template->name)?> verteilt am
                    <?= date('d.m.Y', EportfolioGroupTemplates::getWannWurdeVerteilt($group_id, $template->id)) ?>
                </div>
            <? endforeach ?>
        <? endif ?>
    </div>

    <div  style="margin:30px">
          <!-- Platzhalter für Erklärvideo -->
    </div>
</div>
