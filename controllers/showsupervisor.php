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

        if($_POST["type"] == 'addTemp'){
          $this->addTempToDB();
          exit();
        }

        if($_POST["type"] == 'delete'){
          $this->deletePortfolio();
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
        $groups = Group::getAllGroupsOfSupervisor($GLOBALS["user"]->id);
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

        $navcreate->addLink("Neue Gruppe anlegen", PluginEngine::getLink($this->plugin, array(), 'showsupervisor/creategroup') , "", array('data-dialog'=>"size=auto;reload-on-close"));
 
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
    }

    public function index_action()
    {

      $id = $_GET["id"];
      $this->id = $id;

      if(!$id == ''){
        $query = "SELECT owner_id FROM eportfolio_groups WHERE seminar_id = :id";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array(':id'=> $id));
        $check = $statement->fetchAll();

        //check permission
        if(!$check[0][0] == $GLOBALS["user"]->id){
          throw new AccessDeniedException(_("Sie haben keine Berechtigung"));
        } else {
          $this->groupList = Group::getGroupMember($id);

        }
      } else {

      }

      $this->userid = $GLOBALS["user"]->id;

      $this->url = $_SERVER['REQUEST_URI'];
      if($id){
        $course = new Seminar($id);
        $this->courseName = $course->getName();
      } else $this->courseName = '';

    }

    public function countViewer($cid) {

      $db = DBManager::get();
      $query = "SELECT  COUNT(Seminar_id) FROM eportfolio_user WHERE Seminar_id = :cid AND owner = 0";
      $statement = $db->prepare($query);
      $statement->execute(array(':cid'=> $cid));
      echo $statement->fetchAll()[0][0];

    }

    public function getGroupMemberAjax($cid) {

      $db = DBManager::get();
      $query = "SELECT user_id FROM eportfolio_groups_user WHERE Seminar_id = :cid";
      $statement = $db->prepare($query);
      $statement->execute(array(':cid'=> $cid));
      $array = array();
      foreach ($statement->fetchAll() as $key) {
        array_push($array, $key[0]);
      }
      print json_encode($array);

    }

    public function getTemplates(){

      $semId;
      $seminare = array();

      foreach ($GLOBALS['SEM_TYPE'] as $id => $sem_type){ //get the id of ePortfolio Seminarclass
        if ($sem_type['name'] == 'ePortfolio-Vorlage') {
          $semId = $id;
        }
      }

      $db = DBManager::get();
      $query = "SELECT Seminar_id FROM seminare WHERE status = :semId";
      $statement = $db->prepare($query);
      $statement->execute(array(':semId'=> $semId));
      foreach ($statement->fetchAll() as $key) {
        array_push($seminare, $key[Seminar_id]);
      }

      return $seminare;

    }
    
        public function addTempToDB(){
      $groupid = $_POST["groupid"];
      $tempid = $_POST["tempid"];
      $db = DBManager::get();
      $query = "SELECT templates FROM eportfolio_groups WHERE seminar_id = :groupid";
      $statement = $db->prepare($query);
      $statement->execute(array(':groupid'=> $groupid));
      $q = $statement->fetchAll();
      if(empty($q[0][0])){
        $array = array($tempid);
        $array = json_encode($array);
        $query = "UPDATE eportfolio_groups SET templates = :array WHERE seminar_id = :groupid";
        $statement = $db->prepare($query);
        $statement->execute(array(':groupid'=> $groupid, ':array'=> $array));
        echo "created";
      } else {
        $array = json_decode($q[0][0]);
        if(in_array($tempid, $array)){
          echo "already";
          exit();
        }
        array_push($array, $tempid);
        $array = json_encode($array);
        $query = "UPDATE eportfolio_groups SET templates = :array WHERE seminar_id = :groupid";
        $statement = $db->prepare($query);
        $statement->execute(array(':groupid'=> $groupid, ':array'=> $array));
        echo "created";
      }
    }

    public function getChapters($id){
        $db = DBManager::get();
        $query = "SELECT title, id FROM mooc_blocks WHERE seminar_id = :id AND type = 'Chapter'";
        $statement = $db->prepare($query);
        $statement->execute(array(':id'=> $id));
        return $statement->fetchAll();
    }

    public function getTemplateName($id){
      //$q = DBManager::get()->query("SELECT temp_name FROM eportfolio_templates WHERE id = '$id'")->fetchAll();
      //$array = array();
      //return $q[0][0];
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
      $db = DBManager::get();
      $query = "SELECT templates FROM eportfolio_groups WHERE seminar_id = :groupid";
      $statement = $db->prepare($query);
      $statement->execute(array(':groupid'=> $groupid));
      $templates = json_decode($statement->fetchAll()[0][0]);
      $templates = array_diff($templates, array($tempid));
      $templates = json_encode($templates);
      $query = "UPDATE eportfolio_groups SET templates = :templates WHERE  seminar_id = :groupid";
      $statement = $db->prepare($query);
      $statement->execute(array(':groupid'=> $groupid, ':templates'=> $templates));

      //get all seminar ids
      $query = "SELECT * FROM eportfolio WHERE template_id = :tempid";
      $statement = $db->prepare($query);
      $statement->execute(array(':tempid'=> $tempid));
      $q = $statement->fetchAll();
      $member = Group::getGroupMember($groupid); // get member list as array
      foreach ($q as $key) {
        $sid = $key["Seminar_id"];
        $query = "SELECT owner_id FROM eportfolio WHERE Seminar_id = :sid";
        $statement = $db->prepare($query);
        $statement->execute(array(':sid'=> $sid));
        $ownerid = $statement->fetchAll();
        if (in_array($ownerid[0][0], $member)) { //delete portfolios of group member only
            $query = "DELETE FROM seminare WHERE Seminar_id = :sid";
            $statement = $db->prepare($query);
            $statement->execute(array(':sid'=> $sid));
            $query = "DELETE FROM seminar_user WHERE Seminar_id = :sid";
            $statement = $db->prepare($query);
            $statement->execute(array(':sid'=> $sid));
            $query = "DELETE FROM eportfolio_user WHERE Seminar_id = :sid";
            $statement = $db->prepare($query);
            $statement->execute(array(':sid'=> $sid));
            $query = "DELETE FROM eportfolio WHERE Seminar_id = :sid";
            $statement = $db->prepare($query);
            $statement->execute(array(':sid'=> $sid));
        }

      }
    }

    public function creategroup_action($master = NULL, $groupid = NULL){
       
        $this->ownerid = $GLOBALS["user"]->id;
        if($_POST["create"]){
          $group_id = Group::create($this->ownerid, studip_utf8decode(strip_tags($_POST["name"])), studip_utf8decode(strip_tags($_POST["beschreibung"])));
          $this->response->add_header('X-Dialog-Close', '1');
          $this->render_nothing();
        }
        
    }
    
    
    public function createportfolio_action($master = NULL, $groupid = NULL){

      $this->semList = array();
      $masterid = $_POST['master'] ? $_POST['master'] : $master;
      $groupid = $_POST['groupid'] ? $_POST['groupid'] : $groupid;

      $member     = Group::getGroupMember($groupid);
      $groupowner = Group::getOwner($groupid);
      $groupname  = new Seminar($groupid);

      $db = DBManager::get();
      $query = "SELECT templates FROM eportfolio_groups WHERE seminar_id = :groupid";
      $statement = $db->prepare($query);
      $statement->execute(array(':groupid'=> $groupid));
      $groups = $statement->fetchAll()[0][0];
      $groupHasTemplates = json_decode($groups);

      //wenn bereits Vorlagen an diese Gruppe verteilt wurden, verwende die zugehörigen Portfolios um die weiteren Vorlagen hinzuzufügen
      if (count($groupHasTemplates) >= 1) {
        foreach ($member as $key => $value) {
          $query = "SELECT Seminar_id FROM eportfolio WHERE group_id = :groupid AND owner_id = :value";
          $statement = $db->prepare($query);
          $statement->execute(array(':groupid'=> $groupid, ':value'=> $value));
          $seminarGroupId = $statement->fetchAll(PDO::FETCH_ASSOC);
          $seminarGroupId = $seminarGroupId[0]['Seminar_id'];
          array_push($this->semList, $seminarGroupId);
        }

      } else {
        //Falls noch keine Vorlagen verteilt wurden erhält jeder Nutzer ein eigenes ePortfolio
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

            array_push($this->semList, $sem->Seminar_id);

            $eportfolio = new Seminar();
            $eportfolio_id = $eportfolio->createId();
            $query = "INSERT INTO eportfolio (Seminar_id, eportfolio_id, group_id, owner_id, template_id, supervisor_id) VALUES (:sem_id, :eportfolio_id, :groupid , :userid, :masterid, :groupowner)";
            $statement = $db->prepare($query);
            $statement->execute(array(':groupid'=> $groupid, ':sem_id'=> $sem_id, ':eportfolio_id'=> $eportfolio_id, ':userid'=> $userid,  ':masterid'=> $masterid, ':groupowner'=> $groupowner));
            $query = "INSERT INTO eportfolio_user(user_id, Seminar_id, eportfolio_id, owner) VALUES (:userid, :Seminar_id , :eportfolio_id, 1)";
            $statement = $db->prepare($query);
            $statement->execute(array(':Seminar_id'=> $sem_id, ':eportfolio_id'=> $eportfolio_id, ':userid'=> $userid));
            
            
            create_folder(_('Allgemeiner Dateiordner'),
                          _('Ablage für allgemeine Ordner und Dokumente der Veranstaltung'),
                          $sem->Seminar_id,
                          7,
                          $sem->Seminar_id);
        }

      }
      //ids der Studentenportfolios speichern, damit zukünftige weitere Vorlagen dort angehängt werden
      $this->storeTemplateForGroup($groupid, $masterid);
      
      
      $this->masterid = $masterid;
      $this->groupid = $groupid;
      
    }

    public function getPortfolioSemId(){
      foreach ($GLOBALS['SEM_TYPE'] as $id => $sem_type){ //get the id of ePortfolio Seminarclass
        if ($sem_type['name'] == 'ePortfolio') {
          return $id;
        }
      }
    }

    public function storeTemplateForGroup($groupid, $postMaster){
      $db = DBManager::get();
      $query = "SELECT templates FROM eportfolio_groups WHERE seminar_id = :groupid";
      $statement = $db->prepare($query);
      $statement->execute(array(':groupid'=> $groupid));
      $query = $statement->fetchAll();
      if (!empty($query[0][0])) {
        $array = json_decode($query[0][0]);
        array_push($array, $postMaster);
        $array = json_encode($array);
        $query = "UPDATE eportfolio_groups SET templates = :array WHERE seminar_id = :groupid";
        $statement = $db->prepare($query);
        $statement->execute(array(':array'=> $array, ':groupid'=> $groupid));
      } else {
        $array = array($postMaster);
        $array = json_encode($array);
        $query = "UPDATE eportfolio_groups SET templates = :array WHERE seminar_id = :groupid";
        $statement = $db->prepare($query);
        $statement->execute(array(':array'=> $array, ':groupid'=> $groupid));
      }
    }

    public function checkTemplate($groupid, $masterid) {
      $db = DBManager::get();
      $query = "SELECT templates FROM eportfolio_groups WHERE seminar_id = :groupid";
      $statement = $db->prepare($query);
      $statement->execute(array(':groupid'=> $groupid));
      $query = $statement->fetchAll();
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
          $db = DBManager::get();
          $query = "SELECT user_id FROM eportfolio_groups_user WHERE user_id = :userId AND seminar_id = :groupid";  //checkt ob schon in Gruppe eingetragen ist
          $statement = $db->prepare($query);
          $statement->execute(array(':groupid'=> $groupid, ':userId'=> $userId));
          $query = $statement->fetchAll();
          if(empty($query[0][0])){
            $query = "INSERT INTO eportfolio_groups_user (user_id, seminar_id) VALUES (:userId, :groupid)";
            $statement = $db->prepare($query);
            $statement->execute(array(':groupid'=> $groupid, ':userId'=> $userId));
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
      $db = DBManager::get();
      $query = "SELECT user_id FROM eportfolio_groups_user WHERE seminar_id = :groupid";
      $statement = $db->prepare($query);
      $statement->execute(array(':groupid'=> $groupid));
      $query = $statement->fetchAll();
      if (empty($query[0])) {
        return false;
      } else {
        return true;
      }
    }

    public function checkSupervisorNotiz($id){
      $db = DBManager::get();
      $query = "SELECT id FROM mooc_blocks WHERE parent_id = :id";
      $statement = $db->prepare($query);
      $statement->execute(array(':id'=> $id));
      $supervisorNotiz = $statement->fetchAll();
      foreach ($supervisorNotiz[0] as $key => $value) {
        $query = "SELECT id FROM mooc_blocks WHERE parent_id = :value";
        $statement = $db->prepare($query);
        $statement->execute(array(':value'=> $value));
        $supervisorNotizSubchapter = $statement->fetchAll();
        foreach ($supervisorNotizSubchapter[0] as $keySub => $valueSub) {
          $query = "SELECT id FROM mooc_blocks WHERE parent_id = :valueSub AND type ='PortfolioBlockSupervisor' ";
          $statement = $db->prepare($query);
          $statement->execute(array(':valueSub'=> $valueSub));
          $supervisorNotizSubchapterBlock = $statement->fetchAll();
          if (!empty($supervisorNotizSubchapterBlock)) {
            return true;
          }
        }
      }
    }

    public function checkSupervisorFeedback($id){
      $db = DBManager::get();
      $query = "SELECT id FROM mooc_blocks WHERE parent_id = :id";
      $statement = $db->prepare($query);
      $statement->execute(array(':id'=> $id));
      $supervisorNotiz = $statement->fetchAll();
      foreach ($supervisorNotiz[0] as $key => $value) {
        $query = "SELECT id FROM mooc_blocks WHERE parent_id = :value";  
        $statement = $db->prepare($query);
        $statement->execute(array(':value'=> $value));
        $supervisorNotizSubchapter = $statement->fetchAll();
        foreach ($supervisorNotizSubchapter[0] as $keySub => $valueSub) {
          $query = "SELECT id FROM mooc_blocks WHERE parent_id = :valueSub AND type ='PortfolioBlockSupervisor' ";  
          $statement = $db->prepare($query);
          $statement->execute(array(':valueSub'=> $valueSub));
          $supervisorNotizSubchapterBlock = $statement->fetchAll();
          foreach ($supervisorNotizSubchapterBlock as $keyBlock => $valueBlock) {
            $query = "SELECT json_data FROM mooc_fields WHERE block_id = :block_id AND name = 'supervisorcontent'";  
            $statement = $db->prepare($query);
            $statement->execute(array(':block_id'=> $valueBlock[id]));
            $supervisorFeedback = $statement->fetchAll();
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
      $db = DBManager::get();
      $query = "SELECT eportfolio_id, Seminar_id FROM eportfolio WHERE group_id = :id"; 
      $statement = $db->prepare($query);
      $statement->execute(array(':id'=> $id));
      $eportfolio = $statement->fetchAll();

      # eportfolio_groups
      $query = "DELETE FROM eportfolio_groups WHERE seminar_id = :id";  
      $statement = $db->prepare($query);
      $statement->execute(array(':id'=> $id));

      # eportfolio_groups_user
      $query = "DELETE FROM eportfolio_groups_user WHERE seminar_id = :id";  
      $statement = $db->prepare($query);
      $statement->execute(array(':id'=> $id));


      foreach ($eportfolio as $key) {
        $eportfolio_id = $key['eportfolio_id'];
        $Seminar_id = $key['Seminar_id'];

        # eportfolio_user
        $query = "DELETE FROM eportfolio_user WHERE eportfolio_id = :eportfolio_id";  
        $statement = $db->prepare($query);
        $statement->execute(array(':eportfolio_id'=> $eportfolio_id));

        $sem = new Seminar($Seminar_id);
        $sem->delete();

      }

      # eportfolio
      $query = "DELETE FROM eportfolio WHERE group_id = :id";  
      $statement = $db->prepare($query);
      $statement->execute(array(':id'=> $id));

    }
    
    public function deleteUserFromGroup_action($id, $group_id){
        Group::deleteUser($id, $group_id);
        $this->redirect('showsupervisor?id=' . $group_id);
    }

}
