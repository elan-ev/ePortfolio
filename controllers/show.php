<?php

class ShowController extends PluginController
{

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        PageLayout::setTitle('ePortfolio - Übersicht');
        Navigation::activateItem('/profile/eportfolioplugin');
        $this->userId   = $GLOBALS['user']->id;
        $this->isDozent = $GLOBALS['perm']->have_perm('dozent');

        $sidebar = Sidebar::Get();

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

    public function index_action()
    {
        $this->my_portfolios = EportfolioModel::getMyPortfolios();

        $courses = EportfolioModel::getPortfolioVorlagen();
        $this->vorlagen = array_filter($courses, function($course) use ($id) {
            return empty(EportfolioArchive::find($course->id));
        });

        $this->archived = array_filter($courses, function($course) use ($id) {
            return !empty(EportfolioArchive::find($course->id));
        });


        $this->accessible_portfolios = EportfolioModel::findBySQL(
            "JOIN seminar_user ON (
                seminar_user.Seminar_id = eportfolio.Seminar_id
            )
            WHERE
                eportfolio.owner_id != :user_id
                AND seminar_user.user_id = :user_id
                AND seminar_user.status = 'user'",
            [':user_id' => $GLOBALS['user']->id]
        );
    }

    public function list_seminars_action($vorlage_id)
    {
        if (!$GLOBALS['perm']->have_studip_perm('dozent', $vorlage_id)) {
            throw new Exception('Access denied');
        }

        $this->courses = [];

        foreach (EportfolioGroupTemplates::findBySeminar_id($vorlage_id) as $vorlage) {
            $course = Course::find($vorlage->group_id);
            $this->courses[$course->id] =
                $course->getFullname('sem-duration-name') . ' - ' .
                $course->getFullname();
        }

        asort($this->courses);
    }

    public function archive_action($vorlage_id)
    {
        if (!$GLOBALS['perm']->have_studip_perm('dozent', $vorlage_id)) {
            throw new Exception('Access denied');
        }

        $archive = new EportfolioArchive();
        $archive->eportfolio_id = $vorlage_id;
        $archive->store();

        PageLayout::postSuccess(_('Vorlage wurde archiviert.'));

        $this->redirect('show');
    }

    public function unarchive_action($vorlage_id)
    {
        if (!$GLOBALS['perm']->have_studip_perm('dozent', $vorlage_id)) {
            throw new Exception('Access denied');
        }

        $archive = EportfolioArchive::find($vorlage_id);
        if ($archive && $archive->delete()) {
            PageLayout::postSuccess(_('Vorlage wurde wiederhergestellt.'));
        }

        $this->redirect('show');
    }

    public function createvorlage_action()
    {
    }

    public function createportfolio_action()
    {
    }

    public function updatevorlage_action($vorlage_id)
    {
        if (!$GLOBALS['perm']->have_studip_perm('dozent', $vorlage_id)) {
            throw new Exception('Access denied');
        }

        $seminar = Seminar::getInstance($vorlage_id);

        if(Request::submitted('updatevorlage')) {
            $sem_name        = strip_tags(Request::get('name'));
            $sem_description = strip_tags(Request::get('description'));

            $seminar->name = $sem_name;
            $seminar->description = $sem_description;

            $seminar->store();

            PageLayout::postSuccess(sprintf(_('Vorlage "%s" wurde angelegt.'), $sem_name));

            $this->response->add_header('X-Dialog-Close', '1');
            $this->render_nothing();
        } else {
            $this->template_name = $seminar->name;
            $this->template_description = $seminar->description;
        }
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

        PageLayout::postMessage(MessageBox::success(sprintf(_('Vorlage "%s" wurde angelegt.'), $sem_name)));

        $this->response->add_header('X-Dialog-Close', '1');
        $this->render_nothing();
    }

    public function newportfolio_action()
    {
        $sem_type_id      = Config::get()->SEM_CLASS_PORTFOLIO;
        $current_semester = Semester::findCurrent();

        $userid          = $GLOBALS["user"]->id; //get userid
        $sem_name        = Request::get('name');
        $sem_description = Request::get('beschreibung');

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
        $values    = ['sem_id' => $sem_id, 'userid' => $userid];
        $query     = "INSERT INTO eportfolio (Seminar_id, owner_id, group_id) VALUES (:sem_id, :userid, '')";
        $statement = DBManager::get()->prepare($query);
        $statement->execute($values);

        PageLayout::postMessage(MessageBox::success(sprintf(_('Portfolio "%s" wurde angelegt.'), $sem_name)));

        $this->response->add_header('X-Dialog-Close', '1');
        $this->render_nothing();
    }
}
