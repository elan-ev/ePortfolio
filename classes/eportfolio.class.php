<?php

class eportfolio {

  private $eportfolioId = "DEFAULT_VALUE";

  public function __construct($id) {
    $this->eportfolioId = $id;
  }

  public function isOwner($userId){
    if($this->getOwner() == $userId){
      return true;
    } else {
      return false;
    }
  }

  public function getOwner(){
    $db = DBManager::get();
    $query = "SELECT owner_id FROM eportfolio WHERE Seminar_id = :id";
    $statement = $db->prepare($query);
    $statement->execute(array(':id'=> $this->eportfolioId));
    return $statement->fetchAll()[0][owner_id];
  }

  public function isEportfolio(){
    $db = DBManager::get();
    $query = "SELECT * FROM eportfolio WHERE Seminar_id = :id";
        $statement = $db->prepare($query);
        $statement->execute(array(':id'=> $this->eportfolioId));
    if (!empty($statement->fetchAll())) {
      return true;
    }
  }

  public function getId(){
    return $this->eportfolioId;
  }


}
