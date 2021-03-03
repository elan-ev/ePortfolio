<form data-dialog="size=auto;reload-on-close"
      action="<?= URLHelper::getLink("plugins.php/eportfolioplugin/showsupervisor/updatevorlage/" . basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) ?>"
      method="post" enctype="multipart/form-data" class="default"
    <?= Request::isAjax() ? "data-dialog" : "" ?>>

    <label>
        <?= _("Name") ?>
        <input type="text" name="name" required="" class="size-l" value="<?= htmlReady($template_name) ?>">
    </label>
    <label>
        <?= _("Beschreibung") ?>
        <input type="text" name="description" required="" class="size-l" value="<?= $template_description ?>">
    </label>

    <div data-dialog-button>
        <?= \Studip\Button::create(_("Speichern"), 'updatevorlage', ["data-dialog" => ""]) ?>
    </div>
</form>