<div>
    <div class="showstudent" style='max-width:900px'>
        <? if (!$portfolio_id): ?>
            <h1>Generelle Hinweise</h1>
                <div class="showstudent_textblock">
                    Ihre Dozentin/ Ihr Dozent wird im Verlauf der Veranstaltung Aufgaben zur Erstellung Ihres ePortfolios verteilen.<br>
                    <br>
                </div>
                <div class="showstudent_textblock">
                    Sobald das passiert ist, finden Sie hier eine kurze Übersicht sowie einen Direktlink in Ihr ePortfolio.
                    Falls Sie in mehreren Veranstaltungen mit ePortfolios arbeiten, finden Sie eine Gesamtliste Ihrer
                    ePortfolios in Ihrem Stud.IP-Profil unter dem Menüpunkt
                    <a href="<?= URLHelper::getLink('plugins.php/eportfolioplugin/show') ?>"> ePortfolios</a>. <br>
                    <? if ($groupTemplates): ?>
                    <br>
                    <b>
                        Es wurden schon Vorlagen in dieser Veranstaltung verteilt,
                        bitte wenden Sie sich an Ihre Lehrenden.
                    </b>
                    <? endif ?>
                </div>
            </div>
        <? else: ?>
            <div class="showstudent_textblock" style="margin:15px;">
                <a style="float:left" title='Mein Portfolio' href ='<?= URLHelper::getLink('seminar_main.php?auswahl=' . $portfolio_id, ['return_to' => Context::getId()]) ?>'>
                    <?=Icon::create('eportfolio', 'clickable', ['size' => 200])?>
                </a>

                Klicken Sie auf das ePortfolio-Symbol. <br>
                <br>
                Dies ist im Rahmen dieser Veranstaltung Ihre individuelle Arbeitsmappe.<br>
                Vorlagen und Arbeitsblätter, die von Dozierenden verteilt werden, landen direkt in Ihrem eigenen ePortfolio. <br>
                <br>
                Um nach Ihrer Bearbeitung die Arbeitsergebnisse mit Ihren Dozent*innen zu teilen, müssen Sie
                die Zugriffsrechte explizit für diese freigeben.<br>
                <br>
                Falls Sie in  mehreren Veranstaltungen mit ePortfolios arbeiten, finden
                Sie eine Gesamtliste Ihrer ePortfolios in Ihrem Stud.IP-Profil unter dem Menüpunkt
                <a href="<?= URLHelper::getLink('plugins.php/eportfolioplugin/show') ?>"> ePortfolios</a>.
                Dort finden Sie auch ePortfolios aufgelistet, die Ihnen andere Studierende
                zur Ansicht freigegeben haben.
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
                    <?= date('d.m.Y', EportfolioGroupTemplates::getWannWurdeVerteilt($course_id, $template->id)) ?>
                </div>
            <? endforeach ?>
        <? endif ?>
    </div>

    <div  style="margin:30px">
          <!-- Platzhalter für Erklärvideo -->
    </div>
</div>
