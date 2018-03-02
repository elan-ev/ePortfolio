<?php


/**
 * @author  <asudau@uos.de>
 *
 * @property varchar     $supervisor_group_id
 * @property varchar     $user_id
 */
class SupervisorGroupUser extends SimpleORMap
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

        $this->db_table = 'supervisor_group_user';

        parent::__construct($id);
    }
    
    public static function findBySupervisorGroupId($id)
    {
        return static::findBySQL('supervisor_group_id = ?', array($id));
    }
    
    
}
