<?php

class Group{

  var $groupId;

  public function __construct($id) {
    $groupid = $id;
  }

   public static function getTemplates($id){
    $query = "SELECT templates FROM eportfolio_groups WHERE seminar_id = :id";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array(':id'=> $id));
    $q = json_decode($statement->fetchAll()[0][0], true);
    return $q;
  }
  
  public static function getGroupMember($id) {
    $query = "SELECT user_id FROM eportfolio_groups_user WHERE Seminar_id = :id";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array(':id'=> $id));
    $q = $statement->fetchAll();
    $array = array();
    foreach ($q as $key) {
      array_push($array, $key[0]);
    }
    return $array;
  }

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
    $supervisorgroup->name = $title;
    $supervisorgroup->eportfolio_group = $id;
    $supervisorgroup->store();
    $supervisorgroup->addUser($owner);

    $supervisorgroupId = $supervisorgroup->getId();

    //TODO anpassen
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
    $query = "DELETE FROM eportfolio_groups_user WHERE user_id = :user_id AND seminar_id = :sem_id";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array(':user_id'=> $userId, ':sem_id'=> $seminar_id));
    return true;
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
