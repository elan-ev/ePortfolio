<?php

class settingsController extends StudipController {

  public function __construct($dispatcher)
  {
      parent::__construct($dispatcher);
      $this->plugin = $dispatcher->plugin;

  }

  public function before_filter(&$action, &$args)
  {
    parent::before_filter($action, $args);
    PageLayout::setTitle('Uebersicht');

  }

  public function index_action()
  {
    //set AutoNavigation/////
    Navigation::activateItem("course/settings");
    ////////////////////////

    // set vars
    $userid = $GLOBALS["user"]->id;
    $cid = $_GET["cid"];
    $db = DBManager::get();

    //get seninar infos
    $getSeminarInfo = $db->query("SELECT name FROM seminare WHERE Seminar_id = '$cid'")->fetchAll();
    $getS = $getSeminarInfo[0][name];

    //delete Portfolio///
    ////////////////////

    if ($_POST["deletePortfolio"]) {

      $deleteTrigger = $_POST["deletePortfolio"];

      if ($deleteTrigger == '1'){
        $db->query("DELETE FROM eportfolio WHERE Seminar_id = '$cid'");
        $db->query("DELETE FROM mooc_blocks WHERE Seminar_id = '$cid'");
        $db->query("DELETE FROM seminare WHERE Seminar_id = '$cid'");
      }

    }

    ////////////////////
    ////////////////////

    //setAccess for viewer//
    ///////////////////////

    if ($_POST["setAccess"]){

      $setAcess_block_id = $_POST["block_id"];
      $setAcess_viewer_id = $_POST["viewer_id"];
      echo $setAcess_block_id;

      $query_set_access = $db->query("SELECT eportfolio_access FROM seminar_user WHERE user_id = '$setAcess_viewer_id' AND Seminar_id = '$cid'")->fetchAll();
      $array_set_access = unserialize($query_set_access[0][eportfolio_access]);
      print_r($array_set_access);

        //set if nothing is set jet
        if (!array_key_exists($setAcess_block_id, $array_set_access[chapter])){
          $array_set_access[chapter][$setAcess_block_id] = 0;
          $array_set_access_new = serialize($array_set_access);
          $db->query("UPDATE seminar_user SET eportfolio_access = '$array_set_access_new' WHERE user_id = '$setAcess_viewer_id' AND Seminar_id = '$cid'");
        }

        if ($array_set_access[chapter][$setAcess_block_id] == 1){
          $array_set_access[chapter][$setAcess_block_id] = 0;
          $array_set_access_new = serialize($array_set_access);
          $db->query("UPDATE seminar_user SET eportfolio_access = '$array_set_access_new' WHERE user_id = '$setAcess_viewer_id' AND Seminar_id = '$cid'");
        } else {
          if ($array_set_access[chapter][$setAcess_block_id] == 0){
            $array_set_access[chapter][$setAcess_block_id] = 1;
            $array_set_access_new = serialize($array_set_access);
            $db->query("UPDATE seminar_user SET eportfolio_access = '$array_set_access_new' WHERE user_id = '$setAcess_viewer_id' AND Seminar_id = '$cid'");
          }
        }

    }

    ///////////////////////
    ///////////////////////


    //viewer controll //
    ///////////////////
    $return_arr = array();
    $arrayList = array();
    $countChapter = 0;

    //get list chapters
    $chapterList = $db->query("SELECT * FROM mooc_blocks WHERE type = 'Chapter' AND seminar_id = '$cid'")->fetchAll();
    foreach ($chapterList as $key) {
      array_push($arrayList, array('title' => $key[title], 'id' => $key[id]));
      $countChapter++;
    }

    //get viewer information
    $getPortfolioViewer = $db->query("SELECT * FROM seminar_user WHERE Seminar_id = '$cid' AND status != 'dozent'")->fetchAll();
    foreach ($getPortfolioViewer as $key) {

      $viewer_id =  $key[user_id];
      $viewerInfo = $db->query("SELECT Vorname, Nachname FROM auth_user_md5 WHERE user_id = '$viewer_id'")->fetchAll();
      $viewerVorname = $viewerInfo[0][Vorname];
      $viewerNachname = $viewerInfo[0][Nachname];

      $viewerAccess = $db->query("SELECT eportfolio_access FROM seminar_user WHERE user_id = '$viewer_id' AND Seminar_id = '$cid'")->fetchAll();
      $dataAccess = unserialize($viewerAccess[0][eportfolio_access]);

      $arrayOne = array();
      $arrayOne['Vorname'] = $viewerVorname;
      $arrayOne['Nachname'] = $viewerNachname;
      $arrayOne['viewer_id'] = $viewer_id;
      $arrayOne['Chapter'] = $dataAccess[chapter];

      array_push($return_arr, $arrayOne);

    }

    ///////////////////
    //////////////////

    //get current supervisor//
    //////////////////////////

    //set supervisor array
    $supervisorInfo = array();

    //get supervisor_id
    $getSupervisorquery = $db->query("SELECT supervisor_id FROM eportfolio WHERE Seminar_id = '$cid'")->fetchAll();
    $supervisor_id = $getSupervisorquery[0][supervisor_id];

    //get info of person
    $getSupervisiorInfo = $db->query("SELECT Vorname, Nachname, Email FROM auth_user_md5 WHERE user_id = '$supervisor_id'")->fetchAll();

    //push info to array
    $supervisorInfo[Vorname] = $getSupervisiorInfo[0][Vorname];
    $supervisorInfo[Nachname] = $getSupervisiorInfo[0][Nachname];
    $supervisorInfo[Email] = $getSupervisiorInfo[0][Email];

    //////////////////////////
    //////////////////////////

    //get Portfolio Information//
    ////////////////////////////

    $queryPortfolioInfo = $db->query("SELECT Name, Beschreibung FROM seminare WHERE Seminar_id = '$cid'")->fetchAll();
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
      $db->query("UPDATE eportfolio SET supervisor_id = '$supervisorId' WHERE Seminar_id = '$cid'");
      $db->query("INSERT INTO seminar_user (seminar_id, user_id, status, visible, eportfolio_access) VALUES ('$cid', '$supervisorId', 'dozent', 1, '$access_array_serialize')");
    }

    //////////////////
    //////////////////

    //set Viewer//
    //////////////////

    if($_POST["setViewer"]){
      $viewerId = $_POST["viewerId"];
      $access_array_serialize = serialize($access_array);

      $db->query("INSERT INTO seminar_user (seminar_id, user_id, status, visible) VALUES ('$cid', '$viewerId', 'autor', 1)");
    }

    //////////////////
    //////////////////

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
    $this->supervisorInfo = $supervisorInfo;
    $this->portfolioInfo = $portfolioInfo;
  }

  public function saveChanges(){
    $db = DBManager::get();
    $cid = $_GET["cid"];
    $change_name = $_POST['Name'];
    $change_beschreibung = $_POST['Beschreibung'];
    $db->query("UPDATE seminare SET Name = '$change_name', Beschreibung = '$change_beschreibung' WHERE Seminar_id = '$cid'");
  }

}
