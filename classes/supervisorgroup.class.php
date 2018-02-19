<?php

class Supervisorgroup{

  var $supervisorgroupId = null;
  var $supervisorgroupName = "DEFAULT_NAME";

  public function __construct($group_or_false = FALSE) {
    if ($group_or_false == FALSE) {
      $this->setId();
    } else {
      $this->supervisorgroupId = $group_or_false;
    }
  }

  private function setId(){
    $sem = new Seminar();
    $this->supervisorgroupId = $sem->createId();
  }

  public function setName($name){
    $this->supervisorgroupName = $name;
  }

  public function getId(){
    return $this->supervisorgroupId;
  }

  public function getName(){
    $db = DBManager::get();
    $query = "SELECT name FROM supervisor_group WHERE id = :id";
    $statement = $db->prepare($query);
    $statement->execute(array(':id'=> $this->supervisorgroupId));
    
    return $statement->fetchAll()[0][name];
  }

  public function getUsersOfGroup(){
    $db = DBManager::get();
    $query = "SELECT * FROM supervisor_group_user WHERE supervisor_group_id = :id";
    $statement = $db->prepare($query);
    $statement->execute(array(':id'=> $this->supervisorgroupId));
    return $statement->fetchAll();
  }

  public function addUser($userId){
    $db = DBManager::get();
    $query = "INSERT INTO supervisor_group_user (supervisor_group_id, user_id) VALUES (:id, :userId)";
    $statement = $db->prepare($query);
    $statement->execute(array(':id'=> $this->supervisorgroupId, ':userId' => $userId));
  }

  public function deleteUser($userId){
    $db = DBManager::get();
    $query = "DELETE FROM supervisor_group_user WHERE supervisor_group_id = :id AND user_id = :userId";
    $statement = $db->prepare($query);
    $statement->execute(array(':id'=> $this->supervisorgroupId, ':userId' => $userId));
  }

  public function save(){
    $db = DBManager::get();
    $query = "INSERT INTO supervisor_group (id, name) VALUES (:id, :name)";
    $statement = $db->prepare($query);
    $statement->execute(array(':id'=> $this->supervisorgroupId, ':name' => $this->supervisorgroupName));
  }

  public function delete(){
    $db = DBManager::get();
    $query = "DELETE FROM supervisor_group WHERE id = :id";
    $statement = $db->prepare($query);
    $statement->execute(array(':id'=> $this->supervisorgroupId));
    $query = "DELETE FROM supervisor_group_user WHERE supervisor_group_id = :id";
    $statement = $db->prepare($query);
    $statement->execute(array(':id'=> $this->supervisorgroupId));
  }

}
