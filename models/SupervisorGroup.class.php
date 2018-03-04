<?php


/**
 * @author  <asudau@uos.de>
 *
 * @property varchar                $id
 * @property varchar                $eportfolio_group
 * @property string                 $name
 * @property SupervisorGroupUser[]  $user
 */
class SupervisorGroup extends SimpleORMap
{

    public $errors = array();

    protected static function configure($config = array())
    {
        $config['db_table'] = 'supervisor_group';

        $config['belongs_to']['eportfolio_group'] = array(
            'class_name' => 'EportfolioGroups',
            'foreign_key' => 'eportfolio_group', );

        $config['has_many']['user'] = array(
            'class_name' => 'SupervisorGroupUser',
            'assoc_foreign_key' => 'supervisor_group_id',
            'assoc_func' => 'findBySupervisorGroupId',
            'on_delete' => 'delete',
            'on_store' => 'store',
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
    
    public function addUser($user_id){
        $user = new SupervisorGroupUser();
        $user->supervisor_group_id = $this->id;
        $user->user_id = $user_id;
        $user->store();
        
        $seminar = new Seminar($this->eportfolio_group);
        $seminar->addMember($user_id, 'dozent');
        $seminar->store();
    }

    public function deleteUser($user_id){
        $user = SupervisorGroupUser::findBySQL('user_id = :user_id AND supervisor_group_id = :supervisor_group_id',
                array(':user_id' => $user_id, ':supervisor_group_id' => $this->id));
        $user->delete();
        
        $seminar = new Seminar($this->eportfolio_group);
        $seminar->deleteMember($user_id);
        $sem->store();
    }

  public static function newGroup($name){
    $group = new Supervisorgroup();
    $group->name = $name;
    $group->save();
  }

  public static function deleteGroup($group_id){
    $group = SupervisorGroup($group_id); 
    $group->delete();
  }
  //testen
  public function isUserInGroup($userId){
      $user = new SupervisorGroupUser($userId);
      return in_array($user, $this->user);
  }
}
