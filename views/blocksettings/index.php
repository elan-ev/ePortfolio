<table class="default" id='blocksettings'>
    <caption><?= _('Blöcke für die weitere Bearbeitung durch Studierende sperren') ?></caption>
    <tr class="sortable">
        <th><?= _('Element') ?></th>
        <th><?= _('Bearbeiten erlaubt') ?></th>
    </tr>
    <tbody>
        <? foreach ($chapterList as $chapter): ?>
            <?= $this->render_partial('blocksettings/_block.php', ['chapter' => $chapter, 'cid' => $cid]); ?>
        <? endforeach; ?>
    </tbody>
</table>


<script type="text/javascript"
        src="<?php echo $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'plugins_packages/uos/EportfolioPlugin/assets/js/eportfolio.js'; ?>"></script>
<script type="text/javascript">

    var cid = '<?php echo $cid; ?>';

    function setLockBlock(blockid, obj, cid) {
        var status = $(obj).children('span').hasClass('glyphicon-ok');
        var url = STUDIP.URLHelper.getURL('plugins.php/eportfolioplugin/blocksettings/lockBlock/' + cid + '/' + blockid + '/' + status);
        $.ajax({
            type: "POST",
            url: url,
            success: function (data) {
                if (status === false) {
                    $(obj).empty().append('<span class="glyphicon glyphicon-ok"><?php echo Icon::create('accept', 'clickable'); ?></span>');
                } else {
                    $(obj).empty().append('<span class="glyphicon glyphicon-remove"><?php echo Icon::create('decline', 'clickable'); ?></span>');
                }

            }
        });
    }
</script>
