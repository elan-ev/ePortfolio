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
    $query = $db->query("SELECT owner_id FROM eportfolio WHERE Seminar_id = '$this->eportfolioId'")->fetchAll();
    return $query[0][owner_id];
  }

  public function isEportfolio(){
    $db = DBManager::get();
    $query = $db->query("SELECT * FROM eportfolio WHERE Seminar_id = '$this->eportfolioId'")->fetchAll();
    if (!empty($query)) {
      return true;
    }
  }

  public function getId(){
    return $this->eportfolioId;
  }


}
