<?=var_dump($flash['semList']);?>
<?foreach ($studentportfolios as $sem): ?>
<form action="<?= URLHelper::getLink("plugins.php/courseware/importportfolio?cid=". $sem['id']) ?>"
         method="post" enctype="multipart/form-data"
      <?= Request::isAjax() ? "data-dialog" : "" ?>>
    <input type="hidden" name="path" value="<?= $path ?>">
    <div data-dialog-button>
        <?= \Studip\Button::create(_("Fertigstellen"), 'newvorlage', array("data-dialog"=>"close")) ?>
    </div>
</form>
<?endforeach?>
