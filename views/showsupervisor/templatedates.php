<form action="<?= $controller->url_for('showsupervisor/settemplatedates/' . $group_id . '/' . $template_id) ?>" method="POST" class="default" enctype="multipart/form-data">

    <label>
      Abgabedatum:
      <input required type="text" id="beginn" name="begin" data-date-picker='' value="<?= $abgabe ?>"></input><br>
    </label>

    <footer data-dialog-button>
        <?= \Studip\Button::createAccept(_("Speichern")) ?>
    </footer>
</form>
