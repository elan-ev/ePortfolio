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
        $this->plugin = $dispatcher->current_plugin;
        $user = get_username();

        $id = $_GET["cid"];
        $this->sem = Course::findById($id);

        if($this->sem){
            $this->groupid = $id;
            $this->userid = $GLOBALS["user"]->id;
            $this->ownerid = $GLOBALS["user"]->id;

            $this->groupTemplates = EportfolioGroupTemplates::getGroupTemplates($id);
            $this->templistid = $this->groupTemplates;

            $group = EportfolioGroup::findbySQL('seminar_id = :id', array(':id'=> $this->groupid));
            $this->supervisorGroupId = $group[0]->supervisor_group_id;

            //object_set_visit($this->groupid, "portfolio-group");
        }
        //userData for Modal

        if($_POST["type"] == 'addTemp'){
          $this->addTempToDB();
          exit();
        }

        if($_POST["type"] == 'delete'){
          $this->deletePortfolio();
          exit();
        }


        # Aktuelle Seite
        PageLayout::setTitle('ePortfolio - Supervisionsansicht');

        //sidebar
        $sidebar = Sidebar::Get();

        if($this->groupid){
            $navcreate = new LinksWidget();
            $navcreate->setTitle('Gruppen-Aktionen');
            //$navcreate->addLink("Nutzer eintragen", '', 'icons/16/blue/add/community.svg', NULL);
            $navcreate->addLink("Supervisoren verwalten", URLHelper::getLink("plugins.php/eportfolioplugin/showsupervisor/supervisorgroup/". $id, array('cid' => $id)), Icon::create('edit', 'clickable'), NULL);
            //$navcreate->addLink("Diese Gruppe l�schen", URLHelper::getLink('plugins.php/eportfolioplugin/showsupervisor/delete/' . $id), Icon::create('trash', 'clickable'), array('onclick' => "return confirm('Gruppe wirklich l�schen?')"));
            $sidebar->addWidget($navcreate);
        }

    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if(Course::findCurrent()){
            Navigation::activateItem("course/eportfolioplugin");
        }

    }

    public function index_action()
    {
        Navigation::activateItem('/course/eportfolioplugin/supervision');

        $course = Course::findCurrent();
        $id = $course->id;

        //berechtigung prüfen (group-owner TODO:refactoring //ggf das hier nur für Supervisor,
        //das würde dann aber schon in der Pluginklasse passieren
        /**
        if(!$id == ''){
            $query = "SELECT owner_id FROM eportfolio_groups WHERE seminar_id = :id";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array(':id'=> $id));
            $check = $statement->fetchAll();

            //check permission
            if(!$check[0][0] == $GLOBALS["user"]->id){
              throw new AccessDeniedException(_("Sie haben keine Berechtigung"));
            }
        }
         */

        $this->id = $id;
        $this->userid = $GLOBALS["user"]->id;
        $this->group = EportfolioGroup::find($id);

        //noch kein Portfoliogruppeneintrag für dieses Seminar vorhanden: Gruppe erstellen
        if(!$this->group){
            EportfolioGroup::newGroup($this->userid, $course->id);
        }
        $this->courseName = $course->name;
        $this->member = EportfolioGroup::getGroupMember($course->id);

    }

    public function countViewer($cid) {

      $db = DBManager::get();
      $query = "SELECT  COUNT(Seminar_id) FROM eportfolio_user WHERE Seminar_id = :cid AND owner = 0";
      $statement = $db->prepare($query);
      $statement->execute(array(':cid'=> $cid));
      echo $statement->fetchAll()[0][0];

    }

    public function getChapters($id){
        $db = DBManager::get();
        $query = "SELECT title, id FROM mooc_blocks WHERE seminar_id = :id AND type = 'Chapter' ORDER BY position ASC";
        $statement = $db->prepare($query);
        $statement->execute(array(':id'=> $id));
        return $statement->fetchAll();
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

    //brauchen wir die hier wirklich?
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

    }

    public function createportfolio_action($master){
      $this->semList = array();
      $masterid = $master;
      $groupid = Course::findCurrent()->id;
      $group = EportfolioGroup::find($groupid);

      $member     = EportfolioGroup::getGroupMember($groupid);;
      $groupowner = $group->owner_id;
      $groupname  = new Seminar($groupid);
      $supervisorgroupid = EportfolioGroup::getSupervisorGroupId($groupid);
      $db = DBManager::get();
      $query = "SELECT templates FROM eportfolio_groups WHERE seminar_id = :groupid";
      $statement = $db->prepare($query);
      $statement->execute(array(':groupid'=> $groupid));
      $groups = $statement->fetchAll()[0][0];
      EportfolioGroup::createTemplateForGroup($groupid, $master);
      //wenn bereits Vorlagen an diese Gruppe verteilt wurden, verwende die zugeh�rigen Portfolios um die weiteren Vorlagen hinzuzuf�gen
      if (EportfolioGroupTemplates::getNumberOfGroupTemplates($id) > 0) {
        foreach ($member as $user_id) {
          $query = "SELECT Seminar_id FROM eportfolio WHERE group_id = :groupid AND owner_id = :value";
          $statement = $db->prepare($query);
          $statement->execute(array(':groupid'=> $groupid, ':value'=> $user_id));
          $seminarGroupId = $statement->fetchAll(PDO::FETCH_ASSOC);
          $seminarGroupId = $seminarGroupId[0]['Seminar_id'];
          $user = new User($user_id);
          $this->semList[$user['Nachname']] = $seminarGroupId;
        }
      } else {
        //Falls noch keine Vorlagen verteilt wurden erh�lt jeder Nutzer ein eigenes ePortfolio
        $master = new Seminar($masterid);
        $sem_type_id = $this->getPortfolioSemId();

        foreach ($member as $user_id) {
            $owner            = User::find($user_id);
            $owner_fullname   = $owner['Vorname'] . ' ' . $owner['Nachname'];
            $sem_name         = "Gruppenportfolio: ".$groupname->getName() . " (" . $owner_fullname .")";
            $sem_description  = "Dieses Portfolio wurde Ihnen von einem Supervisor zugeteilt";
            $current_semester = Semester::findCurrent();
            $sem              = new Seminar();
            $sem->Seminar_id  = $sem->createId();
            $sem->name        = $sem_name;
            $sem->description = $sem_description;
            $sem->status      = $sem_type_id;
            $sem->read_level  = 1;
            $sem->write_level = 1;
            $sem->setEndSemester(-1);
            $sem->setStartSemester($current_semester->beginn);
            $sem->institut_id = Config::Get()->STUDYGROUP_DEFAULT_INST;
            $sem->visible     = 0;
            $sem_id = $sem->Seminar_id;
            $avatar = CourseAvatar::getAvatar($sem_id);
            $filename = sprintf('%s/%s',$this->plugin->getpluginPath(),'assets/images/avatare/eportfolio.png');
            $avatar->createFrom($filename);
            $sem->addMember($user_id, 'dozent'); // add user to his to seminar

            //add all Supervisors
            $supervisors = EportfolioGroup::getAllSupervisors($groupid);
            foreach($supervisors as $supervisor){
                $sem->addMember($supervisor, 'autor');
            }
            $sem->store(); //save sem
            $user = new User($user_id);
            $this->semList[$user['Nachname']] = $sem->Seminar_id;
            $eportfolio = new Seminar();
            $eportfolio_id = $eportfolio->createId();
            $query = "INSERT INTO eportfolio (Seminar_id, eportfolio_id, group_id, owner_id, template_id, supervisor_id) VALUES (:sem_id, :eportfolio_id, :groupid , :userid, :masterid, :groupowner)";
            $statement = $db->prepare($query);
            $statement->execute(array(':groupid'=> $groupid, ':sem_id'=> $sem_id, ':eportfolio_id'=> $eportfolio_id, ':userid'=> $user_id,  ':masterid'=> $masterid, ':groupowner'=> $groupowner));
            $query = "INSERT INTO eportfolio_user(user_id, Seminar_id, eportfolio_id, owner) VALUES (:userid, :Seminar_id , :eportfolio_id, 1)";
            $statement = $db->prepare($query);
            $statement->execute(array(':Seminar_id'=> $sem_id, ':eportfolio_id'=> $eportfolio_id, ':userid'=> $user_id));
            //delete dummy courseware chapters //TODO funktionier noch nicht
            $query = "DELETE FROM mooc_blocks WHERE seminar_id = :sem_id AND type NOT LIKE 'Courseware'";
            $statement = $db->prepare($query);
            $statement->execute(array(':sem_id'=> $sem_id));

            /**
            *create_folder(_('Allgemeiner Dateiordner'),
            *              _('Ablage f�r allgemeine Ordner und Dokumente der Veranstaltung'),
            *              $sem->Seminar_id,
            *              7,
            *              $sem->Seminar_id);
            **/
        }

      }


      $this->masterid = $masterid;
      $this->groupid = $groupid;
      //$this->response->add_header('X-Dialog-Close', '1');

      VorlagenCopy::copyCourseware(new Seminar($masterid), $this->semList);
      EportfolioActivity::addVorlagenActivity($groupid, User::findCurrent()->id);

      //$this->storeTemplateForGroup($groupid, $masterid);

      $this->redirect('showsupervisor?cid=' . $groupid);

    }


    public function getPortfolioSemId(){
      foreach ($GLOBALS['SEM_TYPE'] as $id => $sem_type){ //get the id of ePortfolio Seminarclass
        if ($sem_type['name'] == 'ePortfolio') {
          return $id;
        }
      }
    }

    public function delete_action($cid){
      $cid = $_GET['cid'];
      EportfolioGroup::deleteGroup($cid);
      PageLayout::postMessage(MessageBox::success(_('Die Gruppe wurde gel�scht.')));
      $this->redirect(URLHelper::getLink("plugins.php/eportfolioplugin/showsupervisor", array('cid' => '')));
    }

    public function supervisorgroup_action($group_Id){

      $groupId = Course::findCurrent()->id;
      $sem = new Seminar($groupId);
      $this->groupName = $sem->getName();

      $supervisorgroupid = Eportfoliogroup::getSupervisorGroupId($groupId);

      $group = new SupervisorGroup($supervisorgroupid);
      $this->title = $group->name;
      $this->groupId = $group->id;
      $this->linkId = $groupId;

      $search_obj = new SQLSearch("SELECT auth_user_md5.user_id, CONCAT(auth_user_md5.nachname, ', ', auth_user_md5.vorname, ' (' , auth_user_md5.email, ')' ) as fullname, username, perms "
                            . "FROM auth_user_md5 "
                            . "WHERE (CONCAT(auth_user_md5.Vorname, \" \", auth_user_md5.Nachname) LIKE :input "
                            . "OR CONCAT(auth_user_md5.Nachname, \" \", auth_user_md5.Vorname) LIKE :input "
                            . "OR auth_user_md5.username LIKE :input)"
                            . "AND auth_user_md5.perms LIKE 'dozent'"
                            . "AND auth_user_md5.user_id NOT IN "
                            . "(SELECT supervisor_group_user.user_id FROM supervisor_group_user WHERE supervisor_group_user.supervisor_group_id = '". $supervisorgroupid ."')  "
                            . "ORDER BY Vorname, Nachname ",
                _("Teilnehmer suchen"), "username");

      $this->mp = MultiPersonSearch::get('supervisorgroupSelectUsers')
        ->setLinkText(_('Supervisoren hinzuf�gen'))
        ->setTitle(_('Personen zur Supervisorgruppe hinzuf�gen'))
        ->setSearchObject($search_obj)
        ->setExecuteURL(URLHelper::getLink('plugins.php/eportfolioplugin/supervisorgroup/addUser/'. $group->id, array('id' => $group_id, 'redirect' => $this->url_for('showsupervisor/supervisorgroup/'. $this->linkId))))
        ->render();

      $this->usersOfGroup = $group->user;
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

    public function addAsFav_action($group_id, $template_id){
      EportfolioGroup::markTemplateAsFav($group_id, $template_id);
      $this->redirect('showsupervisor?cid=' . $group_id);
    }

    public function deleteAsFav_action($group_id, $template_id){
      EportfolioGroup::deletetemplateAsFav($group_id, $template_id);
      $this->redirect('showsupervisor?cid=' . $group_id);
    }

    public function memberdetail_action($group_id, $user_id){
      $this->portfolio_id = EportfolioGroup::getPortfolioIdOfUserInGroup($user_id, $group_id);
      $this->chapters = Eportfoliomodel::getChapters($this->portfolio_id);
      $this->group_id = $group_id;

      $user = new User($user_id);
      $this->vorname = $user['Vorname'];
      $this->nachname = $user['Nachname'];

      $this->AnzahlFreigegebenerKapitel = EportfolioGroup::getAnzahlFreigegebenerKapitel($user_id, $group_id);
      $this->AnzahlAllerKapitel = EportfolioGroup::getAnzahlAllerKapitel($group_id);
      $this->GesamtfortschrittInProzent = EportfolioGroup::getGesamtfortschrittInProzent($user_id, $group_id);
      $this->AnzahlNotizen = EportfolioGroup::getAnzahlNotizen($user_id, $group_id);
      $this->templates = EportfolioGroupTemplates::getGroupTemplates($group_id);
    }

    public function activityfeed_action(){
      Navigation::activateItem('/course/eportfolioplugin/portfoliofeed');
      $id = $_GET["cid"];
      $group = EportfolioGroup::findOneBySQL('Seminar_id = :cid', array(':cid' => $id));
      $this->activities = $group->getActivities();
      $this->countActivities = $group->getNumberOfNewActivities();
    }

    public function templatedates_action($group_id, $template_id){
      $this->group_id = $group_id;
      $this->template_id = $template_id;

      $timestamp = EportfolioGroupTemplates::getDeadline($group_id, $template_id);
      $this->abgabe = date('d.m.Y', $timestamp);
    }

    public function settemplatedates_action($group_id, $template_id){
      $dtime = DateTime::createFromFormat("d.m.Y", $_POST['begin']);
      $timestamp = $dtime->getTimestamp();
      EportfolioGroupTemplates::setDeadline($group_id, $template_id, $timestamp);
      $this->redirect('showsupervisor?cid=' . $group_id);
    }

}
