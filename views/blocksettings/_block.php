<?php
$block = new Mooc\DB\Block($chapter['id']);

if (in_array($block->type, ['Chapter', 'Subchapter', 'Section'])) {
    $type = $block->type;
} else $type = 'Block';

?>
    <tr class="<?= $type ?>">
        <td><strong><?= $chapter['title'] ?></strong</td>
        <?php $isBlocked = LockedBlock::isLocked($chapter['id'], true); ?>
        <td onClick="setLockBlock('<?= $chapter['id'] ?>', this, '<?= $course->id ?>');" class="righttable-inner">
            <? if (!$isBlocked): ?>
                <span id="icon-<?= $chapter['id']; ?>" class="glyphicon glyphicon-ok"
                      title="<?= _('Bearbeitung durch Studierende sperren') ?>"><?= Icon::create('accept', Icon::ROLE_CLICKABLE); ?></span>
            <? else : ?>
                <span id="icon-<?= $chapter['id']; ?>" class="glyphicon glyphicon-remove"
                      title="<?= _('Bearbeitung durch Studierende erlauben') ?>"><?= Icon::create('decline', Icon::ROLE_CLICKABLE); ?></span>
            <? endif; ?>
        </td>
    </tr>

<? if (in_array($block->type, ['Chapter', 'Subchapter', 'Section'])): ?>
    <? foreach ($block->children as $subchapter): ?>
        <?= $this->render_partial('blocksettings/_block.php', ['chapter' => $subchapter, 'cid' => $course->id]); ?>
    <? endforeach; ?>
<? endif; ?>