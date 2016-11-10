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
        // $db->query("DELETE FROM eportfolio WHERE Seminar_id = '$cid'");
        // $db->query("DELETE FROM mooc_blocks WHERE Seminar_id = '$cid'");
        // $db->query("DELETE FROM seminare WHERE Seminar_id = '$cid'");
      }

    }

    ////////////////////
    ////////////////////

    //push to template
    $this->cid = $cid;
    $this->userid = $userid;
    $this->title = $getS;

  }
}
