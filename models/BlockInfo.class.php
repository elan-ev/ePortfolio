<?php

/**
 * @author  <asudau@uos.de>
 *
 * @property string $Seminar_id (ePortfolio)
 * @property string $block_id
 * @property string $vorlagen_block_id
 * @property boolean $blocked
 * @property string $template_id
 * @property int $mkdate
 * @property int $chdate
 */
class BlockInfo extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'eportfolio_block_infos';
        parent::configure($config);
    }

    /**
     * Use as constructor
     * is used by VorlagenCopy, when students get their own copy of a courseware
     *
     * @param string $portfolio_id
     * @param string $block_id
     * @param string $vorlagen_block_id
     */
    public static function createEntry($portfolio_id, $block_id, $vorlagen_block_id, $template_id)
    {
        $entry                    = new self($block_id);
        $entry->vorlagen_block_id = $vorlagen_block_id;
        $entry->Seminar_id        = $portfolio_id;
        $entry->template_id       = $template_id;
        return $entry->store();
    }

    /**
     * check if a given Mooc-Block is marked as locked
     *
     * @param string $block_id
     */
    public static function isLocked($block_id)
    {
        $entry = self::findById($block_id);
        if ($entry->blocked) {
            return true;
        } else {
            return false;
        }
    }
}
