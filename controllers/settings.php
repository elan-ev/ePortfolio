<?php

class settingsController extends StudipController {

  public function __construct($dispatcher)
  {
      parent::__construct($dispatcher);
      $this->plugin = $dispatcher->current_plugin;

      $portfolioid = $_GET['portfolioid'];

      $cid = $_GET['cid'];


      if ($_POST['action'] == 'deleteUserAccess') {
        $this->deleteUserAccess($_POST['userId'], $_POST['seminar_id']);
        exit();
      }

      if ($_POST['action'] == 'deletePortfolio') {
        $this->deletePortfolio($_POST['cid']);
        exit();
      }

      if ($_POST['action'] == 'setsettingsColor') {
        $this->setsettingsColor($_POST['cid'], $_POST['color']);
        exit();
      }

  }

  public function before_filter(&$action, &$args)
  {
    parent::before_filter($action, $args);
  }

  public function index_action($cid = NULL)
  {

    // set vars
    $userid = $GLOBALS["user"]->id;
    $cid = $_GET["cid"]?  $_GET["cid"] : $cid;
    $db = DBManager::get();
    $this->cid = $cid;

    # Aktuelle Seite
    $seminar = new Seminar($_GET["cid"]);
    PageLayout::setTitle('ePortfolio - Zugriffsrechte: '.$seminar->getName());

    //autonavigation
    Navigation::activateItem("course/settings");

    $sidebar = Sidebar::Get();
    $sidebar->setTitle('Navigation');

    $views = new ViewsWidget();
    $views->setTitle('Rechte');
    $views->addLink(_('Rechteverwaltung'), '#')->setActive(true);
    Sidebar::get()->addWidget($views);

    # Überprüft ob Besitzer der Veranstaltung
    // if (!$this->checkIfOwner($userId, $cid) == true) {
    //   exit("Sie haben keine Berechtigung!");
    // }

    //set AutoNavigation////
    //Navigation::activateItem("course/settings");
    ////////////////////////s

    //get seninar infos
    $query = "SELECT name FROM seminare WHERE Seminar_id = :cid";
    $statement = $db->prepare($query);
    $statement->execute(array(':cid'=> $cid));
    $getS = $statement->fetchAll()[0][name];


    //viewer controll //
    ///////////////////
    $return_arr = array();
    $arrayList = array();
    $countChapter = 0;

    //get list chapters
    $query = "SELECT * FROM mooc_blocks WHERE type = 'Chapter' AND seminar_id = :cid";
    $statement = $db->prepare($query);
    $statement->execute(array(':cid'=> $cid));
    foreach ($statement->fetchAll() as $key) {
      array_push($arrayList, array('title' => $key[title], 'id' => $key[id]));
      $countChapter++;
    }

    //get viewer information
    $query = "SELECT * FROM seminar_user WHERE Seminar_id = :cid AND status != 'dozent'";
    $statement = $db->prepare($query);
    $statement->execute(array(':cid'=> $cid));
    foreach ($statement->fetchAll() as $key) {

      $viewer_id =  $key[user_id];
      $query = "SELECT Vorname, Nachname FROM auth_user_md5 WHERE user_id = :viewer_id";
      $statement = $db->prepare($query);
      $statement->execute(array(':viewer_id'=> $viewer_id));
      $viewerInfo = $statement->fetchAll();
      $viewerVorname = $viewerInfo[0][Vorname];
      $viewerNachname = $viewerInfo[0][Nachname];

      //$viewerAccess = $db->query("SELECT eportfolio_access FROM seminar_user WHERE user_id = '$viewer_id' AND Seminar_id = '$cid'")->fetchAll();
      //$dataAccess = unserialize($viewerAccess[0][eportfolio_access]);

      $arrayOne = array();
      $arrayOne['Vorname'] = $viewerVorname;
      $arrayOne['Nachname'] = $viewerNachname;
      $arrayOne['viewer_id'] = $viewer_id;
      //$arrayOne['Chapter'] = $dataAccess[chapter];

      array_push($return_arr, $arrayOne);

    }

    //get supervisor_id
    $supervisor_id = $this->getSupervisorGroupOfPortfolio($cid);
    //get Portfolio Information//
    ////////////////////////////

    $query = "SELECT Name, Beschreibung FROM seminare WHERE Seminar_id = :cid";
    $statement = $db->prepare($query);
    $statement->execute(array(':cid'=> $cid));
    $queryPortfolioInfo = $statement->fetchAll();
    $portfolioInfo = array('Name' => $queryPortfolioInfo[0][Name], 'Beschreibung' => $queryPortfolioInfo[0][Beschreibung]);

    ////////////////////////////
    ////////////////////////////

    //set Supervisor//
    //////////////////

    if($_POST["setSupervisor"]){
      $supervisorId = $_POST["supervisorId"];
      $access_array = array('viewer' => 0);
      $access_array_serialize = serialize($access_array);

      //set supervisor_id in eportfolio
      $query = "UPDATE eportfolio SET supervisor_id = :supervisorId WHERE Seminar_id = :cid";
      $statement = $db->prepare($query);
      $statement->execute(array(':supervisorId'=> $supervisorId, ':cid'=> $cid));
      $query = "INSERT INTO seminar_user (seminar_id, user_id, status, visible, eportfolio_access) VALUES (:cid, :supervisorId, 'dozent', 1, :access_array_serialize)";
      $statement = $db->prepare($query);
      $statement->execute(array(':supervisorId'=> $supervisorId, ':cid'=> $cid, ':access_array_serialize'=> $access_array_serialize));
    }

    //////////////////
    //////////////////

    //set Viewer//
    //////////////////

    if($_POST["setViewer"]){
      $viewerId = $_POST["viewerId"];
      $access_array_serialize = serialize($access_array);
      $eportfolio_access = array();

      $list = $this->getCurrentChapter($cid);

      foreach ($list as $key => $value) {
        $eportfolio_access[$value] = 1;
      }

      $json = serialize($eportfolio_access);

      $eportfolio_id = $this->getEportfolioId($cid);

      $query = "INSERT INTO seminar_user (seminar_id, user_id, status, visible) VALUES (:cid, :viewerId, 'autor', 1)";
      $statement = $db->prepare($query);
      $statement->execute(array(':viewerId'=> $viewerId, ':cid'=> $cid));

      $query = "INSERT INTO eportfolio_user (user_id, Seminar_id, eportfolio_id, status, eportfolio_access, owner) VALUES (:viewerId, :cid, :eportfolio_id, 'autor', :json, 0)";
      $statement = $db->prepare($query);
      $statement->execute(array(':viewerId'=> $viewerId, ':cid'=> $cid, ':eportfolio_id'=> $eportfolio_id, ':json'=> $json));
    }

    //////////////////
    //////////////////
    //Änderung von Name und Beschreibung des Portfolios
    if($_POST["saveChanges"]){
      $this->saveChanges();
    }

    //push to template
    $this->cid = $cid;
    $this->userid = $userid;
    $this->title = $getS;
    $this->chapterList = $arrayList;
    $this->viewerList = $return_arr;
    $this->numberChapter = $countChapter;
    $this->supervisorId = $supervisor_id;
    $this->portfolioInfo = $portfolioInfo;
    $this->access = $access;

  }
  
  public function setAccess_action($user_id, $seminar_id, $chapter_id, $status)
  {
      $freigabe= new EportfolioFreigabe();
      $freigabe::setAccess($user_id, $seminar_id, $chapter_id, $status);
      $this->render_nothing();
  }

  //name und beschreibung speichern
  public function saveChanges(){
    $db = DBManager::get();
    $cid = $_GET["cid"];
    $change_name = $_POST['Name'];
    $change_beschreibung = $_POST['Beschreibung'];
    $query = "UPDATE seminare SET Name = :change_name, Beschreibung = :change_beschreibung WHERE Seminar_id = :cid";
    $statement = $db->prepare($query);
    $statement->execute(array(':change_name'=> $change_name, ':cid'=> $cid, ':change_beschreibung' => $change_beschreibung));
  }

  public function getCurrentChapter($id){
    $db = DBManager::get();
    $arrayChapter = array();
    $query = "SELECT id FROM mooc_blocks WHERE seminar_id = :id AND type = 'Chapter'";
    $statement = $db->prepare($query);
    $statement->execute(array(':id'=> $id));
    foreach($statement->fetchAll() as $key){
      array_push($arrayChapter, $key[id]);
    }

    return $arrayChapter;
  }

  public function getEportfolioId($id){
    $db = DBManager::get();
    $query = "SELECT eportfolio_id FROM eportfolio WHERE Seminar_id = :id";
    $statement = $db->prepare($query);
    $statement->execute(array(':id'=> $id));
    return $statement->fetchAll()[0][eportfolio_id];
  }

  //TODO user
  public function getEportfolioAccess($id, $sid){
    $db = DBManager::get();
    $query = "SELECT eportfolio_access FROM eportfolio_user WHERE user_id = :id AND Seminar_id = :sid";
    $statement = $db->prepare($query);
    $statement->execute(array(':id'=> $id, ':sid'=> $sid));
    return unserialize($statement->fetchAll()[0][eportfolio_access]);
  }

    //TODO user
  public function saveEportfolioAccess($id, $data, $sid){
    $pushArray = serialize($data);
    echo $pushArray; //TODO raus damit?
    $db = DBManager::get();
    $query = "UPDATE eportfolio_user SET eportfolio_access = :pushArray WHERE user_id = :id AND Seminar_id = :sid";
    $statement = $db->prepare($query);
    $statement->execute(array(':id'=> $id, ':sid'=> $sid, ':pushArray' => $pushArray));
  }

  public function getSupervisorGroupOfPortfolio($id){
    $portfolio = Eportfoliomodel::findBySQL('seminar_id = :id', array(':id'=> $id));
     if ($portfolio[0]->group_id){
        $portfoliogroup = EportfolioGroup::findbySQL('seminar_id = :id', array(':id'=> $portfolio[0]->group_id));
     } if ($portfoliogroup[0]->supervisor_group_id){
     
            return $portfoliogroup[0]->supervisor_group_id;
        } else return false;   
  }

  //todo
  public function getPortfolioFreigaben($id){
    $db = DBManager::get();
    $query = "SELECT freigaben_kapitel FROM eportfolio WHERE Seminar_id = :id";
    $statement = $db->prepare($query);
    $statement->execute(array(':id'=> $id));
    $query = json_decode($statement->fetchAll()[0][0], true);
    return $query;
  }

  //addUserAccess
  public function addZugriff_action($id){
    $mp             = MultiPersonSearch::load('eindeutige_id');
    $seminar        = new Seminar($id);
    $eportfolio_id  = $this->getEportfolioId($id);
    $userRole       = 'autor';

    # User der Gruppe hinzufügen
    foreach ($mp->getAddedUsers() as $userId) {

      #Seminar Add Member
      $seminar->addMember($userId, $userRole);

      # User der Tabelle eportfolio_user hinzufügen
      $db = DBManager::get();
      $query = "INSERT INTO eportfolio_user (user_id, Seminar_id, eportfolio_id, status, owner) VALUES (:userId, :id, :eportfolio_id, 'autor', 0)";
      $statement = $db->prepare($query);
      $statement->execute(array(':id'=> $id, ':userId'=> $userId, ':eportfolio_id' => $eportfolio_id));
    }

    $this->redirect($this->url_for('settings/index/'. $id));
    # Seminar speichern
    //$seminar->store();

  }

  public function deleteUserAccess($userId, $cid){
    $seminar        = new Seminar($cid);
    $eportfolio_id  = $this->getEportfolioId($cid);

    # User aus Seminar entfernen
    $seminar->deleteMember($userId);

    # User aus eportfolio_user entfernen
    $db = DBManager::get();
    $query = "DELETE FROM eportfolio_user WHERE user_id = :userId AND seminar_id = :cid AND eportfolio_id = :eportfolio_id";
    $statement = $db->prepare($query);
    $statement->execute(array(':cid'=> $cid, ':userId'=> $userId, ':eportfolio_id' => $eportfolio_id));
  }

  public function deletePortfolio($cid){
    $seminar = new Seminar($cid);
    $seminar->delete();

    # Seminar aus eportfolio-tabllen löschen
    $db = DBManager::get();
    $query = "DELETE FROM eportfolio WHERE seminar_id = :cid";
    $statement = $db->prepare($query);
    $statement->execute(array(':cid'=> $cid));
    $query = "DELETE FROM eportfolio_user WHERE seminar_id = :cid";
    $statement = $db->prepare($query);
    $statement->execute(array(':cid'=> $cid));
  }

  public function setsettingsColor($cid, $color){
    $newArray = array();
    //$setting = DBManager::get()->query("SELECT settings FROM eportfolio WHERE semniar_id = $cid;")->fetchAll();
    $newArray['color'] = $color;
    $newArray = json_encode($newArray);
    $db = DBManager::get();
    $query = "UPDATE eportfolio SET settings = :newArray WHERE seminar_id = :cid";
    $statement = $db->prepare($query);
    $statement->execute(array(':cid'=> $cid, ':newArray'=> $newArray));
  }

  public function getsettingsColor(){
    $cid = $_GET['cid'];
    $db = DBManager::get();
    $query = "SELECT settings FROM eportfolio WHERE seminar_id = :cid";
    $statement = $db->prepare($query);
    $statement->execute(array(':cid'=> $cid));
    $color = json_decode($statement->fetchAll()[0][0]);
    return $color->color;
  }

  public function eigenesPortfolio($cid){
    $db = DBManager::get();
    $query = "SELECT template_id FROM eportfolio WHERE seminar_id = :cid";
    $statement = $db->prepare($query);
    $statement->execute(array(':cid'=> $cid));
    if (empty($statement->fetchAll()[0][0])) {
      return true;
    } else {
      return false;
    }
  }

  public function checkIfOwner($userId, $cid){
    $db = DBManager::get();
    $query = "SELECT status FROM seminar_user WHERE user_id = :userId AND seminar_id = :cid";
    $statement = $db->prepare($query);
    $statement->execute(array(':cid'=> $cid, ':userId'=> $userId));
    if ($statement->fetchAll()[0][0] == "dozent") {
      return true;
    } else {
      return false;
    }
  }
  
  
  function url_for($to)
    {
        $args = func_get_args();

        # find params
        $params = array();
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        # urlencode all but the first argument
        $args = array_map('urlencode', $args);
        $args[0] = $to;

        return PluginEngine::getURL($this->dispatcher->current_plugin, $params, join('/', $args));
    } 

}
