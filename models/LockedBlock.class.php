<?php

/**
 * @author  <asudau@uos.de>
 *
 * @property string $Seminar_id
 * @property string $block_id
 * @property int $mkdate
 * @property int $chdate
 */
class LockedBlock extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'eportfolio_locked_blocks';
        parent::configure($config);
    }

    public static function isLocked($block_id)
    {
        $entry = self::findById($block_id);
        if ($entry) {
            return true;
        } else {
            return false;
        }
    }

    public static function lockBlock($Seminar_id, $block_id, $lock)
    {
        if (($lock == 'true') && !self::findById($block_id)) {
            $lockedBlock             = new self($block_id);
            $lockedBlock->Seminar_id = $Seminar_id;
            $lockedBlock->store();
        } else if (($lock == 'false') && self::findById($block_id)) {
            self::deleteBySQL('block_id = :block_id',
                [':block_id' => $block_id]);
        }
    }
}
