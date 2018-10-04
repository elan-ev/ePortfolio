<div>
    <div class="showstudent">
        <h1>Generelle Hinweise</h1>

            <div class="showstudent_videobox">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/VAibAJquJSo" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
            </div>

            <div class="showstudent_textblock">
                Ihr Dozent/Ihre Dozentin wird im Verlauf der Veranstaltung Inhalte verteilen, 
                welche Sie in Form eines ePortfolios zur Verfügung gestellt bekommen.
            </div>
            <div class="showstudent_textblock">
                Sobald erste Inhalte Verteilt wurden, finden Sie hier eine Übersicht, sowie einen Direktlink in Ihr Portfolio. <br>
                Ausserdem finden Sie eine Gesamtliste Ihrer Portfolios in Ihrem Profil unter <a href="<?php echo $link_eportfolios; ?>">ePortfolios</a>.
            </div>
        </div>
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

