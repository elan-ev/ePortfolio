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

        $config['has_one']['eportfolio_group'] = array(
            'class_name' => 'EportfolioGroup',
            'assoc_foreign_key' => 'supervisor_group_id',
        );

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

        //als user in alle ePortfolios der StudentInnen eintragen
        $group = $this->eportfolio_group;
        $seminare = $group->getRelatedStudentPortfolios();
 
        if($seminare){
            foreach($seminare as $seminar){
                $seminar = new Seminar($seminar);
                $seminar->addMember($user_id, 'dozent');
                $seminar->store();
            }
        }
        //Supervisoren werden nur noch aus der Gruppe der Dozenten hinzugefügt
        //$seminar = new Seminar($this->eportfolio_group->seminar_id);
        //$seminar->addMember($user_id, 'dozent');

    }

    public function deleteUser($user_id){
        //aus Supervisorgruppe austragen
        $user = SupervisorGroupUser::findBySQL('user_id = :user_id AND supervisor_group_id = :supervisor_group_id',
                array(':user_id' => $user_id, ':supervisor_group_id' => $this->id));
        $user->delete();

        //als user aus allen ePortfolios der StudentInnen austragen
        $group = new EportfolioGroup($this->eportfolio_group);
        $seminare = $group->getRelatedStudentPortfolios();
        foreach($seminare as $seminar){
            $seminar = new Seminar($seminar);
            $seminar->deleteMember($user_id);
            $seminar->store();
        }
        //aus Portfoliogruppen-veranstaltung austragen
        $seminar = new Seminar($this->eportfolio_group);
        $seminar->deleteMember($user_id);
        $sem->store();
    }

  public static function newGroup($name){
    $group = new SupervisorGroup();
    $group->name = $name;
    $group->save();
  }

  public static function deleteGroup($group_id){
    $group = new SupervisorGroup($group_id);
    $group->delete();
  }
  //testen
  public function isUserInGroup($userId){
      $user = new SupervisorGroupUser($userId);
      return in_array($user, $this->user);
  }
}
