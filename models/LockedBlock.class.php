
<?php

include_once __DIR__.'/Eportfoliomodel.class.php';
include_once __DIR__.'/EportfolioGroup.class.php';
include_once __DIR__.'/EportfolioGroupUser.class.php';
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
            $seminar = new Seminar($entry->Seminar_id);
            $status = $seminar->getStatus();
            if ($status == Config::get()->getValue('SEM_CLASS_PORTFOLIO')){
                return true;
            }
        }
        return false;
    }
}
