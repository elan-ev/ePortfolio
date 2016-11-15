<?php

class settingsController extends StudipController {

  public function __construct($dispatcher)
  {
      parent::__construct($dispatcher);
      $this->plugin = $dispatcher->plugin;

      // Sidebar - not in use
      // $sidebar = Sidebar::Get();
      // Sidebar::Get()->setTitle('Uebersicht');
      // $widget = new SearchWidget();
      // Sidebar::Get()->addWidget($widget);
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

    //push to template
    $this->cid = $cid;
    $this->userid = $userid;
    $this->title = $getS;
    $this->chapterList = $arrayList;
    $this->viewerList = $return_arr;
    $this->numberChapter = $countChapter;

  }
}
