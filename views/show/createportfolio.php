<form data-dialog="size=auto;reload-on-close"
      action="<?= $controller->url_for('show/newportfolio') ?>"
      method="post" enctype="multipart/form-data" class="default"
    <?= Request::isAjax() ? "data-dialog" : "" ?>>
    <label>
        <?= _("Name") ?>
        <input type="text" name="name" required="" class="size-l">
    </label>
    <label>
        <?= _("Beschreibung") ?>
        <input type="text" name="beschreibung" required="" class="size-l">
    </label>
    <div data-dialog-button>
        <?= \Studip\Button::create(_("Speichern"), 'newportfolio', ["data-dialog" => "size=auto;reload-on-close"]) ?>
    </div>
</form>