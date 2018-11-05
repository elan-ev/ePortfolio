<?

use Mooc\Container;

class ShowsupervisorController extends StudipController
{
    
    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->current_plugin;
        
        $id        = $_GET["cid"];
        $this->sem = Course::findById($id);
        
        if ($this->sem) {
            $this->groupid = $id;
            $this->userid  = $GLOBALS["user"]->id;
            $this->ownerid = $GLOBALS["user"]->id;
            
            $this->groupTemplates = EportfolioGroupTemplates::getGroupTemplates($id);
            $this->templistid     = $this->groupTemplates;
            
            $group                   = EportfolioGroup::findbySQL('seminar_id = :id', [':id' => $this->groupid]);
            $this->supervisorGroupId = $group[0]->supervisor_group_id;
        }
        
        
        if (Request::get('type') == 'delete') {
            $this->deletePortfolio();
            exit();
        }
        
        
        # Aktuelle Seite
        PageLayout::setTitle('ePortfolio Administration');
        
        //sidebar
        $sidebar = Sidebar::Get();
        
        if ($this->groupid) {
            $navcreate = new LinksWidget();
            $navcreate->setTitle(_('Gruppen-Aktionen'));
            $navcreate->addLink(
                _('Supervisoren verwalten'),
                URLHelper::getLink("plugins.php/eportfolioplugin/showsupervisor/supervisorgroup/" . $id, ['cid' => $id]),
                Icon::create('edit', 'clickable')
            );
            $sidebar->addWidget($navcreate);
        }
        
    }
    
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        
        if (Course::findCurrent()) {
            Navigation::activateItem("course/eportfolioplugin");
        }
        
    }
    
    public function index_action()
    {
        Navigation::activateItem('/course/eportfolioplugin/supervision');
        
        $course = Course::findCurrent();
        $id     = $course->id;
        
        //berechtigung prüfen (group-owner TODO:refactoring //ggf das hier nur für Supervisor,
        //das würde dann aber schon in der Pluginklasse passieren
        /**
         *if(!$id == ''){
         *    $query = "SELECT owner_id FROM eportfolio_groups WHERE seminar_id = :id";
         *    $statement = DBManager::get()->prepare($query);
         *    $statement->execute(array(':id'=> $id));
         *    $check = $statement->fetchAll();
         *
         *    //check permission
         *    if(!$check[0][0] == $GLOBALS["user"]->id){
         *      throw new AccessDeniedException(_("Sie haben keine Berechtigung"));
         *    }
         *}
         */
        
        $this->id     = $id;
        $this->userid = $GLOBALS["user"]->id;
        $this->group  = EportfolioGroup::find($id);
        
        //noch kein Portfoliogruppeneintrag für dieses Seminar vorhanden: Gruppe erstellen
        if (!$this->group) {
            EportfolioGroup::newGroup($this->userid, $course->id);
        }
        $this->courseName = $course->name;
        $this->member     = EportfolioGroup::getGroupMember($course->id);
        
    }
    
    public function countViewer($cid)
    {
        $query     = "SELECT  COUNT(Seminar_id) FROM eportfolio_user WHERE Seminar_id = :cid AND owner = 0";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':cid' => $cid]);
        echo $statement->fetchAll()[0][0];
        
    }
    
    public function getChapters($id)
    {
        $query     = "SELECT title, id FROM mooc_blocks WHERE seminar_id = :id AND type = 'Chapter' ORDER BY position ASC";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':id' => $id]);
        return $statement->fetchAll();
    }
    
    public function generateRandomString($length = 32)
    {
        $characters       = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString     = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
    //brauchen wir die hier wirklich?
    public function deletePortfolio()
    {
        //delete templateid in eportfolio_groups-table
        $query     = "SELECT templates FROM eportfolio_groups WHERE seminar_id = :groupid";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':groupid' => Request::get('groupid')]);
        $templates = json_decode($statement->fetchAll()[0][0]);
        $templates = array_diff($templates, [Request::get('tempid')]);
        $templates = json_encode($templates);
        $query     = "UPDATE eportfolio_groups SET templates = :templates WHERE  seminar_id = :groupid";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':groupid' => Request::get('groupid'), ':templates' => $templates]);
        
        //get all seminar ids
        $query     = "SELECT * FROM eportfolio WHERE template_id = :tempid";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':tempid' => Request::get('tempid')]);
        $q = $statement->fetchAll();
        
    }
    
    public function createportfolio_action($master)
    {
        
        $this->seminar_list = [];
        $masterid           = $master;
        $groupid            = Course::findCurrent()->id;
        $group              = EportfolioGroup::find($groupid);
        
        $member = EportfolioGroup::getGroupMember($groupid);;
        $groupowner        = $group->owner_id;
        $groupname         = new Seminar($groupid);
        $supervisorgroupid = EportfolioGroup::getSupervisorGroupId($groupid);
        
        /**
         * Jeden User in der Gruppe einzeln behandeln
         * **/
        
        foreach ($member as $user_id) {
            
            /**
             * Überprüfen ob es für den Nutzer schon ein Portfolio-Seminar gibt
             * **/
            
            $portfolio_id = EportfolioGroup::getPortfolioIDsFromUserinGroup($groupid, $user_id);
            
            if (!empty($portfolio_id)) {
                
                /**
                 * Wenn ja: Template einfach wie gehabt Kopieren
                 * bzw. in die seminar_list anfügen und später triggern
                 * **/
                
                $portfolio_id_add = $portfolio_id[0];
                array_push($this->seminar_list, $portfolio_id_add);
                
            } else {
                
                /**
                 * Wenn nein: Neues Portfolio-Seminar für den User anlegen und ausgewähltes Template kopieren
                 * in seminar_list anfügen
                 * **/
                
                $portfolio_id_add = EportfolioModel::createPortfolioForUser($groupid, $user_id);
                array_push($this->seminar_list, $portfolio_id_add);
                
            }
            
        }
        
        EportfolioGroup::createTemplateForGroup($groupid, $masterid, $GLOBALS["user"]->id);
        
        $this->masterid = $masterid;
        $this->groupid  = $groupid;
        
        VorlagenCopy::copyCourseware(new Seminar($masterid), $this->seminar_list);
        EportfolioActivity::addVorlagenActivity($groupid, User::findCurrent()->id);
        
        $this->redirect('showsupervisor?cid=' . $groupid);
        
    }
    
    public function delete_action($cid)
    {
        EportfolioGroup::deleteGroup(Request::get('cid'));
        PageLayout::postMessage(MessageBox::success(_('Die Gruppe wurde gel�scht.')));
        $this->redirect(URLHelper::getLink("plugins.php/eportfolioplugin/showsupervisor", ['cid' => '']));
    }
    
    public function supervisorgroup_action($group_Id)
    {
        $groupId         = Course::findCurrent()->id;
        $sem             = new Seminar($groupId);
        $this->groupName = $sem->getName();
        
        $supervisorgroupid = Eportfoliogroup::getSupervisorGroupId($groupId);
        
        $group         = new SupervisorGroup($supervisorgroupid);
        $this->title   = $group->name;
        $this->groupId = $group->id;
        $this->linkId  = $groupId;
        
        $search_obj = new SQLSearch("SELECT auth_user_md5.user_id, CONCAT(auth_user_md5.nachname, ', ', auth_user_md5.vorname, ' (' , auth_user_md5.email, ')' ) as fullname, username, perms "
            . "FROM auth_user_md5 "
            . "WHERE (CONCAT(auth_user_md5.Vorname, \" \", auth_user_md5.Nachname) LIKE :input "
            . "OR CONCAT(auth_user_md5.Nachname, \" \", auth_user_md5.Vorname) LIKE :input "
            . "OR auth_user_md5.username LIKE :input)"
            . "AND auth_user_md5.perms LIKE 'dozent'"
            . "AND auth_user_md5.user_id NOT IN "
            . "(SELECT supervisor_group_user.user_id FROM supervisor_group_user WHERE supervisor_group_user.supervisor_group_id = '" . $supervisorgroupid . "')  "
            . "ORDER BY Vorname, Nachname ",
            _("Teilnehmer suchen"), "username");
        
        $this->mp = MultiPersonSearch::get('supervisorgroupSelectUsers')
            ->setLinkText(_('Supervisoren hinzuf�gen'))
            ->setTitle(_('Personen zur Supervisorgruppe hinzuf�gen'))
            ->setSearchObject($search_obj)
            ->setExecuteURL(URLHelper::getLink('plugins.php/eportfolioplugin/supervisorgroup/addUser/' . $group->id, ['id' => $group_id, 'redirect' => $this->url_for('showsupervisor/supervisorgroup/' . $this->linkId)]))
            ->render();
        
        $this->usersOfGroup = $group->user;
    }
    
    public function url_for($to = '')
    {
        $args = func_get_args();
        
        # find params
        $params = [];
        if (is_array(end($args))) {
            $params = array_pop($args);
        }
        
        # urlencode all but the first argument
        $args    = array_map('urlencode', $args);
        $args[0] = $to;
        
        return PluginEngine::getURL($this->dispatcher->current_plugin, $params, join('/', $args));
    }
    
    public function addAsFav_action($group_id, $template_id)
    {
        EportfolioGroup::markTemplateAsFav($group_id, $template_id);
        $this->redirect('showsupervisor?cid=' . $group_id);
    }
    
    public function deleteAsFav_action($group_id, $template_id)
    {
        EportfolioGroup::deletetemplateAsFav($group_id, $template_id);
        $this->redirect('showsupervisor?cid=' . $group_id);
    }
    
    public function memberdetail_action($group_id, $user_id)
    {
        $this->portfolio_id = EportfolioGroup::getPortfolioIdOfUserInGroup($user_id, $group_id);
        $this->chapters     = Eportfoliomodel::getChapters($this->portfolio_id);
        $this->group_id     = $group_id;
        $this->group_title  = Course::findCurrent()->name;
        
        $user           = new User($user_id);
        $this->user     = $user;
        $this->user_id  = $user_id;
        $this->vorname  = $user['Vorname'];
        $this->nachname = $user['Nachname'];
        
        $this->AnzahlFreigegebenerKapitel = EportfolioGroup::getAnzahlFreigegebenerKapitel($user_id, $group_id);
        $this->AnzahlAllerKapitel         = EportfolioGroup::getAnzahlAllerKapitel($group_id);
        $this->GesamtfortschrittInProzent = EportfolioGroup::getGesamtfortschrittInProzent($user_id, $group_id);
        $this->AnzahlNotizen              = EportfolioGroup::getAnzahlNotizen($user_id, $group_id);
        $this->templates                  = EportfolioGroupTemplates::getGroupTemplates($group_id);
    }
    
    public function activityfeed_action()
    {
        Navigation::activateItem('/course/eportfolioplugin/portfoliofeed');
        $group                 = EportfolioGroup::findOneBySQL('Seminar_id = :cid', [':cid' => Request::get('cid')]);
        $this->activities      = $group->getActivities();
        $this->countActivities = $group->getNumberOfNewActivities();
    }
    
    public function templatedates_action($group_id, $template_id)
    {
        $this->group_id    = $group_id;
        $this->template_id = $template_id;
        
        $timestamp    = EportfolioGroupTemplates::getDeadline($group_id, $template_id);
        $this->abgabe = date('d.m.Y', $timestamp);
    }
    
    public function settemplatedates_action($group_id, $template_id)
    {
        $dtime     = DateTime::createFromFormat("d.m.Y", $_POST['begin']);
        $timestamp = $dtime->getTimestamp();
        EportfolioGroupTemplates::setDeadline($group_id, $template_id, $timestamp);
        $this->redirect('showsupervisor?cid=' . $group_id);
    }
    
    public function createlateportfolio_action($group_id, $user_id)
    {
        
        /**
         *     1.   Hat ein nutzer überhaput schon ein Portfolio in der Gruppe ?
         *          Wenn nicht, muss eins erstellt werden.
         *     2.   Welche Templates fehlem dem Nutzer ? Diese müssen dann verteilt werden.
         **/
        
        $portfolio_id = EportfolioGroup::getPortfolioIDsFromUserinGroup($group_id, $user_id);
        
        if (!$portfolio_id) {
            /**
             * Der User hat noch kein Portfilio
             * in die das Template importiert werden kann
             * **/
            $portfolio_id = EportfolioModel::createPortfolioForUser($group_id, $user_id);
            $portfolio_id = $portfolio_id;
            
            $template_list_not_shared = EportfolioGroupTemplates::getGroupTemplates($group_id);
            //array_push($portfolio_list, $portfolio_id_in_array);
            
        } else {
            
            $portfolio_id = $portfolio_id[0];
            /**
             * Welche Templates wurden dem Nutzer noch nicht Verteilt?
             * **/
            $template_list_not_shared = EportfolioModel::getNotSharedTemplatesOfUserInGroup($group_id, $user_id, $portfolio_id);
        }
        
        /**
         * Jedes Template in der Liste verteilen
         * **/
        foreach ($template_list_not_shared as $current_template_id) {
            
            /**
             * Portfolio in ein Array packen da die copyCourseware-Funktion
             * ein Array mit Portfolio_ids erwartet
             * **/
            
            //$semList as $user_id => $cid
            VorlagenCopy::copyCourseware(new Seminar($current_template_id), [$user_id => $portfolio_id]);
            
            /**
             * TODO:
             * Hier vielleicht einen neuen Aktivitättypen einführen
             * für das nachträgliche Verteilen von Templates
             * z.B. Es wurden 5 Templates nachträglich an User XY verteilt
             * **/
            EportfolioActivity::addVorlagenActivity($group_id, User::findCurrent()->id);
        }
        
        $this->redirect('showsupervisor?cid=' . $group_id);
    }
    
}
