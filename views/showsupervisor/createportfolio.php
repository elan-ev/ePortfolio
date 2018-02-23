<form action="<?= URLHelper::getLink("plugins.php/courseware/exportportfolio?cid=". $masterid) ?>"

      method="post" enctype="multipart/form-data"
      <?= Request::isAjax() ? "data-dialog" : "" ?>>
    

     <div data-dialog-button>
        <?= \Studip\Button::create(_("Vorlage verteilen"), 'newvorlage') ?>
    </div>
</form>

<form action="<?= URLHelper::getLink("plugins.php/eportfolioplugin/showsupervisor/distributePortfolioContents/". $masterid . '/' . $groupid) ?>">
    <div data-dialog-button>
        <?= \Studip\Button::create(_("Fertigstellen"), 'newvorlage', array("data-dialog"=>"close")) ?>
    </div>
</form>