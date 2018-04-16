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
