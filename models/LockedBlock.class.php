
<?php

include_once __DIR__.'/Eportfoliomodel.class.php';
include_once __DIR__.'/EportfolioGroup.class.php';
include_once __DIR__.'/SupervisorGroupUser.class.php';

/**
 * @author  <asudau@uos.de>
 *
 * @property string     $Seminar_id
 * @property string     $block_id
 * @property int        $mkdate
 * @property int        $chdate
 */
class LockedBlock extends SimpleORMap
{

    public $errors = array();

    /**
     * Give primary key of record as param to fetch
     * corresponding record from db if available, if not preset primary key
     * with given value. Give null to create new record
     *
     * @param mixed $id primary key of table
     */
    public function __construct($id = null) {

        $this->db_table = 'eportfolio_locked_blocks';

        parent::__construct($id);
    }
    
    public static function isLocked($block_id){
        $entry = self::findById($block_id);
        if($entry){
            return true;
        } else return false;
    }
    
    public static function lockBlock($Seminar_id, $block_id, $lock){
        if (($lock == 'true') && !self::findById($block_id)){
            $lockedBlock = new self($block_id);
            $lockedBlock->Seminar_id = $Seminar_id;
            $lockedBlock->mkdate = time();
            $lockedBlock->chdate = time();
            $lockedBlock->store();
        } else if (($lock == 'false') && self::findById($block_id)){
            self::deleteBySQL('block_id = :block_id',
                array(':block_id' => $block_id));
        }
    }
}
