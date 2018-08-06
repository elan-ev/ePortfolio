<form action="<?= $controller->url_for('showsupervisor/settemplatedates/' . $group_id . '/' . $template_id) ?>" method="POST" class="default" enctype="multipart/form-data">

    <label>
      Abgabedatum:
      <input required type="text" id="beginn" name="begin" data-date-picker='' value="<?php echo $abgabe; ?>"></input><br>
    </label>

    <?= \Studip\Button::createAccept(_("Speichern")) ?>

</form>
