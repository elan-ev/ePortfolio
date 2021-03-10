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
                $this->url_for('show/createvorlage'),
                Icon::create('add'),
                ['data-dialog' => 'size=auto;reload-on-close']
            );
        }

        $sidebar->addWidget($actions);
    }

    public function index_action()
    {
        PageLayout::setTitle(_('Meine Portfolios'));
        Helpbar::get()->addPlainText(
            _('Hier finden Sie alle ePortfolios, die Sie angelegt haben oder die andere f&uuml;r Sie freigegeben haben.'),
            'icons/white/info-circle.svg'
        );
        $this->my_portfolios = EportfolioModel::getMyPortfolios();

        $courses = EportfolioModel::getPortfolioVorlagen();
        $this->vorlagen = array_filter($courses, function($course) {
            return empty(EportfolioArchive::find($course->id));
        });

        $this->archived = array_filter($courses, function($course) {
            return !empty(EportfolioArchive::find($course->id));
        });


        $this->accessible_portfolios = EportfolioModel::findBySQL(
            "JOIN seminar_user ON (
                seminar_user.Seminar_id = eportfolio.Seminar_id
            )
            WHERE
                eportfolio.owner_id != :user_id
                AND seminar_user.user_id = :user_id
                AND (
                    seminar_user.status = 'user' OR
                    seminar_user.status = 'autor'
                )",
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

        EportfolioArchive::create(['eportfolio_id' => $vorlage_id]);

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

        $seminar = Seminar::GetInstance($vorlage_id);

        if(Request::submitted('updatevorlage')) {
            $sem_name        = strip_tags(Request::get('name'));
            $sem_description = strip_tags(Request::get('description'));

            $seminar->name = $sem_name;
            $seminar->description = $sem_description;

            $seminar->store();

            PageLayout::postSuccess(sprintf(_('Vorlage "%s" wurde aktualisiert.'), $sem_name));

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
        @file_get_contents(
            URLHelper::getUrl($GLOBALS['ABSOLUTE_URI_STUDIP'] .'/plugins.php/courseware/courseware', [
                'cid'       => $sem->Seminar_id,
                'return_to' => 'overview'
            ])
        );
        DBManager::get()->execute(
            'UPDATE `mooc_blocks` SET `title` = "Vorlage" WHERE `seminar_id` = ? AND `parent_id` IS NULL AND `type` = "Courseware"',
            [$sem->Seminar_id]
        );
        PageLayout::postSuccess(sprintf(_('Vorlage "%s" wurde angelegt.'), htmlReady($sem_name)));

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

        EportfolioModel::create(
            [
                'Seminar_id' => $sem_id,
                'owner_id' => $userid
            ]
        );
        PageLayout::postSuccess(sprintf(_('Portfolio "%s" wurde angelegt.'), htmlReady($sem_name)));

        $this->response->add_header('X-Dialog-Close', '1');
        $this->render_nothing();
    }
}