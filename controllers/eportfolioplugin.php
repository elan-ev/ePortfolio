<?php

class EportfoliopluginController extends StudipController {

  public function __construct($dispatcher)
  {
      parent::__construct($dispatcher);
      $this->plugin = $dispatcher->plugin;

      if ($_POST['titleChanger']) {
        $this->changeTitle();
        exit();
      }

      if ($_POST['infobox']) {
        $this->infobox($_POST["cid"], $_POST["userid"], $_POST["selected"]);
        exit();
      }

      $cid = $_GET['cid'];

      $sidebar = Sidebar::Get();
      Sidebar::Get()->setTitle('ï¿½bersicht');

      $navOverview = new LinksWidget();
      $navOverview->setTitle('ï¿½bersicht');
      $navOverview->addLink('ï¿½bersicht', URLHelper::getLink('plugins.php/eportfolioplugin/eportfolioplugin', array('portfolioid' => $portfolioid)), null , array('class' => 'active-link'));
      $sidebar->addWidget($navOverview);

      $nav = new LinksWidget();
      $nav->setTitle(_('Courseware'));
      $nav->addLink($name, "");

      foreach ($GLOBALS['SEM_TYPE'] as $id => $sem_type){ //get the id of ePortfolio Seminarclass
        if ($sem_type['name'] == 'Portfolio - Vorlage') {
          $sem_type_id = $id;
        }
      }

      $seminar = new Seminar($cid);
      $eportfolio = new eportfolio($cid);

      if ($seminar->status == $sem_type_id) {
        $this->canEdit = true;
      }

      $getCoursewareChapters = $this->getCardInfos($cid);
      foreach ($getCoursewareChapters as $key => $value) {
        $isOwner = $eportfolio->isOwner($GLOBALS["user"]->id);
        if ($this->checkPersmissionOfChapter($value[id], $GLOBALS["user"]->id, $cid) == true && $isOwner == NULL) {
          $nav->addLink($value[title], URLHelper::getLink('plugins.php/courseware/courseware', array('cid' => $cid, 'selected' => $value[id])));
        } else {
          $nav->addLink($value[title], URLHelper::getLink('plugins.php/courseware/courseware', array('cid' => $cid, 'selected' => $value[id])));
        }
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

  }


  public function index_action()
  {

    //set AutoNavigation/////
    Navigation::activateItem("course/eportfolioplugin");
    ////////////////////////

    $userid = $GLOBALS["user"]->id;
    $cid = $_GET["cid"];
    $this->cid = $cid;
    $this->userId = $userid;
    $i = 0;
    $eportfolio = new eportfolio($cid);
    $isOwner = $eportfolio->isOwner($userid);

    $db = DBManager::get();
    $this->plugin = $dispatcher->plugin;

    // get template Status
    //$templateStatus = $db->query("SELECT templateStatus FROM eportfolio WHERE Seminar_id = '$cid' ")->fetchAll();
    //$t = $templateStatus[0][templateStatus];
    //echo $t;

    // get courseware parentId
    $query = "SELECT id FROM mooc_blocks WHERE type = 'Courseware' AND seminar_id = :cid";
    $statement = $db->prepare($query);
    $statement->execute(array(':cid'=> $cid));
    $getC = $statement->fetchAll()[0][id];

    //get seninar infos
    $query = "SELECT name FROM seminare WHERE Seminar_id = :cid";
    $statement = $db->prepare($query);
    $statement->execute(array(':cid'=> $cid));
    $getS = $statement->fetchAll()[0][name];

    //auto insert chapters
    /**
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
     *
     */

    //get cardinfos for overview
    $return_arr = array();
    $query = "SELECT id, title FROM mooc_blocks WHERE seminar_id = :cid AND type = 'Chapter'";
    $statement = $db->prepare($query);
    $statement->execute(array(':cid'=> $cid));
 
    foreach ($statement->fetchAll() as $value) {
      $arrayOne = array();
      $arrayOne['id'] = $value[id];
      $arrayOne['title'] = $value[title];

      // get sections of chapter
      $query = "SELECT id, title FROM mooc_blocks WHERE parent_id = :id";
      $statement = $db->prepare($query);
      $statement->execute(array(':id'=> $value[id]));
      $arrayOne['section'] = $statement->fetchAll();

      array_push($return_arr, $arrayOne);
    }

    //get list chapters
    $chapterListArray = array();
    $query = "SELECT * FROM mooc_blocks WHERE type = 'Chapter' AND seminar_id = :cid";
    $statement = $db->prepare($query);
    $statement->execute(array(':cid'=> $cid));
    foreach ($statement->fetchAll() as $key) {
      $chapterListArray[$key[0]] = array("number" => 0, "user" => array());
    }

    //get views of chapter
    //$querygetviewer = $db->query("SELECT eportfolio_access, user_id FROM seminar_user WHERE Seminar_id = '$cid'")->fetchAll();
    //foreach ($querygetviewer as $key) {
    //  $getviewerList = unserialize($key[0]);
    //  foreach ($getviewerList[chapter] as $val => $value) {
    //    if($value == '1'){
    //      $chapterListArray[$val][number]++;
    //      array_push($chapterListArray[$val][user], $key[user_id]);
    //    }
    //  }
    //}

    //get viewer
    $viewerList = array();
    $viewerCounter = 0;
    
    $query = "SELECT user_id FROM seminar_user WHERE Seminar_id = :cid";
    $statement = $db->prepare($query);
    $statement->execute(array(':cid'=> $cid));
    foreach ($statement->fetchAll() as $key){
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

    # Aktuelle Seite
    PageLayout::setTitle('ePortfolio - Übersicht: '.$getS);

  }

  public function getCardInfos($cid){
    $db = DBManager::get();
    $return_arr = array();
    $query = "SELECT id, title FROM mooc_blocks WHERE seminar_id = :cid AND type = 'Chapter' ORDER BY id ASC";
    $statement = $db->prepare($query);
    $statement->execute(array(':cid'=> $cid));
    foreach ($statement->fetchAll() as $value) {
      $arrayOne = array();
      $arrayOne['id'] = $value[id];
      $arrayOne['title'] = $value[title];

      // get sections of chapter
      $query = "SELECT id, title FROM mooc_blocks WHERE parent_id = :id";
      $statement = $db->prepare($query);
      $statement->execute(array(':id'=> $value[id]));
      $arrayOne['section'] = $statement->fetchAll();

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
    //$q = DBManager::get()->query("SELECT * FROM eportfolio WHERE Seminar_id = '$cid'")->fetchAll();
    //$tempid = $q[0][7];
    //$img = DBManager::get()->query("SELECT * FROM eportfolio_templates WHERE id = '$tempid'")->fetchAll();
    //return $img[0]["img"];
  }

  public function getChapterViewer($nummer, $chapter){
    $db = DBManager::get();
    $query = "SELECT eportfolio_access, user_id FROM eportfolio_user WHERE Seminar_id = :nummer AND owner = '0'";
    $statement = $db->prepare($query);
    $statement->execute(array(':nummer'=> $nummer));
    $viewer = array();

    foreach ($statement->fetchAll() as $key) {
      $array = unserialize($key[eportfolio_access]);
      if( $array[$chapter] > 0 ){
        array_push($viewer, $key[user_id]);
      }
      $counter++;
    }

    return $viewer;

  }

  public function checkIfTemplate($id){
    $db = DBManager::get();
    $query = "SELECT template_id FROM eportfolio WHERE seminar_id = :id";
    $statement = $db->prepare($query);
    $statement->execute(array(':id'=> $id));
    return $statement->fetchAll()[0][0];
  }

  public function changeTitle(){
    $title      = studip_utf8decode(strip_tags($_POST['title']));
    $cid        = $_POST['cid'];

    $sem        = new Seminar($cid);
    $sem->name  = $title;
    $sem->store();
  }

  public function infobox($cid, $owner_id, $selected){

    $infoboxArray = array();
    $db = DBManager::get();

    if ($this->isOwner($cid, $owner_id) == true) {

      $infoboxArray["owner"] = true;
      $infoboxArray["users"] = array();

      //get user list
      $query = "SELECT * FROM eportfolio_user WHERE Seminar_id = :cid";
      $statement = $db->prepare($query);
      $statement->execute(array(':cid'=> $cid));
      foreach ($statement->fetchAll() as $key) {
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

    } else {

      //get owner Id
      $query = "SELECT owner_id FROM eportfolio WHERE Seminar_id = :cid";
      $statement = $db->prepare($query);
      $statement->execute(array(':cid'=> $cid));
      $userId = $statement->fetchAll()[0][0];
      $supervisor = UserModel::getUser($userId);
      $infoboxArray['firstname'] = $supervisor[Vorname];
      $infoboxArray['lastname'] = $supervisor[Nachname];

    }

    print_r(json_encode($infoboxArray));

  }

  public function checkPersmissionOfChapter($chapter, $userId, $cid){
    $db = DBManager::get();
    $query = "SELECT eportfolio_access FROM eportfolio_user WHERE user_id = :user_id AND seminar_id = :cid";
    $statement = $db->prepare($query);
    $statement->execute(array(':user_id'=> $userId, ':cid'=> $cid));
    $data = unserialize($statement->fetchAll()[0][0]);
    if ($data[$chapter] == 1) {
      return true;
    } else {
      return false;
    }
  }

}
