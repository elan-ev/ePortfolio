
<?php

include_once __DIR__.'/Eportfoliomodel.class.php';
include_once __DIR__.'/EportfolioGroup.class.php';
include_once __DIR__.'/EportfolioGroupUser.class.php';
include_once __DIR__.'/SupervisorGroupUser.class.php';

/**
 * @author  <asudau@uos.de>
 *
 * @property string     $Seminar_id (ePortfolio)
 * @property string     $block_id
 * @property string     $vorlagen_block_id
 * @property boolean    $blocked
 * @property int        $mkdate
 * @property int        $chdate
 */
class BlockInfo extends SimpleORMap
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

        $this->db_table = 'eportfolio_block_infos';

        parent::__construct($id);
    }

    public static function createEntry($portfolio_id, $block_id, $vorlagen_block_id){
        $entry = new self($block_id);
        $entry->vorlagen_block_id = $vorlagen_block_id;
        $entry->Seminar_id = $portfolio_id;
        $entry->mkdate = time();
        if($entry->store()){
            return true;
        } else return false;
    }

    public static function isLocked($block_id){
        $entry = self::findById($block_id);
        if($entry->blocked){
            return true;
        } else return false;
    }

    public static function getPortfolioBlockByVorlagenID($block_id, $portfolio_id){
        $entry = self::findBySQL('block_id = :block_id AND Seminar_id = :portfolio_id', array(':block_id' => $block_id, 'portfolio_id' => $portfolio_id));
        if($entry && $entry->block_id){
            return $entry->block_id;
        } else return false;
    }
}
