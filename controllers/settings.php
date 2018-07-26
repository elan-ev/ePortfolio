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
    $cid = Course::findCurrent()->id;
    $this->isVorlage = Eportfoliomodel::isVorlage($cid);
    $eportfolio = Eportfoliomodel::findBySeminarId($cid);

    $seminar = new Seminar($cid);
    
    # Aktuelle Seite
    PageLayout::setTitle('ePortfolio - Zugriffsrechte: '.$seminar->getName());
    if($this->isVorlage){
        PageLayout::setTitle('ePortfolio-Vorlage - Zugriffsrechte: '.$seminar->getName());
    }

    //autonavigation
    Navigation::activateItem("course/settings");

    $sidebar = Sidebar::Get();
    $sidebar->setTitle('Navigation');

    $views = new ViewsWidget();
    $views->setTitle('Rechte');
    $views->addLink(_('Rechteverwaltung'), '#')->setActive(true);
    Sidebar::get()->addWidget($views);

    //get list chapters
    $chapters = Eportfoliomodel::getChapters($cid);

    //get viewer information
    $viewers = $seminar->getMembers('autor');

    //get supervisor_id
    $supervisor_id = $this->getSupervisorGroupOfPortfolio($cid);
    
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

      $query = "INSERT INTO seminar_user (seminar_id, user_id, status, visible) VALUES (:cid, :viewerId, 'autor', 1)";
      $statement = $db->prepare($query);
      $statement->execute(array(':viewerId'=> $viewerId, ':cid'=> $cid));

      $query = "INSERT INTO eportfolio_user (user_id, Seminar_id, eportfolio_id, status, eportfolio_access, owner) VALUES (:viewerId, :cid, :eportfolio_id, 'autor', :json, 0)";
      $statement = $db->prepare($query);
      $statement->execute(array(':viewerId'=> $viewerId, ':cid'=> $cid, ':eportfolio_id'=> $eportfolio[0]->eportfolio_id, ':json'=> $json));
    }

    //////////////////
    //////////////////
    //Änderung von Name und Beschreibung des Portfolios
    if($_POST["saveChanges"]){
      $this->saveChanges();
    }
    
     $search_obj = new SQLSearch("SELECT auth_user_md5.user_id, CONCAT(auth_user_md5.nachname, ', ', auth_user_md5.vorname, ' (' , auth_user_md5.email, ')' ) as fullname, username, perms "
                            . "FROM auth_user_md5 "
                            . "WHERE (CONCAT(auth_user_md5.Vorname, \" \", auth_user_md5.Nachname) LIKE :input "
                            . "OR CONCAT(auth_user_md5.Nachname, \" \", auth_user_md5.Vorname) LIKE :input "
                            . "OR auth_user_md5.username LIKE :input)"
                            . "AND auth_user_md5.user_id NOT IN "
                            . "(SELECT eportfolio_user.user_id FROM eportfolio_user WHERE eportfolio_user.Seminar_id = '". $cid ."')  "
                            . "ORDER BY Vorname, Nachname ",
                _("NUtzer suchen"), "username");

      $this->mp = MultiPersonSearch::get('selectFreigabeUser')
        ->setLinkText(_('Zugriffsrechte vergeben'))
        ->setTitle(_('NutzerInnen zur Verwaltung von Zugriffsrechten hinzufügen'))
        ->setSearchObject($search_obj)
        ->setExecuteURL(URLHelper::getLink('plugins.php/eportfolioplugin/settings/addZugriff/'. $cid))
        ->render();

    //push to template
    $this->cid = $cid;
    $this->userid = $userid;
    $this->title = $seminar->name;
    $this->chapterList = $chapters; //$arrayList;
    $this->viewerList = $viewers; //$return_arr;
    $this->numberChapter = count($chapters);
    $this->supervisorId = $supervisor_id;
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

  //TOTO refactoring gehört in ePortfoliomodel
  public function getSupervisorGroupOfPortfolio($id){
    $portfolio = Eportfoliomodel::findBySeminarId($id);
     if ($portfolio[0]->group_id){
        $portfoliogroup = EportfolioGroup::findbySQL('seminar_id = :id', array(':id'=> $portfolio[0]->group_id));
     } if ($portfoliogroup[0]->supervisor_group_id){
     
            return $portfoliogroup[0]->supervisor_group_id;
        } else return false;   
  }


  //addUserAccess
  public function addZugriff_action($id){
    $mp             = MultiPersonSearch::load('selectFreigabeUser');
    $seminar        = new Seminar($id);
    $eportfolio = Eportfoliomodel::findBySeminarId($id);
    $eportfolio_id  = $eportfolio[0]->eportfolio_id;
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
    $eportfolio = Eportfoliomodel::findBySeminarId($cid);
    $eportfolio_id  = $eportfolio[0]->eportfolio_id;

    # User aus Seminar entfernen
    $seminar->deleteMember($userId);

    # User aus eportfolio_user entfernen
    $db = DBManager::get();
    $query = "DELETE FROM eportfolio_user WHERE user_id = :userId AND seminar_id = :cid AND eportfolio_id = :eportfolio_id";
    $statement = $db->prepare($query);
    $statement->execute(array(':cid'=> $cid, ':userId'=> $userId, ':eportfolio_id' => $eportfolio_id));
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
  
  
  function url_for($to = '')
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
