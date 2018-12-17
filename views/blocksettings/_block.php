<?php
$block = new Mooc\DB\Block($chapter[id]);

if (in_array($block->type, ['Chapter', 'Subchapter', 'Section'])) {
    $type = $block->type;
} else $type = 'Block';

?>
    <tr class="<?= $type ?>">
        <td><h4><?= $chapter['title'] ?></h4</td>
        <?php $isBlocked = LockedBlock::isLocked($chapter['id'], true); ?>
        <td onClick="setLockBlock('<?= $chapter['id'] ?>', this, '<?= $cid ?>');" class="righttable-inner">
            <? if (!$isBlocked): ?>
                <span id="icon-<?= $chapter['id']; ?>" class="glyphicon glyphicon-ok"
                      title="<?= _('Bearbeitung durch Studierende sperren') ?>"><?= Icon::create('accept', 'clickable'); ?></span>
            <? else : ?>
                <span id="icon-<?= $chapter['id']; ?>" class="glyphicon glyphicon-remove"
                      title="<?= _('Bearbeitung durch Studierende erlauben') ?>"><?= Icon::create('decline', 'clickable'); ?></span>
            <? endif; ?>
        </td>
    </tr>

<? if (in_array($block->type, ['Chapter', 'Subchapter', 'Section'])): ?>
    <? foreach ($block->children as $subchapter): ?>
        <?= $this->render_partial('blocksettings/_block.php', ['chapter' => $subchapter, 'cid' => $cid]); ?>
    <? endforeach; ?>
<? endif; ?>