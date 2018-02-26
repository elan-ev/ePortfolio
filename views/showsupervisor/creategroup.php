
<form data-dialog="size=auto;reload-on-close" action="<?= URLHelper::getLink("plugins.php/eportfolioplugin/showsupervisor/creategroup") ?>"
      method="post" enctype="multipart/form-data"
      <?= Request::isAjax() ? "data-dialog" : "" ?>>
    
      <input type="hidden" name="create" required="" value="1">

      <fieldset>
        <legend><?= _("Neue Gruppe erstellen") ?></legend>
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
        <?= \Studip\Button::create(_("Speichern"), array("data-dialog"=>"")) ?>
    </div>
</form>

