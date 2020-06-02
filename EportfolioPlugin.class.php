<?
require __DIR__ . '/bootstrap.php';


/**
 * EportfolioPlugin.class.php
 * @author  Marcel Kipp
 * @version 0.18
 */
class EportfolioPlugin extends StudIPPlugin implements StandardPlugin, SystemPlugin
{

    public function __construct()
    {
        parent::__construct();

        $navigation = new Navigation(_('ePortfolios'));
        $navigation->setURL(PluginEngine::getURL($this, [], 'show'));

        if (!Request::get('username') || Request::get('username') == $GLOBALS['user']->username) {
            Navigation::addItem('/profile/eportfolioplugin', $navigation);
        }

        NotificationCenter::addObserver($this, 'setup_navigation', 'PageWillRender');
        NotificationCenter::addObserver($this, 'store_activity', 'UserDidPostSupervisorNotiz');
        NotificationCenter::addObserver($this, 'store_activity', 'SupervisorDidPostAnswer');
        NotificationCenter::addObserver($this, 'store_activity', 'UserDidPostNotiz');

        NotificationCenter::addObserver($this, 'prevent_settings_access', 'NavigationDidActivateItem');

        // generate css to hide all portfolio seminars on the my_realm page - DIRTY HACK, i know
        PageLayout::addStyle('#my_seminars {display: none; }');

        $stmt = DBManager::get()->prepare('SELECT Seminar_id FROM seminar_user
            JOIN eportfolio_user USING (Seminar_id)
            WHERE seminar_user.user_id = ?');
        $stmt->execute([$GLOBALS['user']->id]);

        $js = '';

        while ($semid = $stmt->fetchColumn()) {
            $js .= "jQuery('#my_seminars .course-$semid').parent().parent().remove();\n";
        }

        $js .= 'jQuery("#my_seminars").show(); jQuery("#my_seminars .mycourses").each(function() {
            if (jQuery(this).find("tbody tr").length == 0) {
                jQuery(this).remove();
            }
        })';

        if ($js) {
            PageLayout::addHeadElement('script', [], "jQuery(function() { $js })");
        }
    }

    public function getCardInfos($cid)
    {
        $db         = DBManager::get();
        $return_arr = [];
        $query      = "SELECT id, title FROM mooc_blocks WHERE seminar_id = :id AND type = 'Chapter' ORDER BY position ASC";
        $statement  = $db->prepare($query);
        $statement->execute([':id' => $cid]);
        $getCardInfos = $statement->fetchAll();
        foreach ($getCardInfos as $value) {
            $arrayOne          = [];
            $arrayOne['id']    = $value['id'];
            $arrayOne['title'] = $value['title'];

            // get sections of chapter
            $query     = "SELECT id, title FROM mooc_blocks WHERE parent_id = :id";
            $statement = $db->prepare($query);
            $statement->execute([':id' => $value['id']]);
            $arrayOne['section'] = $statement->fetchAll();

            array_push($return_arr, $arrayOne);
        }

        return $return_arr;
    }

    public function initialize()
    {
        $this->addStylesheet('assets/style.less');
        PageLayout::addStylesheet($this->getPluginURL() . '/assets/flexboxgrid.min.css');
        PageLayout::addScript($this->getPluginURL() . '/assets/js/jquery.tablesorter.min.js');
    }

    public function getTabNavigation($course_id)
    {
        $tabs         = [];
        $isSupervisor = SupervisorGroup::isUserInGroup($GLOBALS['user']->id, $course_id)
            || $GLOBALS['perm']->have_perm('root');

        //Veranstaltungsreiter in Vorlesung
        if (!$this->isPortfolio() && !$this->isVorlage()) {
            if ($isSupervisor) {
                $navigation = new Navigation('Portfolio-Arbeit', PluginEngine::getURL($this, compact('cid'), 'showsupervisor', true));
                $navigation->setImage(Icon::create('group4', Icon::ROLE_INFO_ALT));
                $navigation->setActiveImage(Icon::create('group4', Icon::ROLE_INFO));

                $item = new Navigation(_('Portfolio-Arbeit'), PluginEngine::getURL($this, compact('cid'), 'showsupervisor', true));
                $navigation->addSubNavigation('supervision', $item);

                $item = new Navigation(_('Activity Feed'), PluginEngine::getURL($this, compact('cid'), 'showsupervisor/activityfeed', true));
                $navigation->addSubNavigation('portfoliofeed', $item);

                $item = new Navigation(_('Berechtigungen Portfolioarbeit'), PluginEngine::getURL($this, compact('cid'), 'supervisorgroup', true));
                $navigation->addSubNavigation('supervisorgroup', $item);
            } else {
                $navigation = new Navigation('Portfolio-Arbeit', PluginEngine::getURL($this, compact('cid'), 'showstudent', true));
                $navigation->setImage(Icon::create('group4', Icon::ROLE_INFO_ALT));
                $navigation->setActiveImage(Icon::create('group4', Icon::ROLE_INFO));
            }

        } else if ($this->isPortfolio() || $this->isVorlage()) {
            if ($isSupervisor && !$this->isVorlage()) {
                $navigation = new Navigation(
                    _('Übersicht'),
                    PluginEngine::getURL($this, ['cid' => $course_id], 'eportfolioplugin', true)
                );
                $navigation->setImage(Icon::create('group4', Icon::ROLE_INFO_ALT));
                $navigation->setActiveImage(Icon::create('group4', Icon::ROLE_INFO));
            }

            if (Request::option('return_to')) {
                $_SESSION['return_to'] = Request::option('return_to');
            }

            if ($_SESSION['return_to']) {

                if ($_SESSION['return_to'] == 'overview') {
                    $tabs['return'] = new Navigation(
                        _('Zurück zum Profil'),
                        PluginEngine::getURL($this, [], 'show', true)
                    );
                } else {
                    if ($GLOBALS['perm']->have_studip_perm('tutor', $_SESSION['return_to'])) {
                        $return_to = 'showsupervisor';
                    } else {
                        $return_to = 'showstudent';
                    }

                    $tabs['return'] = new Navigation(
                        _('Zurück zur Veranstaltung'),
                        PluginEngine::getURL($this, ['cid' => $_SESSION['return_to']], $return_to, true)
                    );
                }
            }
        }

        $owner = Eportfoliomodel::isOwner($course_id, $GLOBALS['user']->id);

        if ($this->isPortfolio() && $owner) {
            $navigationSettings = new Navigation('Zugriffsrechte', PluginEngine::getURL($this, compact('cid'), 'settings', true));
            $navigationSettings->setImage(Icon::create('admin', Icon::ROLE_INFO_ALT));
            $navigationSettings->setActiveImage(Icon::create('admin', Icon::ROLE_INFO));
            $tabs['settings'] = $navigationSettings;
        } else if ($isSupervisor == true && $this->isVorlage()) {
            $navigationSettings = new Navigation('Einstellungen', PluginEngine::getURL($this, compact('cid'), 'blocksettings', true));
            $navigationSettings->setImage(Icon::create('admin', Icon::ROLE_INFO_ALT));
            $navigationSettings->setActiveImage(Icon::create('admin', Icon::ROLE_INFO));
            $tabs['blocksettings'] = $navigationSettings;
        }

        $tabs['eportfolioplugin'] = $navigation;

        return array_reverse($tabs);

    }

    public function getNotificationObjects($course_id, $since, $user_id)
    {
        return [];
    }

    public function getIconNavigation($course_id, $last_visit, $user_id)
    {
        if ($GLOBALS['perm']->have_studip_perm('dozent', $course_id)) {
            $url = 'showsupervisor/activityfeed';
        } else {
            $url = 'showstudent';
        }

        $icon = new AutoNavigation(
            'Portfolio-Arbeit',
            PluginEngine::getURL($this, ['cid' => $course_id, 'iconnav' => 'true'], $url, true)
        );

        $group = EportfolioGroup::find($course_id);
        if ($group) {
            $activies = $group->getActivities();
            if (is_array($activies)) {
                $new_ones = count($activies);
                if ($GLOBALS['perm']->have_studip_perm('dozent', $course_id)) {
                    $title = $new_ones > 1 ? sprintf(_('%s neue Ereignisse in Studierenden-Portfolios'), $new_ones) : _('1 neues Ereignisse in Studierenden-Portfolio');
                } else {
                    $title = _('Keine neuen Ereignisse.');
                }

                $icon->setImage(Icon::create('eportfolio', Icon::ROLE_ATTENTION, ['title' => $title]));
                $icon->setBadgeNumber($new_ones);
            } else {
                $icon->setImage(Icon::create('eportfolio', Icon::ROLE_ATTENTION, ['title' => 'Supervision']));
            }
        }

        return $icon;
    }

    public function getInfoTemplate($course_id)
    {
        // ...
    }

    private function getSemClass()
    {
        global $SEM_CLASS, $SEM_TYPE, $SessSemName;
        return $SEM_CLASS[$SEM_TYPE[$SessSemName['art_num']]['class']];
    }

    private function isSlotModule()
    {
        if (!$this->getSemClass()) {
            return false;
        }

        return $this->getSemClass()->isSlotModule(get_class($this));
    }

    //aktuelle cid/seminarid
    static function getSeminarId()
    {
        if (!Request::option('cid')) {
            if ($GLOBALS['SessionSeminar']) {
                URLHelper::bindLinkParam('cid', $GLOBALS['SessionSeminar']);
                return $GLOBALS['SessionSeminar'];
            }
            return false;
        }
        return Request::option('cid');
    }

    private function isPortfolio()
    {
        $course = Course::findCurrent();
        if ($course) {
            $status = $course->status;
            if ($status == Config::get()->SEM_CLASS_PORTFOLIO) {
                return true;
            }
        }
        return false;
    }

    private function isVorlage()
    {
        $course = Course::findCurrent();
        if ($course) {
            $status = $course->status;
            if ($status == Config::get()->SEM_CLASS_PORTFOLIO_VORLAGE) {
                return true;
            }
        }
        return false;
    }

    private function isSupervisionsgruppe()
    {
        $course = Course::findCurrent();
        if ($course) {
            $status = $course->status;
            if ($status == Config::get()->SEM_CLASS_PORTFOLIO_Supervisionsgruppe) {
                return true;
            }
        }
        return false;
    }

    public function setup_navigation()
    {
        if (($this->isPortfolio() || $this->isVorlage()) && Navigation::hasItem('/course/mooc_courseware')) {
            if ($this->isVorlage()) {
                $stmt = DBManager::get()->prepare("UPDATE mooc_blocks
                    SET title = 'Vorlage'
                    WHERE type = 'Courseware'
                        AND seminar_id = ?");
                $stmt->execute([Context::getId()]);

                Navigation::getItem('/course/mooc_courseware')->setTitle('Vorlage');
            } else {
                $stmt = DBManager::get()->prepare("UPDATE mooc_blocks
                    SET title = 'Vorlage'
                    WHERE type = 'Courseware'
                        AND seminar_id = ?");
                $stmt->execute([Context::getId()]);
                Navigation::getItem('/course/mooc_courseware')->setTitle('ePortfolio');
            }

            //default Courseware-Hilfe ersetzen
            $widgets = Helpbar::get()->getWidgets();
            foreach ($widgets as $index => $widget) {
                $elements = $widget->getElements();
                Helpbar::get()->removeWidget($index);
            }

            if ($this->isPortfolio()) {
                $description = _('Unter **Zugriffsrechte** können Sie einzelne Kapitel für Komilitonen oder Ihre Supervisoren freigeben.') . ' ';
                $description .= _('') . '';
                $tip         = _('Unter **ePortfolio** können Sie Ihr Portfolio bearbeiten. ');
                $tip         .= _('');
                $bearbeiten  = _('Um Inhalte oder Kapitel hinzuzufügen, klicken Sie im Reiter **ePortfolio** oben rechts auf den Doktorandenhut');
                Helpbar::get()->addPlainText(_(''), $description, '');
                Helpbar::get()->addPlainText(_(''), $tip, '');
                Helpbar::get()->addPlainText(_('Tip zum Bearbeiten'), $bearbeiten, Icon::create('doctoral-cap', 'info_alt'));
            }
            if ($this->isVorlage()) {
                $description = _('Unter **Teilnehmende** können Sie festlegen, wer Zugriff auf diese Vorlage hat. ') . ' ';
                $description .= _('Ausserdem können Sie unter **Einstellungen** Inhalte der Vorlage für die spätere Bearbeitung durch Studierende sperren.') . '';
                $tip         = _('Unter **Vorlage** können Sie die Vorlage bearbeiten. ');
                $tip         .= _('');
                $bearbeiten  = _('Um Inhalte oder Kapitel hinzuzufügen, klicken Sie im Reiter **Vorlage** oben rechts auf den Doktorandenhut');
                Helpbar::get()->addPlainText(_(''), $description, '');
                Helpbar::get()->addPlainText(_(''), $tip, '');
                Helpbar::get()->addPlainText(_('Tip zum Bearbeiten'), $bearbeiten, Icon::create('doctoral-cap', 'info_alt'));
            }
        }

        // rename Dateien to Meine Portfoliodateien
        if ($this->isPortfolio() && Navigation::hasItem('/course/files')) {
            Navigation::getItem('/course/files')->setTitle('Meine Portfoliodateien');
        }
    }

    public function prevent_settings_access($event, $path)
    {
        if (($this->isPortfolio() || $this->isVorlage())
            && $path == '/course/mooc_courseware/settings') {
            throw new AccessDeniedException();
        }
    }

    public function store_activity($notification, $block_id, $course_id)
    {
        EportfolioActivity::addActivity($course_id, $block_id, $notification);
    }
}
