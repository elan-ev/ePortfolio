<?php


/**
 * @author  <asudau@uos.de>
 *
 * @property varchar    $user_id
 * @property varchar    $Seminar_id
 * @property varchar    $eportfolio_id
 * @property string     $status
 * @property text       $eportfolio_access
 * @property int        $owner
 */
class EportfolioUser extends SimpleORMap
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

        $this->db_table = 'eportfolio_user';

        parent::__construct($id);
    }
    
    
}
