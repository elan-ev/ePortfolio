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

     protected static function configure($config = array())
    {
        $config['db_table'] = 'supervisor_group_user';

        $config['has_one']['supervisor_group'] = array(
            'class_name' => 'SupervisorGroup',
            'assoc_foreign_key' => 'supervisor_group_id',
            'assoc_func' => 'findById',  
        );

        parent::configure($config);
    }


    /**
     * Give primary key of record as param to fetch
     * corresponding record from db if available, if not preset primary key
     * with given value. Give null to create new record
     *
     * @param mixed $id primary key of table
     */
    public function __construct($id = null) {

        parent::__construct($id);
    }

    public static function findBySupervisorGroupId($id)
    {
        return static::findBySQL('supervisor_group_id = ?', array($id));
    }

    public static function getSupervisorGroups($user_id){
        $array = array();
        $groupUser = SupervisorGroupUser::findBySQL('user_id = ?', array($user_id));
        foreach ($groupUser as $user) {
            $supervisor_group = new SupervisorGroup($user->supervisor_group_id);
            array_push($array, $supervisor_group);
        }
      return $array;
    }

    public static function deleteUserFromGroup($id){
        $groupUser = SupervisorGroupUser::findbySQL('supervisor_group_id = ?', array($id));
        foreach ($groupUser as $user) {
            $currentUser = new SupervisorGroupUser($user);
            $currentUser->delete();
        }
    }

}
