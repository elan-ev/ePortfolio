<? if (Request::isXhr()) : ?>
    <? foreach (PageLayout::getMessages() as $messagebox) : ?>
        <?= $messagebox ?>
    <? endforeach ?>
<? endif ?>

<form data-dialog="size=auto;reload-on-close" action="<?= URLHelper::getLink("plugins.php/eportfolioplugin/create") ?>"
      method="post" enctype="multipart/form-data">
      
</form>