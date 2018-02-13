<?php

use Mooc\Export\XmlExport;
use Mooc\Import\XmlImport;
use Mooc\Container;
use Mooc\UI\Courseware\Courseware;

# require_once 'plugins_packages/virtUOS/Courseware/controllers/exportportfolio.php';

class ShowsupervisorController extends StudipController {

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;
        $user = get_username();
        $id = $_GET["id"];
        $this->id = $id;

        $groupid = $id;
        $this->groupid = $_GET["id"];
        $this->userid = $GLOBALS["user"]->id;
        $this->ownerid = $GLOBALS["user"]->id;

        $this->groupTemplates = Group::getTemplates($id);
        $this->templistid = $this->groupTemplates;

        //userData for Modal

        if($_GET["create"]){
          Group::create($_POST["ownerid"], $_POST["name"], $_POST["description"]);
          exit();
        }

        if($_POST["type"] == 'addTemp'){
          $this->addTempToDB();
          exit();
        }

        if($_POST["type"] == 'delete'){
          $this->deletePortfolio();
          exit();
        }

        if($_POST["type"] == 'addTemplateTest'){
          $this->addTemplateTest();
          exit();
        }

        if($_POST["type"] == 'getGroupMember'){
          $this->getGroupMemberAjax($_POST['id']);
          exit();
        }

        if ($_GET["action"] == 'addUsersToGroup') {
          $this->addUsersToGroup();
        }

        if ($_POST["action"] == 'deleteUserFromGroup') {
          Group::deleteUser($_POST['userId'], $_POST["seminar_id"]);
          exit();
        }

        # Aktuelle Seite
        PageLayout::setTitle('ePortfolio - Supervisionsansicht');

        //sidebar
        $sidebar = Sidebar::Get();
        Sidebar::Get()->setTitle('Supervisionsansicht');

        $nav = new LinksWidget();
        $nav->setTitle(_('Supervisionsgrupppen'));
        $groups = $this->getGroups($GLOBALS["user"]->id);
        foreach ($groups as $key) {
          $seminar = new Seminar($key);
          $name = $seminar->getName();
          if($_GET['id'] == $key){
            $attr = array('class' => 'active-link');
          } else {
            $attr = array('class' => '');
          }

          $nav->addLink($name, "showsupervisor?id=".$key, null, $attr);
        }

        $navcreate = new LinksWidget();
        $navcreate->setTitle('Navigation');

        $attr = array("onclick"=>"modalneueGruppe()");
        $navcreate->addLink("Neue Gruppe anlegen", "#", "", $attr);
        $navcreate->addLink("Meine Portfolios", "show");

        $navSupervisorGroup = new LinksWidget();
        $navSupervisorGroup->setTitle("Supervisorengruppen");
        $navSupervisorGroupURL = URLHelper::getLink("plugins.php/eportfolioplugin/supervisorgroup");
        $navSupervisorGroup->addLink("Verwalten", $navSupervisorGroupURL);

        $sidebar->addWidget($nav);
        $sidebar->addWidget($navcreate);
        $sidebar->addWidget($navSupervisorGroup);

    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        $this->set_layout($GLOBALS['template_factory']->open('layouts/base.php'));
    }

    public function index_action()
    {

      $id = $_GET["id"];
      $this->id = $id;

      if(!$id == ''){
        $check = DBManager::get()->query("SELECT owner_id FROM eportfolio_groups WHERE seminar_id = '$id'")->fetchAll();

        //check permission
        if(!$check[0][0] == $GLOBALS["user"]->id){
          throw new AccessDeniedException(_("Sie haben keine Berechtigung"));
        } else {
          $this->groupList = Group::getGroupMember($id);

        }
      } else {

      }

      $this->userid = $GLOBALS["user"]->id;

      //not working MultiPersonSearch
      $mp = MultiPersonSearch::get('eindeutige_id')
        ->setLinkText(_('Person hinzufï¿½gen'))
        ->setTitle(_('Person zur Gruppe hinzufï¿½gen'))
        ->setExecuteURL($this->url_for('controller'))
        ->render();

      $this->mp = $mp;

      $this->url = $_SERVER['REQUEST_URI'];
      $course = new Seminar($id);
      $this->courseName = $course->getName();

    }

    public function countViewer($cid) {

      $query = DBManager::get()->query("SELECT  COUNT(Seminar_id) FROM eportfolio_user WHERE Seminar_id = '$cid' AND owner = 0")->fetchAll();
      echo $query[0][0];

    }

    public function getGroups($id) {

      $q = DBManager::get()->query("SELECT seminar_id FROM eportfolio_groups WHERE owner_id = '$id'")->fetchAll();
      $array = array();
      foreach ($q as $key) {
        array_push($array, $key[0]);
      }
      return $array;

    }

    public function getGroupMemberAjax($cid) {

      $q = DBManager::get()->query("SELECT user_id FROM eportfolio_groups_user WHERE Seminar_id = '$cid'")->fetchAll();
      $array = array();
      foreach ($q as $key) {
        array_push($array, $key[0]);
      }
      print json_encode($array);

    }

    public function getTemplates(){

      $semId;
      $seminare = array();

      foreach ($GLOBALS['SEM_TYPE'] as $id => $sem_type){ //get the id of ePortfolio Seminarclass
        if ($sem_type['name'] == 'Portfolio - Vorlage') {
          $semId = $id;
        }
      }

      $db = DBManager::get();
      $query = $db->query("SELECT Seminar_id FROM seminare WHERE status = '$semId'")->fetchAll();
      foreach ($query as $key) {
        array_push($seminare, $key[Seminar_id]);
      }

      return $seminare;

    }

    public function addTempToDB(){
      $groupid = $_POST["groupid"];
      $tempid = $_POST["tempid"];
      $q = DBManager::get()->query("SELECT templates FROM eportfolio_groups WHERE seminar_id = '$groupid'")->fetchAll();
      if(empty($q[0][0])){
        $array = array($tempid);
        $array = json_encode($array);
        DBManager::get()->query("UPDATE eportfolio_groups SET templates = '$array' WHERE seminar_id = '$groupid'");
        echo "created";
      } else {
        $array = json_decode($q[0][0]);
        if(in_array($tempid, $array)){
          echo "already";
          exit();
        }
        array_push($array, $tempid);
        $array = json_encode($array);
        DBManager::get()->query("UPDATE eportfolio_groups SET templates = '$array' WHERE seminar_id = '$groupid'");
        echo "created";
      }

    //  print_r($array);
      //DBManager::get()->query("UPDATE eportfolio_groups SET templates = '$array' WHERE seminar_id = '$groupid'");
    }

    //public function getGroupTemplates($id){
      //$q = DBManager::get()->query("SELECT templates FROM eportfolio_groups WHERE seminar_id = '$id'")->fetchAll();
      //$q = json_decode($q[0][0], true);
      //return $q;
    //}

    public function getChapters($id){
      $q = DBManager::get()->query("SELECT title, id FROM mooc_blocks WHERE seminar_id = '$id' AND type = 'Chapter'")->fetchAll();
      return $q;
    }

    public function getTemplateName($id){
      $q = DBManager::get()->query("SELECT temp_name FROM eportfolio_templates WHERE id = '$id'")->fetchAll();
      $array = array();
      return $q[0][0];
    }

    public function generateRandomString($length = 32) {
      $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
      $charactersLength = strlen($characters);
      $randomString = '';
      for ($i = 0; $i < $length; $i++) {
          $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
      return $randomString;
    }

    public function deletePortfolio(){
      $tempid = $_POST["tempid"];
      $groupid = $_POST["groupid"];

      //delete templateid in eportfolio_groups-table
      $q = DBManager::get()->query("SELECT templates FROM eportfolio_groups WHERE seminar_id = '$groupid'")->fetchAll();
      $templates = json_decode($q[0][0]);
      $templates = array_diff($templates, array($tempid));
      $templates = json_encode($templates);
      DBManager::get()->query("UPDATE eportfolio_groups SET templates = '$templates' WHERE  seminar_id = '$groupid'");

      //get all seminar ids
      $q = DBManager::get()->query("SELECT * FROM eportfolio WHERE template_id = '$tempid'")->fetchAll();
      $member = Group::getGroupMember($groupid); // get member list as array
      foreach ($q as $key) {
        $sid = $key["Seminar_id"];
        $ownerid = DBManager::get()->query("SELECT owner_id FROM eportfolio WHERE Seminar_id = '$sid'")->fetchAll();
        if (in_array($ownerid[0][0], $member)) { //delete portfolios of group member only
          DBManager::get()->query("DELETE FROM seminare WHERE Seminar_id = '$sid'"); // delete in seminare
          DBManager::get()->query("DELETE FROM seminar_user WHERE Seminar_id = '$sid'"); //delete in seminar_user
          DBManager::get()->query("DELETE FROM eportfolio_user WHERE Seminar_id = '$sid'"); //delete in eportfolio_user
          DBManager::get()->query("DELETE FROM eportfolio WHERE Seminar_id = '$sid'"); // delete in eportfolio
        }

      }
    }

    public function createportfolio_action(){

      $semList = array();
      $masterid = $_POST['master'];
      $groupid = $_POST['groupid'];

      $member     = Group::getGroupMember($_POST["groupid"]);
      $groupowner = Group::getOwner($_POST["groupid"]);
      $groupname  = new Seminar($_POST["groupid"]);

      $groupHasTemplates = DBManager::get()->query("SELECT templates FROM eportfolio_groups WHERE seminar_id = '$groupid'")->fetchAll();
      $groupHasTemplates = json_decode($groupHasTemplates[0][0]);

      if (count($groupHasTemplates) >= 1) {

        foreach ($member as $key => $value) {
          $seminarGroupId = DBManager::get()->query("SELECT Seminar_id FROM eportfolio WHERE group_id = '$groupid' AND owner_id = '$value'")->fetchAll();
          $seminarGroupId = $seminarGroupId[0][0];
          array_push($semList, $sem->Seminar_id);
        }

      } else {

        $master = new Seminar($masterid);
        $sem_type_id = $this->getPortfolioSemId();

        foreach ($member as $key => $value) {

            $userid           = $value; //get userid
            $sem_name         = $master->getName()." (".$groupname->getName().")";
            $sem_description  = "Beschreibung";

            $sem              = new Seminar();
            $sem->Seminar_id  = $sem->createId();
            $sem->name        = $sem_name;
            $sem->description = $sem_description;
            $sem->status      = $sem_type_id;
            $sem->read_level  = 1;
            $sem->write_level = 1;
            $sem->institut_id = Config::Get()->STUDYGROUP_DEFAULT_INST;
            $sem->visible     = 1;

            $sem_id = $sem->Seminar_id;

            $sem->addMember($userid, 'dozent'); // add target to seminar
            $member = Group::getGroupMember($groupid);

            if (!in_array($groupowner, $member)) {
              $sem->addMember($groupowner, 'dozent');
            }

            $sem->store(); //save sem

            array_push($semList, $sem->Seminar_id);

            $eportfolio = new Seminar();
            $eportfolio_id = $eportfolio->createId();
            DBManager::get()->query("INSERT INTO eportfolio (Seminar_id, eportfolio_id, group_id, owner_id, template_id, supervisor_id) VALUES ('$sem_id', '$eportfolio_id', '$groupid' , '$userid', '$masterid', '$groupowner')"); //table eportfolio
            DBManager::get()->query("INSERT INTO eportfolio_user(user_id, Seminar_id, eportfolio_id, owner) VALUES ('$userid', '$Seminar_id' , '$eportfolio_id', 1)"); //table eportfollio_user

            create_folder(_('Allgemeiner Dateiordner'),
                          _('Ablage für allgemeine Ordner und Dokumente der Veranstaltung'),
                          $sem->Seminar_id,
                          7,
                          $sem->Seminar_id);
        }

      }

      $this->storeTemplateForGroup($_POST['groupid'], $_POST['master']);
      print_r(json_encode($semList));
      die();
    }

    public function getPortfolioSemId(){
      foreach ($GLOBALS['SEM_TYPE'] as $id => $sem_type){ //get the id of ePortfolio Seminarclass
        if ($sem_type['name'] == 'ePortfolio') {
          return $id;
        }
      }
    }

    public function storeTemplateForGroup($groupid, $postMaster){
      $query = DBManager::get()->query("SELECT templates FROM eportfolio_groups WHERE seminar_id = '$groupid'")->fetchAll();
      if (!empty($query[0][0])) {
        $array = json_decode($query[0][0]);
        array_push($array, $postMaster);
        $array = json_encode($array);
        DBManager::get()->query("UPDATE eportfolio_groups SET templates = '$array' WHERE seminar_id = '$groupid'");
      } else {
        $array = array($postMaster);
        $array = json_encode($array);
        DBManager::get()->query("UPDATE eportfolio_groups SET templates = '$array' WHERE seminar_id = '$groupid'");
      }
    }

    public function checkTemplate($groupid, $masterid) {
      $query = DBManager::get()->query("SELECT templates FROM eportfolio_groups WHERE seminar_id = '$groupid'")->fetchAll();
      if (empty($query[0][0])) {
        return false;
      } else {
        $array = json_decode($query[0][0]);
        if (in_array($masterid, $array)) {
          return true;
        } else {
          return false;
        }
      }
    }

    public function addTemplateTest(){
      $cid = "43ff6d96a50cf30836ef6b8d1ea60667";

      $plugin_courseware = \PluginManager::getInstance()->getPlugin('Courseware');
      require_once 'public/' . $plugin_courseware->getPluginPath() . '/vendor/autoload.php';

      //export from master course
      $containerExport =  new Container();
      $containerExport["cid"] = $cid; //Master cid
      print_r($containerExport['block_factory']);
    }

    public function addUsersToGroup(){

      $mp           = MultiPersonSearch::load('eindeutige_id');
      $groupid      = $_GET['id'];
      $templates    = Group::getTemplates($groupid);
      $outputArray  = array();

      # User der Gruppe hinzufï¿½gen
      foreach ($mp->getAddedUsers() as $userId) {
        $query = DBManager::get()->query("SELECT user_id FROM eportfolio_groups_user WHERE user_id = '$userId' AND seminar_id = '$groupid'")->fetchAll(); //checkt ob schon in Gruppe eingetragen ist
        if(empty($query[0][0])){
          DBManager::get()->query("INSERT INTO eportfolio_groups_user (user_id, seminar_id) VALUES ('$userId', '$groupid')");
        }
      }

      # Fï¿½r jedes bereits benutze Template ein Seminar pro Nutzer erstellen
      foreach ($templates as $template) {

        $semList    = array();
        $masterid   = $template;
        $groupowner = Group::getOwner($groupid);
        $master     = new Seminar($masterid);

        # id ePortfolio Seminarklasse
        foreach ($GLOBALS['SEM_TYPE'] as $id => $sem_type){ //get the id of ePortfolio Seminarclass
          if ($sem_type['name'] == 'ePortfolio') {
            $sem_type_id = $id;
          }
        }

        # Erstellt fï¿½r jeden neu hinzugefï¿½gten User ein Seminar
        // foreach ($mp->getAddedUsers() as $userid){
        //
        //   $userid           = $userid; //get userid
        //   $sem_name         = $master->getName();
        //   $sem_description  = "Beschreibung";
        //
        //   $sem              = new Seminar();
        //   $sem->Seminar_id  = $sem->createId();
        //   $sem->name        = $sem_name;
        //   $sem->description = $sem_description;
        //   $sem->status      = $sem_type_id;
        //   $sem->read_level  = 1;
        //   $sem->write_level = 1;
        //   $sem->institut_id = Config::Get()->STUDYGROUP_DEFAULT_INST;
        //   $sem->visible     = 1;
        //
        //   $sem_id = $sem->Seminar_id;
        //
        //   $sem->addMember($userid, 'dozent'); // add target to seminar
        //   $member = $this->getGroupMember($groupid);
        //   if (!in_array($groupowner, $member)) {
        //     $sem->addMember($groupowner, 'dozent');
        //   }
        //
        //   $sem->store(); //save sem
        //
        //   array_push($semList, $sem->Seminar_id);
        //
        //   $eportfolio = new Seminar();
        //   $eportfolio_id = $eportfolio->createId();
        //   DBManager::get()->query("INSERT INTO eportfolio (Seminar_id, eportfolio_id, owner_id, template_id) VALUES ('$sem_id', '$eportfolio_id', '$userid', '$masterid')"); //table eportfolio
        //   DBManager::get()->query("INSERT INTO eportfolio_user(user_id, Seminar_id, eportfolio_id, owner) VALUES ('$userid', '$Seminar_id' , '$eportfolio_id', 1)"); //table eportfollio_user
        //
        // }
        // array_push($outputArray, $semList);
      }

    }

    public function isThereAnyUser() {
      $groupid  = $_GET['id'];
      $query    = DBManager::get()->query("SELECT user_id FROM eportfolio_groups_user WHERE seminar_id = '$groupid'")->fetchAll();
      if (empty($query[0])) {
        return false;
      } else {
        return true;
      }
    }

    public function checkSupervisorNotiz($id){
      $supervisorNotiz = DBManager::get()->query("SELECT id FROM mooc_blocks WHERE parent_id = '$id' ")->fetchAll();
      foreach ($supervisorNotiz[0] as $key => $value) {
        $supervisorNotizSubchapter = DBManager::get()->query("SELECT id FROM mooc_blocks WHERE parent_id = '$value' ")->fetchAll();
        foreach ($supervisorNotizSubchapter[0] as $keySub => $valueSub) {
          $supervisorNotizSubchapterBlock = DBManager::get()->query("SELECT id FROM mooc_blocks WHERE parent_id = '$valueSub' AND type ='PortfolioBlockSupervisor' ")->fetchAll();
          if (!empty($supervisorNotizSubchapterBlock)) {
            return true;
          }
        }
      }
    }

    public function checkSupervisorFeedback($id){
      $supervisorNotiz = DBManager::get()->query("SELECT id FROM mooc_blocks WHERE parent_id = '$id' ")->fetchAll();
      foreach ($supervisorNotiz[0] as $key => $value) {
        $supervisorNotizSubchapter = DBManager::get()->query("SELECT id FROM mooc_blocks WHERE parent_id = '$value' ")->fetchAll();
        foreach ($supervisorNotizSubchapter[0] as $keySub => $valueSub) {
          $supervisorNotizSubchapterBlock = DBManager::get()->query("SELECT id FROM mooc_blocks WHERE parent_id = '$valueSub' AND type ='PortfolioBlockSupervisor' ")->fetchAll();
          foreach ($supervisorNotizSubchapterBlock as $keyBlock => $valueBlock) {
            $supervisorFeedback = DBManager::get()->query("SELECT json_data FROM mooc_fields WHERE block_id = '$valueBlock[id]' AND name = 'supervisorcontent'")->fetchAll();
            if (!empty($supervisorFeedback[0][json_data])) {
              return true;
            }
          }
        }
      }
    }

    public function delete_action($id){

      # check permission if root
      $perm = get_global_perm($GLOBALS["user"]->id);

      if(!$perm == "root"){
          throw new Exception("Not Allowed");
      }

      # get eportfolio id's
      $eportfolio = DBManager::get()->query("SELECT eportfolio_id, Seminar_id FROM eportfolio WHERE group_id = '$id'")->fetchAll();

      # eportfolio_groups
      DBManager::get()->query("DELETE FROM eportfolio_groups WHERE seminar_id = '$id'");

      # eportfolio_groups_user
      DBManager::get()->query("DELETE FROM eportfolio_groups_user WHERE seminar_id = '$id'");


      foreach ($eportfolio as $key) {
        $eportfolio_id = $key['eportfolio_id'];
        $Seminar_id = $key['Seminar_id'];

        # eportfolio_user
        DBManager::get()->query("DELETE FROM eportfolio_user WHERE eportfolio_id = '$eportfolio_id'");

        $sem = new Seminar($Seminar_id);
        $sem->delete();

      }

      # eportfolio
      DBManager::get()->query("DELETE FROM eportfolio WHERE group_id = '$id'");

    }

}
