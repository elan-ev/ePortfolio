<?php 
 $block =  new Mooc\DB\Block($chapter[id]); 

if (in_array($block->type, array('Chapter', 'Subchapter', 'Section'))){
    $type = $block->type;
} else $type = 'Block';

?>

<tr class = '<?=$type?>' >
    <td><h4><?= $chapter[title] ?></h4</td>
        <?php  
        $isBlocked = LockedBlock::isLocked($chapter[id], true);
     
       ?>
      <td onClick="setLockBlock('<?= $chapter[id]?>', this, '<?= $cid ?>');" class="righttable-inner">

        <?php if(!$isBlocked):?>
          <span id="icon-<?php echo $chapter[id]; ?>" class="glyphicon glyphicon-ok" title='Bearbeitung durch Studierende sperren'><?= Icon::create('accept', 'clickable'); ?></span>
        <?php else :?>
          <span id="icon-<?php echo $chapter[id]; ?>" class="glyphicon glyphicon-remove" title='Bearbeitung durch Studierende erlauben'><?= Icon::create('decline', 'clickable'); ?></span>
        <?php endif;?>

      </td>
   </tr>
   
   <?php if (in_array($block->type, array('Chapter', 'Subchapter', 'Section'))): ?>
   <?php foreach ($block->children as $subchapter): ?>
        <?= $this->render_partial('blocksettings/_block.php', array('chapter' => $subchapter, 'cid' => $cid)); ?>
    <?php endforeach; ?>
    <?php endif; ?>