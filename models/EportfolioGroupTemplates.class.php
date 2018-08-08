
<?php

include_once __DIR__.'/Eportfoliomodel.class.php';
include_once __DIR__.'/EportfolioGroup.class.php';
include_once __DIR__.'/SupervisorGroupUser.class.php';

/**
 * @author  <mkipp@uos.de>
 *
 * @property varchar    $group_id
 * @property varchar    $Seminar_id
 * @property int        $favorite
 * @property int        $mkdate
 * @property int        $abgabe_datum
 */
class EportfolioGroupTemplates extends SimpleORMap
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

        $this->db_table = 'eportfolio_group_templates';
        
        parent::__construct($id);
    }

}
