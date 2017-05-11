<?php

class EportfoliopluginController extends StudipController {

  public function __construct($dispatcher)
  {
      parent::__construct($dispatcher);
      $this->plugin = $dispatcher->plugin;

      $cid = $_GET['cid'];

      $sidebar = Sidebar::Get();
      Sidebar::Get()->setTitle('Übersicht');

      $navOverview = new LinksWidget();
      $navOverview->setTitle('Übersicht');
      $navOverview->addLink('Übersicht', URLHelper::getLink('plugins.php/eportfolioplugin/eportfolioplugin', array('portfolioid' => $portfolioid)), null , array('class' => 'active-link'));
      $sidebar->addWidget($navOverview);

      $nav = new LinksWidget();
      $nav->setTitle(_('Courseware'));
      $nav->addLink($name, "");

      $getCoursewareChapters = $this->getCardInfos($cid);
      foreach ($getCoursewareChapters as $key => $value) {
        $nav->addLink($value[title], URLHelper::getLink('plugins.php/courseware/courseware', array('cid' => $cid, 'selected' => $value[id])));
      }

      $sidebar->addWidget($nav);

      //$navEinstellungen = new LinksWidget();
      //$navEinstellungen->setTitle('Einstellungen');
      //$navEinstellungen->addLink('Portfolioeinstellungen', URLHelper::getLink('plugins.php/eportfolioplugin/settings', array('portfolioid' => $portfolioid)));
      //$sidebar->addWidget($navEinstellungen);

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
    $cid = $_GET["cid"];
    $i = 0;
    $isOwner = false;

    $db = DBManager::get();
    $this->plugin = $dispatcher->plugin;

    // get template Status
    $templateStatus = $db->query("SELECT templateStatus FROM eportfolio WHERE Seminar_id = '$cid' ")->fetchAll();
    $t = $templateStatus[0][templateStatus];
    //echo $t;

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
      //echo  " t - triggered";

      $templateid = DBManager::get()->query("SELECT template_id FROM eportfolio WHERE Seminar_id = '$cid'")->fetchAll();
      $templateid = $templateid[0][0];
      $getChapters = DBManager::get()->query("SELECT chapters FROM eportfolio_templates WHERE id = '$templateid'")->fetchAll();
      $chapters = $getChapters[0][0];
      $chapters = json_decode($chapters, true);

      $gettempname = DBManager::get()->query("SELECT temp_name FROM eportfolio_templates WHERE id = '$templateid'")->fetchAll();
      $gettempname = $gettempname[0][0];

      //print_r($chapters);

      //set additional chapter titles
      $template = array('Reflektionsimpuls 3', 'Reflektionsimpuls 4','Reflektionsimpuls 5', 'Reflektionsimpuls 6');
      $template = $chapters;

      foreach ($template as $key => $value) {

        if($key > 1){
          //insert into eportfolio
          $db->query("INSERT INTO mooc_blocks (type, parent_id, seminar_id, title, position) VALUES ('Chapter', '$getC', '$cid', '$value', '$i')");
        }

        //update all mooc_blocks field
        $db->query("UPDATE mooc_blocks SET title = '$gettempname' WHERE type = 'Courseware'");

        //change title of standard chapters
        $db->query("UPDATE mooc_blocks SET title = '$template[0]' WHERE title = 'Kapitel 1' AND Seminar_id= '$cid'");
        $db->query("UPDATE mooc_blocks SET title = '$template[1]' WHERE title = 'Kapitel 2' AND Seminar_id= '$cid'");

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

  public function getCardInfos($cid){
    $db = DBManager::get();
    $return_arr = array();
    $getCardInfos = $db->query("SELECT id, title FROM mooc_blocks WHERE seminar_id = '$cid' AND type = 'Chapter' ORDER BY id ASC")->fetchAll();
    foreach ($getCardInfos as $value) {
      $arrayOne = array();
      $arrayOne['id'] = $value[id];
      $arrayOne['title'] = $value[title];

      // get sections of chapter
      $queryMenuPoints = $db->query("SELECT id, title FROM mooc_blocks WHERE parent_id = '$value[id]'")->fetchAll();
      $arrayOne['section'] = $queryMenuPoints;

      array_push($return_arr, $arrayOne);
    }

    //$tempid = $db->query("SELECT * FROM eportfolio WHERE Seminar_id = '$cid'")->fetchAll();
    //$tempid = $tempid[0]["template_id"];
    //$img = $db->query("SELECT * FROM eportfolio_templates WHERE id = '$tempid'")-fetchAll();
    //$img =  $img[0]["img"];
    //array_push($return_arr, $img);

    return $return_arr;
  }

  public function getImg($cid){
    $q = DBManager::get()->query("SELECT * FROM eportfolio WHERE Seminar_id = '$cid'")->fetchAll();
    $tempid = $q[0][7];
    $img = DBManager::get()->query("SELECT * FROM eportfolio_templates WHERE id = '$tempid'")->fetchAll();
    return $img[0]["img"];
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

  public function isOwner($cid, $userId){
    $db = DBManager::get();
    $query = $db->query("SELECT owner_id FROM eportfolio WHERE Seminar_id = '$cid'")->fetchAll();
    if ($query[0][0] == $userId) {
      return true;
    }
  }

}
