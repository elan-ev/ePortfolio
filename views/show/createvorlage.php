
<form action="<?= URLHelper::getLink("plugins.php/eportfolioplugin/show/newvorlage") ?>"
      method="post" enctype="multipart/form-data"
      <?= Request::isAjax() ? "data-dialog" : "" ?>>
    
    <fieldset>
        <legend><?= _("Neue Vorlage") ?></legend>
        <label>
            <?= _("Name") ?>
            <input type="text" name="name" required="" class="size-l">
        </label>
        <label>
            <?= _("Beschreibung") ?>
            <input type="text" name="beschreibung" required="" class="size-l">
        </label>
    </fieldset>
    
    
     <div data-dialog-button>
        <?= \Studip\Button::create(_("Speichern"), 'newvorlage', array("data-dialog"=>"close")) ?>
    </div>
</form>

