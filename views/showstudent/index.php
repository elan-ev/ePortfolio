<div>
    <div class="showstudent" style='max-width:900px'>
        <? if (!$portfolio_id): ?>
        <h1>Generelle Hinweise</h1>

            <div class="showstudent_textblock">
                Ihr Dozent/Ihre Dozentin wird im Verlauf der Veranstaltung Inhalte verteilen, 
                welche Sie in Form eines ePortfolios zur Verfügung gestellt bekommen.
            </div>
            <div class="showstudent_textblock">
                Sobald erste Inhalte Verteilt wurden, finden Sie hier eine Übersicht, sowie einen Direktlink in Ihr Portfolio. <br>
                Ausserdem finden Sie eine Gesamtliste Ihrer Portfolios in Ihrem Profil unter <a href="<?php echo $link_eportfolios; ?>">ePortfolios</a>.
            </div>
        
            <div class="showstudent_videobox" style="text-align:center;">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/VAibAJquJSo" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
            </div>
        </div>
        <? else: ?>
        <div class="showstudent_textblock" style="margin:15px;">
            <a style="float:left" title='Mein Portfolio' href ='<?= URLHelper::getLink('seminar_main.php?auswahl=' . $portfolio_id[0]) ?>'> <?=Icon::create('eportfolio', 'clickable', ['size' => 200])?> </a> 

                Klicke auf das Portfolio-Symbol um direkt in dein eigenes Portfolio zu wechseln.<br>
                <br>
                Dies ist im Rahmen dieser Veranstaltung deine individuelle Arbeitsmappe.<br>
                Vorlagen und Arbeitsblätter, welche von Dozierenden verteilt werden, landen direkt in deinem eigenen ePortfolio.<br>
                <br>
                Ausserdem findest du eine Gesamtliste deiner Portfolios in deinem Profil unter <a href="<?php echo $link_eportfolios; ?>">ePortfolios</a>.<br>
                <br>
                Um deine Arbeitsergebnisse mit deinem Dozenten/deiner Dozentin zu teilen, musst du die Zugriffsrechet explizit freigeben.<br>
                Weitere Details erklären wir im folgenden Video:
        </div>
        
        <? endif ?>
    </div>
    <div  style="margin:30px">
          <iframe width="560" height="315" src="https://www.youtube.com/embed/VAibAJquJSo" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
    </div>


    <div class="showstudent_verteilteVorlagen"></div>
        <h1>Verteilte Vorlagen</h1>

        <?php if ($isThereAnyTemplate):?>

            <?php foreach ($groupTemplates as $template_id):?>
            <div>
                <?php $template = new Course($template_id['Seminar_id']);?>
                <?php echo $template->name; ?> <a href="<?php echo $link_courseware; ?>">Anschauen</a>
            </div>
            <?php endforeach;?>

        <?php else: ?>

        <div class="showstudent_hinweis">
            Es wurden noch keine Templates von Ihrem Supervisor/Dozenten verteilt.
        </div>

        <?php endif;?>

</div>

