<?

class ShowController extends StudipController
{
    
    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->current_plugin;
        
        $this->userId   = $GLOBALS["user"]->id;
        $this->isDozent = $GLOBALS['perm']->have_perm('dozent');
        
        $sidebar = Sidebar::Get();
        Sidebar::Get()->setTitle('ePortfolios von ' . $GLOBALS['user']->username);
        
        $navcreate = new ActionsWidget();
        $navcreate->setTitle('Navigation');
        $navcreate->addLink("Übersicht", 'show');
        $sidebar->addWidget($navcreate);
        
        $actions = new ActionsWidget();
        $actions->setTitle('Aktionen');
        if ($this->isDozent) {
            $actions->addLink(
                _('Vorlage erstellen'),
                PluginEngine::getLink($this->plugin, [], 'show/createvorlage'),
                Icon::create('add', 'clickable'), ['data-dialog' => "size=auto;reload-on-close"]
            );
        }
        
        $sidebar->addWidget($actions);
    }
    
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        PageLayout::setTitle('ePortfolio - Übersicht');
        Navigation::activateItem('/profile/eportfolioplugin');
    }
    
    public function index_action()
    {
        $this->user = $GLOBALS['user'];
    }
    
    public function getAccessPortfolio()
    {
        return Course::findBySQL(
            'INNER JOIN `eportfolio_user` ON `eportfolio_user`.`Seminar_id` = `seminare`.`Seminar_id`
            WHERE `eportfolio_user`.`user_id` = ? AND `eportfolio_user`.`owner`= "0"',
            [$GLOBALS['user']->id]
        );
    }

    public function getOwnerName($cid)
    {
        $sql = "SELECT CONCAT(a.Vorname, ' ', a.Nachname)
            FROM eportfolio e
            JOIN auth_user_md5 a ON a.user_id = e.owner_id
            WHERE e.Seminar_id = ?
        ";
        
        return DBManager::get()->fetchColumn($sql, [$cid]);
    }
    
    //TODO refactoring gehrt zu ePortfoliomodel
    public function countViewer($cid)
    {
        return  DBManager::get()->fetchColumn("SELECT COUNT(Seminar_id) FROM eportfolio_user WHERE Seminar_id = ? AND owner = 0", [$cid]);
    }
    
    public function createvorlage_action()
    {
    }
    
    public function createportfolio_action()
    {
    }
    
    public function newvorlage_action()
    {
        $sem_type_id      = Config::get()->SEM_CLASS_PORTFOLIO_VORLAGE;
        $current_semester = Semester::findCurrent();
        
        $userid          = $GLOBALS["user"]->id; //get userid
        $sem_name        = strip_tags(Request::get('name'));
        $sem_description = strip_tags(Request::get('beschreibung'));
        
        $sem              = new Seminar();
        $sem->name        = $sem_name;
        $sem->description = $sem_description;
        $sem->status      = $sem_type_id;
        $sem->read_level  = 1;
        $sem->write_level = 1;
        $sem->institut_id = Config::Get()->STUDYGROUP_DEFAULT_INST;
        $sem->visible     = 0;
        $sem_id           = $sem->Seminar_id;
        
        $sem->addMember($userid, 'dozent');
        $sem->store();
        
        $avatar   = CourseAvatar::getAvatar($sem_id);
        $filename = sprintf('%s/%s', $this->plugin->getpluginPath(), 'assets/images/avatare/vorlage.png');
        $avatar->createFrom($filename);
        
        $eportfolio    = new Seminar();
        $eportfolio_id = $eportfolio->createId();
        
        $query     = "INSERT INTO eportfolio (Seminar_id, eportfolio_id, owner_id, group_id) VALUES (:sem_id, :eportfolio_id, :userid, '')";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':sem_id' => $sem_id, ':eportfolio_id' => $eportfolio_id, ':userid' => $userid]); //table eportfolio
        
        $query     = "INSERT INTO eportfolio_user(user_id, Seminar_id, eportfolio_id, owner) VALUES (:userid, :Seminar_id , :eportfolio_id, 1)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':Seminar_id' => $sem_id, ':eportfolio_id' => $eportfolio_id, ':userid' => $userid]); //table eportfollio_user
        
        PageLayout::postMessage(MessageBox::success(sprintf(_('Vorlage "%s" wurde angelegt.'), $sem_name)));
        
        $this->response->add_header('X-Dialog-Close', '1');
        $this->render_nothing();
    }
    
    public function newportfolio_action()
    {
        $sem_type_id      = Config::get()->SEM_CLASS_PORTFOLIO;
        $current_semester = Semester::findCurrent();
        
        $userid          = $GLOBALS["user"]->id; //get userid
        $sem_name        = strip_tags($_POST['name']);
        $sem_description = strip_tags($_POST['beschreibung']);
        
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
        
        $sem->addMember($userid, 'dozent');
        $sem->store();
        
        $avatar   = CourseAvatar::getAvatar($sem_id);
        $filename = sprintf('%s/%s', $this->plugin->getpluginPath(), 'assets/images/avatare/eportfolio.png');
        $avatar->createFrom($filename);
        
        $eportfolio    = new Seminar();
        $eportfolio_id = $eportfolio->createId();
        
        //table eportfolio
        $values    = ['sem_id' => $sem_id, 'eportfolio_id' => $eportfolio_id, 'userid' => $userid];
        $query     = "INSERT INTO eportfolio (Seminar_id, eportfolio_id, owner_id, group_id) VALUES (:sem_id, :eportfolio_id, :userid, '')";
        $statement = DBManager::get()->prepare($query);
        $statement->execute($values);
        
        //table eportfolio_user
        $values2    = ['userid' => $userid, 'Seminar_id' => $sem_id, 'eportfolio_id' => $eportfolio_id,];
        $query2     = "INSERT INTO eportfolio_user(user_id, Seminar_id, eportfolio_id, owner) VALUES (:userid, :Seminar_id , :eportfolio_id, 1)";
        $statement2 = DBManager::get()->prepare($query2);
        $statement2->execute($values2);
        
        PageLayout::postMessage(MessageBox::success(sprintf(_('Portfolio "%s" wurde angelegt.'), $sem_name)));
        
        $this->response->add_header('X-Dialog-Close', '1');
        $this->render_nothing();
    }
}
