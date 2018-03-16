<?php

class EportfoliopluginController extends StudipController {

  public function __construct($dispatcher)
  {
      parent::__construct($dispatcher);
      $this->plugin = $dispatcher->current_plugin;

      if ($_POST['titleChanger']) {
        $this->changeTitle();
        exit();
      }

      if ($_POST['infobox']) {
        $this->infobox($_POST["cid"], $_POST["userid"], $_POST["selected"]);
        exit();
      }

      $cid = $_GET['cid'];
      global $user;
      $eportfolio = Eportfoliomodel::findOneBySeminar_Id($cid);
      $isVorlage = Eportfoliomodel::isVorlage($cid);

      $sidebar = Sidebar::Get();
      Sidebar::Get()->setTitle('Übersicht');

      $navOverview = new LinksWidget();
      $navOverview->setTitle('Übersicht');
      $navOverview->addLink('Übersicht', URLHelper::getLink('plugins.php/eportfolioplugin/eportfolioplugin', array('portfolioid' => $portfolioid)), null , array('class' => 'active-link'));
      $sidebar->addWidget($navOverview);
      
       //Kontextaktionen
      if($eportfolio->owner_id == $user->id){
        $actions = new ActionsWidget();
        $actions->setTitle(_('Aktionen'));
        $actions->addLink('Portfolio löschen',
        URLHelper::getLink('plugins.php/eportfolioplugin/eportfolioplugin/deletePortfolio/'. $portfolioid), null, array('onclick'=> "return confirm('Sind Sie sich sicher, dass Sie das Portfolio löschen wollen? Alle Daten werden hierdurch unwiderruflich gelöscht und können nicht wiederhergestellt werden.')")); 
        Sidebar::get()->addWidget($actions);
      }
      

      $nav = new LinksWidget();
      $nav->setTitle(_('Courseware'));
      $nav->addLink($name, "");

      $sem_type_id = Config::get()->getValue('SEM_CLASS_PORTFOLIO_VORLAGE');

      $seminar = new Seminar($cid);
      $eportfolio = new eportfolio($cid);

      if ($seminar->status == $sem_type_id) {
        $this->canEdit = true;
      }

      $getCoursewareChapters = $this->getCardInfos($cid);
      foreach ($getCoursewareChapters as $key => $value) {
        if (EportfolioFreigabe::hasAccess($GLOBALS["user"]->id, $cid, $value[id]) || $isVorlage){    
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
    $eportfolio = new eportfolio($this->cid);
    $isOwner = $eportfolio->isOwner($userid);
    $this->isVorlage = Eportfoliomodel::isVorlage($this->cid);
    $seminar = new Seminar($this->cid);
    
     # Aktuelle Seite
    PageLayout::setTitle('ePortfolio - Übersicht: '. $seminar->getName());
    if($this->isVorlage){
        PageLayout::setTitle('ePortfolio-Vorlage - Übersicht: '. $seminar->getName());
    }

    $db = DBManager::get();

    //get list chapters
    $chapters = Eportfoliomodel::getChapters($cid);

    //push to template
    $this->cardInfo = $chapters; //$return_arr;
    $this->seminarTitle = $seminar->getName();
    $this->isOwner = $isOwner;
    $this->cid = $cid;
    $this->userid = $userid;

  }

  public function getCardInfos($cid){
    $db = DBManager::get();
    $return_arr = array();
    $query = "SELECT id, title FROM mooc_blocks WHERE seminar_id = :cid AND type = 'Chapter' ORDER BY position ASC";
    $statement = $db->prepare($query);
    $statement->execute(array(':cid'=> $cid));
    foreach ($statement->fetchAll() as $value) {
      $arrayOne = array();
      $arrayOne['id'] = $value[id];
      $arrayOne['title'] = $value[title];

      // get sections of chapter
      $query = "SELECT id, title FROM mooc_blocks WHERE parent_id = :id ORDER BY position ASC";
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
  
  public function deletePortfolio_action($id){
    
      
       PageLayout::postMessage(MessageBox::success(_('Das Portfolio wurde gelöscht.')));
        
       $this->redirect($this->url_for('/index'));
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

}
