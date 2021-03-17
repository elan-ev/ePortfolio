<form data-dialog="size=auto;reload-on-close"
      action="<?= $controller->link_for('show/newportfolio') ?>"
      method="post" enctype="multipart/form-data" class="default"
    <?= Request::isAjax() ? "data-dialog" : "" ?>>
    <label>
        <?= _("Name") ?>
        <input type="text" name="name" required="">
    </label>
    <label>
        <?= _("Beschreibung") ?>
        <input type="text" name="beschreibung" required="">
    </label>
    <div data-dialog-button>
        <?= \Studip\Button::create(_("Speichern"), 'newportfolio', ["data-dialog" => "size=auto;reload-on-close"]) ?>
    </div>
</form>