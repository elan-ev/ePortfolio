<?php


/**
 * @author  <asudau@uos.de>
 *
 * @property int     $id
 * @property string  $type
 * @property int     $related_contact
 * @property string  $content
 * @property int     $mkdate
 */
class EportfolioGroup extends SimpleORMap
{

    public $errors = array();

    //testen
    protected static function configure($config = array())
    {
        $config['db_table'] = 'eportfolio_groups';

        $config['belongs_to']['owner_id'] = array(
            'class_name' => 'StudipUser',
            'foreign_key' => 'owner_id', );

        $config['has_many']['user'] = array(
            'class_name' => 'EportfolioGroupUser',
            'assoc_foreign_key' => 'seminar_id',
            'assoc_func' => 'findByGroupId',
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
    //testen
    public static function getTemplates($id){
        $group = EportfolioGroup::findById();
        $q = json_decode($group->templates, true);
        return $q;
    }
  
  public static function getGroupMember($id) {
    $group = new EportfolioGroup($id);
    $array = array();
    foreach ($group->user as $user) {
      array_push($array, $user->user_id);
    }
    return $array;
  }

  //TODO anpassen
  public static function create($owner, $title, $text){
    $course = new Seminar();
    $id = $course->getId();
    $course->name = $title;
    $course->store();
    $course->addMember($owner, 'dozent', true);

    //was machen die folgenden vier Zeilen?
    $edit = new Course($id);
    $edit->visible = 0;
    $edit->name = $title;
    $edit->store();
    $sem_class = Config::get()->getValue('SEM_CLASS_PORTFOLIO_Supervisionsgruppe');

    $supervisorgroup = new Supervisorgroup();
    $supervisorgroup->setName($title);
    $supervisorgroup->save();
    $supervisorgroup->addUser($owner);

    $supervisorgroupId = $supervisorgroup->getId();

    $db = DBManager::get();
    $query = "UPDATE seminare SET Name = :title, Beschreibung = :text, status = :sem_class WHERE Seminar_id = :id ";
    $statement = $db->prepare($query);
    $statement->execute(array(':title'=> $title, ':text'=> $text, ':id'=> $id, ':sem_class' => $sem_class));
 
    $query = "INSERT INTO eportfolio_groups (seminar_id, owner_id, supervisor_group_id) VALUES (:id, :owner, :supervisorgroupid)";
    $statement = $db->prepare($query);
    $statement->execute(array(':id'=> $id, ':owner'=> $owner, ':supervisorgroupid' => $supervisorgroupId));

    return $id;
  }

  public static function deleteUser($userId, $seminar_id){
    $user = EportfolioGroupUser::findBySQL('user_id = :user_id AND seminar_id = :seminar_id',
                array(':user_id' => $user_id, ':seminar_id' => $seminar_id));
        $user->delete();
        
        $seminar = new Seminar($this->eportfolio_group);
        $seminar->deleteMember($user_id);
        $sem->store();
  }

  public static function getOwner($id){
    $query = "SELECT owner_id FROM eportfolio_groups WHERE seminar_id = :id";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array(':id'=> $id));
    return $statement->fetchAll()[0][0];
  }

  public static function getFirstGroupOfuser($userId){
    $query = "SELECT seminar_id FROM eportfolio_groups WHERE owner_id = :user_id";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array(':user_id'=> $userId));
    return $statement->fetchAll()[0][0];
  }

  public static function getAllGroupsOfUser($userId){
    $query = "SELECT seminar_id FROM eportfolio_groups_user WHERE user_id = :user_id";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array(':user_id'=> $userId));
    return $statement->fetchAll();
  }
  
  public static function getAllGroupsOfSupervisor($userId){
      $query = "SELECT seminar_id FROM eportfolio_groups WHERE owner_id = :id";
      $statement = DBManager::get()->prepare($query);
      $statement->execute(array(':id'=> $userId));
      $array = array();
      foreach ($statement->fetchAll() as $key) {
        array_push($array, $key[0]);
      }
      return $array;
  }

  public function getGroupId(){
    return $groupid;
  }

  public static function getSupervisorGroupId($id){
    $query = "SELECT supervisor_group_id FROM eportfolio_groups WHERE seminar_id = :seminar_id";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array(':seminar_id'=> $id));
    return $statement->fetchAll()[0][0];
  }
    
}
