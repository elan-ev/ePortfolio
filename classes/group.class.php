<?php

class Group{

  var $groupId;

  public function __construct($id) {
    $groupid = $id;
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
    $course->name = 'Unbekannt';
    $course->store();
    $course->addMember($owner, 'dozent', true);

    $edit = new Course($id);
    $edit->visible = 0;
    $edit->name = 'Unbekannt';
    $edit->store();
    $sem_class = Config::get()->getValue('SEM_CLASS_PORTFOLIO_Supervisionsgruppe');

    $db = DBManager::get();
    $query = "UPDATE seminare SET Name = :title, Beschreibung = :text, status = :sem_class WHERE Seminar_id = :id ";
    $statement = $db->prepare($query);
    $statement->execute(array(':title'=> $title, ':text'=> $text, ':id'=> $id, ':sem_class' => $sem_class));
 
    $query = "INSERT INTO eportfolio_groups (seminar_id, owner_id) VALUES (:id, :owner)";
    $statement = $db->prepare($query);
    $statement->execute(array(':id'=> $id, ':owner'=> $owner));

    echo $id;
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

  public function getGroupId(){
    return $groupid;
  }

}
