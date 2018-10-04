<?php

include_once __DIR__.'/SupervisorGroup.class.php';
include_once __DIR__.'/EportfolioActivity.class.php';

/**
 * @author  <asudau@uos.de>
 *
 * @property varchar                $seminar_id
 * @property varchar                $owner_id
 * @property text                   $templates
 * @property varchar                $supervisor_group_id
 * @property CourseMember[]  $user
 */
class EportfolioGroup extends SimpleORMap
{

    public $errors = array();

    //testen
    protected static function configure($config = array())
    {
        $config['db_table'] = 'eportfolio_groups';

        $config['belongs_to']['group_owner'] = array(
            'class_name' => 'User',
            'foreign_key' => 'owner_id', );

        $config['has_many']['user'] = array(
            'class_name' => 'CourseMember',
            'assoc_foreign_key' => 'seminar_id',
            'assoc_func' => 'findByCourse',
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

    //TODO: ich glaube diese Funktion hier wird garnicht benutzt
    public static function getFavotitenDerGruppe($id){
      $templates = EportfolioGroup::findBySQL('Seminar_id = :cid', array(':cid' => $id));
      $templateList = json_decode($templates[0]->templates);

      foreach ($templateList as $temp) {
        $query = "SELECT Seminar_id FROM eportfolio WHERE group_id = :id AND favorite = 1";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array(':id'=> $temp));
        $check = $statement->fetchAll();
      }
    }

    public static function getGroupMember($id) {
      $group = EportfolioGroup::find($id);
      $array = array();
      foreach ($group->user as $user) {
        ($user->status == 'autor') ? array_push($array, $user->user_id): '';
      }
      return $array;
    }

    public static function getAllSupervisors($id) {
      $group = new EportfolioGroup($id);
      $supervisorGroup = new SupervisorGroup($group->supervisor_group_id);
      $array = array();
      foreach ($supervisorGroup->user as $user) {
        array_push($array, $user->user_id);
      }
      return $array;
    }

  //TODO anpassen
  public static function newGroup($owner, $sem_id){

    $course = Course::find($sem_id);

    $supervisorgroup = new SupervisorGroup();
    $supervisorgroup->name = $course->name;
    $supervisorgroup->store();

    //var_dump($id);
    $group = new EportfolioGroup($sem_id);
    $group->supervisor_group_id = $supervisorgroup->id;
    $group->owner_id = $owner;
    $group->store();

    $supervisorgroup->eportfolio_group = $group;
    $supervisorgroup->store();
    $supervisorgroup->addUser($owner);

  }

  public static function getOwner($id){
    $query = "SELECT owner_id FROM eportfolio_groups WHERE seminar_id = :id";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array(':id'=> $id));
    return $statement->fetchAll()[0][0];
  }

  public static function getFirstGroupOfuser($userId){
    return self::getAllGroupsOfSupervisor($userId)[0];
  }

  public static function getAllGroupsOfUser($userId){
    $query = "SELECT seminar_id FROM eportfolio_groups_user WHERE user_id = :user_id";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array(':user_id'=> $userId));
    return $statement->fetchAll();
  }

  //brauchen wir auf jeden Fall
  public static function getAllGroupsOfSupervisor($userId){
      $ownGroups = EportfolioGroup::findBySQL('owner_id = :id', array(':id'=> $userId));
      $addedGroups = SupervisorGroupUser::getSupervisorGroups($userId);

      $array = array();
      foreach ($ownGroups as $group) {
        array_push($array, $group->seminar_id);
      }
      foreach ($addedGroups as $group) {
        if ($group->eportfolio_group->seminar_id){
            array_push($array, $group->eportfolio_group->seminar_id);
        }
      }

      return array_unique($array);
  }

  public function getGroupId(){
    return $this->seminar_id;
  }

  public static function getSupervisorGroupId($id){
    $group = self::find($id);
    return $group->supervisor_group_id;
  }

  public function getRelatedStudentPortfolios(){
      $member = $this->user;
      $portfolios = array();
      if ($this->templates) {

        foreach ($member as $key) {
          $portfolio = Eportfoliomodel::findBySQL('group_id = :groupid AND owner_id = :value', array(':groupid'=> $this->seminar_id, ':value'=> $key->user_id));
          array_push($portfolios, $portfolio[0]->Seminar_id);
        }
        return $portfolios;
      } else return NULL;
  }

  public static function deleteGroup($cid){

    #supervisorgroup holen
    $supervisor_group_id = self::findById($cid)->supervisor_group_id;

    // #eportfolio_groups löschen
    $group = new EportfolioGroup($cid);
    $group->delete();

    // #eportfolio_groups_user löschen
    $query = "DELETE FROM eportfolio_groups_user WHERE seminar_id = :seminar_id";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array(':seminar_id'=> $cid));

    // #seminar mit id löschen
    $course = new Seminar($cid);
    $course->delete();

    #supervisor_group löschen
    SupervisorGroup::deleteGroup($supervisor_group_id);

    #eportfolio mit group_id löschen
    $eportfolio = new Eportfoliomodel($cid);
    $eportfolio->delete();

    #eportfolio_user

  }

  /**
  * Erstellt einen Eintrag in der eportfolio_group_templates Tabelle
  * Damit es nicht knallt wenn beim verteilen einer Vorlage mal was schief geht machen wir hier ein INSERT IGNORE
  * Langfristig könnte man beim Verteilen von Vorlagen noch was drehen, dass das Template nur eingetragen wird, wenn wirklich alles rund gelaufen ist..
  * user_id ist in diesem Fall die User_id des Nutzers der die Vorlage verteilt
  **/
  public static function createTemplateForGroup($group_id, $template_id, $user_id){
    $time = time();
    $query = "INSERT IGNORE INTO eportfolio_group_templates VALUES (:group_id , :template_id, 1, :t, 0, :creator)";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array(':group_id' => $group_id , ':template_id' => $template_id, ':t' => $time, ':creator' => $user_id));
  }

  /**
  * Makiert ein Template als Favorit
  **/
  public static function markTemplateAsFav($group_id, $template_id){
    $query = "UPDATE eportfolio_group_templates SET favorite = 1 WHERE group_id = :group_id AND Seminar_id = :template_id";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array(':group_id' => $group_id , ':template_id' => $template_id));
  }

  /**
  * Löscht ein Template als Favorit
  **/
  public static function deletetemplateAsFav($group_id, $template_id){
    $query = "UPDATE eportfolio_group_templates SET favorite = 0 WHERE group_id = :group_id AND Seminar_id = :template_id";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array(':group_id' => $group_id , ':template_id' => $template_id));
  }

  /**
  * Gibt den Wert 1 zurück wenn Template in der Gruppe als Favorit makiert ist
  **/
  public static function checkIfMarkedAsFav($group_id, $template_id){
    $query = "SELECT favorite FROM eportfolio_group_templates WHERE group_id = :group_id AND Seminar_id = :template_id AND favorite = 1";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array(':group_id'=> $group_id, ':template_id' => $template_id));
    $result = $statement->fetchAll();
    if($result[0][0] == 1){
      return 1;
    } else {
      return 0;
    }
  }

  /**
  * Gibt ein Array mit den ID's den als Favorit makierten Templates zurück
  **/
  public static function getAllMarkedAsFav($group_id){
    $query = "SELECT Seminar_id FROM eportfolio_group_templates WHERE group_id = :group_id AND favorite = 1";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array(':group_id'=> $group_id));
    $result = $statement->fetchAll();
    $return = array();
    foreach ($result as $key) {
      array_push($return, $key[0]);
    }
    return $return;
  }

  /**
  * Gibt die Anzahl aller Kapitel (Chapter) in den Templates wieder
  **/
  public static function getAnzahlAllerKapitel($group_id){
    $anzahl = 0;
    $GroupTemplates = EportfolioGroupTemplates::findBySQL('group_id = :id', array(':id'=> $group_id));
    foreach ($GroupTemplates as $temp) {
      $query = "SELECT COUNT(type) FROM mooc_blocks WHERE seminar_id = :id AND type = 'Chapter'";
      $statement = DBManager::get()->prepare($query);
      $statement->execute(array(':id'=> $temp->Seminar_id));
      $result = $statement->fetchAll();
      $anzahl += $result[0][0];
    }
    return $anzahl;
  }

  //all activities in Group ($user can be used to check if activity is a new one for given user)
  public function getActivities($user = NULL){
    //$activities = EportfolioActivity::getDummyActivitiesForGroup($this->seminar_id);
    $activities = EportfolioActivity::getActivitiesForGroup($this->seminar_id);
    return $activities;
  }

  public static function getActivitiesOfUser($seminar_id, $user){
    $activities = EportfolioActivity::getActivitiesOfGroupUser($seminar_id, $user);
    return $activities;
  }

  /**
  * Gibt die der neuen Aktivitäten eines Nutzers in der Gruppe zurück
  **/
  public function getNumberOfNewActivities(){
    return sizeof(EportfolioActivity::newActivities($this->seminar_id));
  }

  /**
    * Gibt ein Array mit den Portfolio ID's eines Users
    * innerhalb einer Gruppe wieder
    **/
    public static function getPortfolioIDsFromUserinGroup($group_id, $user_id){
      $query = "SELECT * FROM eportfolio WHERE owner_id = :owner_id AND group_id = :group_id";
      $statement = DBManager::get()->prepare($query);
      $statement->execute(array(':owner_id'=> $user_id, ':group_id' => $group_id));
      $result = $statement->fetchAll();
      $return = array();
      foreach ($result as $key) {
        array_push($return, $key[0]);
      }
      return $return;
    }

    /**
    * Gibt die Anzahl der freigegeben Kapitel zurück
    **/
    public static function getAnzahlFreigegebenerKapitel($user_id, $group_id){
      $anzahl = 0;
      $templates = EportfolioGroup::getPortfolioIDsFromUserinGroup($group_id, $user_id);
      foreach ($templates as $temp) {
        $query =  "SELECT COUNT(e1.Seminar_id) FROM eportfolio e1
                  JOIN eportfolio_freigaben e2 ON e1.Seminar_id = e2.Seminar_id
                  WHERE e1.Seminar_id = :id";

        $statement = DBManager::get()->prepare($query);
        $statement->execute(array(':id'=> $temp));
        $result = $statement->fetchAll();
        $anzahl += $result[0][0];
      }
      return $anzahl;
    }

    /**
    * Gibt die Verhältnis freigeben/gesamt in Prozent wieder
    **/
    public static function getGesamtfortschrittInProzent($user_id, $group_id){
      $oben = EportfolioGroup::getAnzahlFreigegebenerKapitel($user_id, $group_id);
      $unten = EportfolioGroup::getAnzahlAllerKapitel($group_id);
      $zahl = $oben / $unten * 100;
      $zahl = round($zahl, 1);
      return $zahl;
    }

    /**
    * Gibt die Anzahl der Notizen für den Supervisor eines users
    * innerhalb einer Gruppe wieder
    **/
    public static function getAnzahlNotizen($user_id, $group_id){
      $anzahl = 0;
      $temps = EportfolioGroup::getPortfolioIDsFromUserinGroup($group_id, $user_id);
      foreach ($temps as $temp) {
        $query = "SELECT COUNT(type) FROM mooc_blocks WHERE Seminar_id = :seminar_id AND type = 'PortfolioBlockSupervisor'";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array(':seminar_id'=> $temp));
        $result = $statement->fetchAll();
        $anzahl += $result[0][0];
      }
      return $anzahl;
    }

    /**
    * Gibt die ID des Portfolios des Nutzers in einer Gruppe zurück
    **/
    public static function getPortfolioIdOfUserInGroup($user_id, $group_id){
      $query = "SELECT Seminar_id FROM eportfolio WHERE owner_id = :owner_id AND group_id = :group_id";
      $statement = DBManager::get()->prepare($query);
      $statement->execute( array(':owner_id' => $user_id, ':group_id' => $group_id));
      $result = $statement->fetchAll();
      return $result[0][0];
    }

    /**
    * Gibt die Anzahl an Neuigkeiten eines Nutzers in einer Gruppe zurück
    **/
    public static function getAnzahlAnNeuerungen($userid, $groupid){
      return sizeof(self::getActivitiesOfUser($groupid, $userid));
    }

    public function isSupervisor($user_id){
        return SupervisorGroupUser::findBySQL('$supervisor_group_id = :group_id AND user_id = :user_id', array(':group_id' => $this->supervisor_group_id, ':user_id' => $user_id));
    }

}
