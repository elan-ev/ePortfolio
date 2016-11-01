<?php

class EportfoliopluginController extends StudipController {

  public function __construct($dispatcher)
  {
      parent::__construct($dispatcher);
      $this->plugin = $dispatcher->plugin;

      $sidebar = Sidebar::Get();
      Sidebar::Get()->setTitle('Uebersicht');
      $widget = new SearchWidget();
      Sidebar::Get()->addWidget($widget);
  }

  public function before_filter(&$action, &$args)
  {
      parent::before_filter($action, $args);

      // $this->set_layout($GLOBALS['template_factory']->open('layouts/base.php'));
      PageLayout::setTitle('Uebersicht');

  }


  public function index_action()
  {

    //set AutoNavigation/////
    Navigation::activateItem("course/eportfolioplugin");
    ////////////////////////

    $userid = $GLOBALS["user"]->id;
    $cid = $_GET["cid"];
    $i = 0;
    $isOwner = false;

    $db = DBManager::get();
    $this->plugin = $dispatcher->plugin;

    // get template Status
    $templateStatus = $db->query("SELECT templateStatus FROM eportfolio WHERE Seminar_id = '$cid' ")->fetchAll();
    $t = $templateStatus[0][templateStatus];

    // get courseware parentId
    $getCourseware = $db->query("SELECT id FROM mooc_blocks WHERE type = 'Courseware' AND seminar_id = '$cid'")->fetchAll();
    $getC = $getCourseware[0][id];

    //get seninar infos
    $getSeminarInfo = $db->query("SELECT name FROM seminare WHERE Seminar_id = '$cid'")->fetchAll();
    $getS = $getSeminarInfo[0][name];

    //check if owner
    $queryIsOwner = $db->query("SELECT owner_id FROM eportfolio WHERE Seminar_id = '$cid'")->fetchAll();
    $ownerId = $queryIsOwner[0][owner_id];
    if($userid == $ownerId){
      $isOwner = true;
    }

    //auto insert chapters
    if ($t == 0) {
      //set chapter titles
      $template = array('Reflektionsimpuls 1', 'Reflektionsimpuls 2', 'Reflektionsimpuls 3', 'Reflektionsimpuls 4','Reflektionsimpuls 5', 'Reflektionsimpuls 6');

      foreach ($template as $value) {
        $db->query("INSERT INTO mooc_blocks (type, parent_id, seminar_id, title, position) VALUES ('Chapter', '$getC', '$cid', '$value', '$i')");
        $db->query("UPDATE eportfolio SET templateStatus = '1' WHERE seminar_id = '$cid'");
        $i++;
      }
    }

    //get cardinfos for overview
    $return_arr = array();
    $getCardInfos = $db->query("SELECT id, title FROM mooc_blocks WHERE seminar_id = '$cid' AND type = 'Chapter'")->fetchAll();
    foreach ($getCardInfos as $value) {
      $arrayOne = array();
      $arrayOne['id'] = $value[id];
      $arrayOne['title'] = $value[title];

      array_push($return_arr, $arrayOne);
    }

    //push to template
    $this->cardInfo = $return_arr;
    $this->seminarTitle = $getS;
    $this->isOwner = $isOwner;

  }

  // customized #url_for for plugins
  // function url_for($to)
  // {
  //     $args = func_get_args();
  //
  //     # find params
  //     $params = array();
  //     if (is_array(end($args))) {
  //         $params = array_pop($args);
  //     }
  //
  //     # urlencode all but the first argument
  //     $args = array_map('urlencode', $args);
  //     $args[0] = $to;
  //
  //     return PluginEngine::getURL($this->dispatcher->plugin, $params, join('/', $args));
  //
  // }
}
