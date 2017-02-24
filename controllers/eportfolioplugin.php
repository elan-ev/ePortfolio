<?php

class EportfoliopluginController extends StudipController {

  public function __construct($dispatcher)
  {
      parent::__construct($dispatcher);
      $this->plugin = $dispatcher->plugin;

      $sidebar = Sidebar::Get();
      Sidebar::Get()->setTitle('Uebersicht');

      $navOverview = new LinksWidget();
      $navOverview->setTitle('Uebersicht');
      $navOverview->addLink('Ubersicht', URLHelper::getLink('plugins.php/eportfolioplugin/eportfolioplugin', array('portfolioid' => '$portfolioid')), null , array('class' => 'active-link'));
      $sidebar->addWidget($navOverview);

      $nav = new LinksWidget();
      $nav->setTitle(_('Reflektionsimpulse'));
      $nav->addLink($name, "");

      $sidebar->addWidget($nav);
      $nav->addLink('Reflektionsimpuls 1', "1");
      $nav->addLink('Reflektionsimpuls 2', "2");
      $nav->addLink('Reflektionsimpuls 3', "3");
      $nav->addLink('Reflektionsimpuls 4', "4");
      $nav->addLink('Reflektionsimpuls 5', "5");
      $nav->addLink('Reflektionsimpuls 6', "6");

      $navEinstellungen = new LinksWidget();
      $navEinstellungen->setTitle('Einstellungen');
      $navEinstellungen->addLink('Portfolioeinstellungen', URLHelper::getLink('plugins.php/eportfolioplugin/settings', array('portfolioid' => '$portfolioid')));
      $sidebar->addWidget($navEinstellungen);

      $navPersonen = new LinksWidget();
      $navPersonen->setTitle('Teilnehmer');
      $navPersonen->addLink('Supervisoren', "1");
      $navPersonen->addLink('Zuschauer', "2");
      $sidebar->addWidget($navPersonen);


      // Sidebar - not in use
      // $sidebar = Sidebar::Get();
      // Sidebar::Get()->setTitle('Uebersicht');
      // $widget = new SearchWidget();
      // Sidebar::Get()->addWidget($widget);
      //print_r($this->getCardInfos());
  }

  public function before_filter(&$action, &$args)
  {
      parent::before_filter($action, $args);
      PageLayout::setTitle('ePortfolio');

  }


  public function index_action()
  {

    //set AutoNavigation/////
    Navigation::activateItem("course/eportfolioplugin");
    ////////////////////////

    $userid = $GLOBALS["user"]->id;
    $cid = $_GET["portfolioid"];
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
      echo  " t - triggered";
      //set additional chapter titles
      $template = array('Reflektionsimpuls 3', 'Reflektionsimpuls 4','Reflektionsimpuls 5', 'Reflektionsimpuls 6');

      foreach ($template as $value) {
        //insert into eportfolio
        $db->query("INSERT INTO mooc_blocks (type, parent_id, seminar_id, title, position) VALUES ('Chapter', '$getC', '$cid', '$value', '$i')");

        //update all mooc_blocks field
        $db->query("UPDATE mooc_blocks SET title = 'Reflektionsimpulse' WHERE type = 'Courseware'");

        //change title of standard chapters
        $db->query("UPDATE mooc_blocks SET title = 'Reflektionsimpuls 1' WHERE title = 'Kapitel 1' AND Seminar_id= '$cid'");
        $db->query("UPDATE mooc_blocks SET title = 'Reflektionsimpuls 2' WHERE title = 'Kapitel 2' AND Seminar_id= '$cid'");

        //change templateStatus
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

      // get sections of chapter
      $queryMenuPoints = $db->query("SELECT id, title FROM mooc_blocks WHERE parent_id = '$value[id]'")->fetchAll();
      $arrayOne['section'] = $queryMenuPoints;

      array_push($return_arr, $arrayOne);
    }

    //get list chapters
    $chapterListArray = array();
    $chapterList = $db->query("SELECT * FROM mooc_blocks WHERE type = 'Chapter' AND seminar_id = '$cid'")->fetchAll();
    foreach ($chapterList as $key) {
      $chapterListArray[$key[0]] = array("number" => 0, "user" => array());
    }

    //get views of chapter
    $querygetviewer = $db->query("SELECT eportfolio_access, user_id FROM seminar_user WHERE Seminar_id = '$cid'")->fetchAll();
    foreach ($querygetviewer as $key) {
      $getviewerList = unserialize($key[0]);
      foreach ($getviewerList[chapter] as $val => $value) {
        if($value == '1'){
          $chapterListArray[$val][number]++;
          array_push($chapterListArray[$val][user], $key[user_id]);
        }
      }
    }

    //get viewer
    $viewerList = array();
    $viewerCounter = 0;
    $viewerListquery = $db->query("SELECT user_id FROM seminar_user WHERE Seminar_id = '$cid'")->fetchAll();
    foreach ($viewerListquery as $key){
      array_push($viewerList, $key[user_id]);
      $viewerCounter++;
    }

    //push to template
    $this->cardInfo = $return_arr;
    $this->seminarTitle = $getS;
    $this->isOwner = $isOwner;
    $this->cid = $cid;
    $this->viewerList = $viewerList;
    $this->viewerCounter = $viewerCounter;
    $this->numChapterViewer = $chapterListArray;
    $this->userid = $userid;

  }

  public function getCardInfos(){
    $db = DBManager::get();
    $return_arr = array();
    $getCardInfos = $db->query("SELECT id, title FROM mooc_blocks WHERE seminar_id = '$cid' AND type = 'Chapter'")->fetchAll();
    foreach ($getCardInfos as $value) {
      $arrayOne = array();
      $arrayOne['id'] = $value[id];
      $arrayOne['title'] = $value[title];

      // get sections of chapter
      $queryMenuPoints = $db->query("SELECT id, title FROM mooc_blocks WHERE parent_id = '$value[id]'")->fetchAll();
      $arrayOne['section'] = $queryMenuPoints;

      array_push($return_arr, $arrayOne);
    }

    return $return_arr;
  }

  public function getChapterViewer($nummer, $chapter){
    $db = DBManager::get();
    $query = $db->query("SELECT eportfolio_access, user_id FROM eportfolio_user WHERE Seminar_id = '$nummer' AND owner = '0' ")->fetchAll();
    $viewer = array();

    foreach ($query as $key) {
      $array = unserialize($key[eportfolio_access]);
      if( $array[$chapter] > 0 ){
        array_push($viewer, $key[user_id]);
      }
      $counter++;
    }

    return $viewer;

  }

}
