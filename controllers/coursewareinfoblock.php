<?php

class CoursewareinfoblockController extends StudipController {

  public function __construct($dispatcher)
  {
      parent::__construct($dispatcher);
      $this->plugin = $dispatcher->plugin;

      if ($_POST['infobox']) {
        $this->infobox($_POST["cid"], $_POST["userid"], $_POST["selected"]);
        exit();
      }

      $cid = $_GET['cid'];

  }

  public function before_filter(&$action, &$args)
  {
      parent::before_filter($action, $args);
      PageLayout::setTitle('ePortfolio');

  }


  public function index_action()
  {

  }

  public function isOwner($cid, $userId){
    $db = DBManager::get();
    $query = $db->query("SELECT owner_id FROM eportfolio WHERE Seminar_id = '$cid'")->fetchAll();
    if ($query[0][0] == $userId) {
      return true;
    }
  }

  public function infobox($cid, $owner_id, $selected){

    $infoboxArray = array();
    $db = DBManager::get();

    if ($this->isOwner($cid, $owner_id) == true) {

      $infoboxArray["owner"] = true;
      $infoboxArray["users"] = array();

      //get user list
      $query = $db->query("SELECT * FROM eportfolio_user WHERE Seminar_id = '$cid'")->fetchAll();
      foreach ($query as $key) {
        $newarray = array();
        $newarray["userid"] = $key["user_id"];
        $newarray["access"] = $key["eportfolio_access"];

        $userinfo = UserModel::getUser($key["user_id"]);
        $newarray['firstname'] = $userinfo[Vorname];
        $newarray['lastname'] = $userinfo[Nachname];

        // $userAccess = json_decode($key["eportfolio_access"]);
        // print_r($userAccess);
        $access = unserialize($newarray["access"]);

        if ($selected == 0) {
          $keys = array_keys($access);
          $selected = $keys[0];
        }

        if ($access[$selected] == 1) {
          $infoboxArray["users"][] = $newarray;
        }

      }

      $supervisorQuery  = DBManager::get()->query("SELECT supervisor_id FROM eportfolio WHERE seminar_id = '$cid'")->fetchAll();
      $supervisorId     = $supervisorQuery[0][0];

      //supervisor Infos
      if (!empty($supervisorQuery[0][0])) {

        # check Freigaben
        $query = DBManager::get()->query("SELECT freigaben_kapitel FROM eportfolio WHERE Seminar_id = '$cid'")->fetchAll();
        $freigabe = json_decode($query[0][0]);
        $freigabe = $freigabe->$selected;

        if ($freigabe == 1) {
          $supervisorInfo = UserModel::getUser($supervisorId);
          $infoboxArray["supervisorId"] = $supervisorId;
          $infoboxArray["supervisorFistname"] = $supervisorInfo[Vorname];
          $infoboxArray["supervisorLastname"] = $supervisorInfo[Nachname];
          $infoboxArray['cid']                = $cid;
        }

      }

    } else {

      //get owner Id
      $query = $db->query("SELECT owner_id FROM eportfolio WHERE Seminar_id = '$cid'")->fetchAll();
      $userId = $query[0][0];
      $supervisor = UserModel::getUser($userId);
      $infoboxArray['firstname']  = $supervisor[Vorname];
      $infoboxArray['lastname']   = $supervisor[Nachname];
      $infoboxArray['userid']     = $userId;
      $infoboxArray['cid']        = $cid;

    }

    print_r(json_encode($infoboxArray));

  }

}
