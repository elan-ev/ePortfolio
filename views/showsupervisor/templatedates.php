<form action="<?= $controller->url_for('showsupervisor/settemplatedates/' . $group_id . '/' . $template_id) ?>" method="POST" class="default" enctype="multipart/form-data">

    <label>
      Abgabedatum:
      <input type="date" id="beginn" name="begin" value="<?= $abgabe ?>" class="size-l"></input><br>
    </label>

    <footer data-dialog-button>
        <?= \Studip\Button::createAccept(_("Speichern")) ?>
    </footer>
</form>
