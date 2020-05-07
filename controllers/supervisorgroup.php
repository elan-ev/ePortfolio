<?php

class SupervisorgroupController extends StudipController
{
    var $id = null;

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->current_plugin;
    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->course = Course::find(Context::getId());

        if ($this->course) {
            $this->groupid = $this->course->id;
            $this->userid  = $GLOBALS['user']->id;

            $this->groupTemplates = EportfolioGroupTemplates::getGroupTemplates($this->course->id);

            $this->templistid = $this->groupTemplates;

            $group = EportfolioGroup::findbySQL('seminar_id = :id', [':id' => $this->course->id]);

            $this->supervisorGroupId = $group[0]->supervisor_group_id;
        }
    }

    public function index_action()
    {
        Navigation::activateItem('/course/eportfolioplugin/supervisorgroup');

        PageLayout::setTitle(Context::getHeaderLine() . ' - Berechtigungen Portfolioarbeit');
        $supervisorgroupid = Eportfoliogroup::getSupervisorGroupId(Context::getId());

        $group         = new SupervisorGroup($supervisorgroupid);
        $this->title   = $group->name;
        $this->groupId = $group->id;
        $this->linkId  = Context::getId();

        $search_obj = new SQLSearch(
            "SELECT auth_user_md5.user_id, CONCAT(auth_user_md5.nachname, ', ', auth_user_md5.vorname, ' (' , auth_user_md5.email, ')' ) as fullname, username, perms 
            FROM auth_user_md5
            WHERE (
                CONCAT(auth_user_md5.Vorname, \" \", auth_user_md5.Nachname) LIKE :input 
                OR CONCAT(auth_user_md5.Nachname, \" \", auth_user_md5.Vorname) LIKE :input 
                OR auth_user_md5.username LIKE :input
            )
            AND auth_user_md5.perms IN ('dozent', 'tutor')
            AND auth_user_md5.user_id NOT IN (
                SELECT supervisor_group_user.user_id FROM supervisor_group_user WHERE supervisor_group_user.supervisor_group_id = '" . $supervisorgroupid . "')
            ORDER BY Vorname, Nachname ",
            _("Teilnehmer suchen"), "username");

        $this->mp = MultiPersonSearch::get('supervisorgroupSelectUsers')
            ->setLinkText(_('Weitere Zugriffsrechte vergeben'))
            ->setLinkIconPath('')
            ->setTitle(_('Personen Zugriffsrechte gewähren'))
            ->setSearchObject($search_obj)
            ->setExecuteURL(URLHelper::getLink('plugins.php/eportfolioplugin/supervisorgroup/addUser/' . $group->id, ['id' => $group_id, 'redirect' => $this->url_for('showsupervisor/supervisorgroup/' . $this->linkId)]))
            ->render();

        $this->usersOfGroup = $group->user;

        // Sidebar
        $sidebar = Sidebar::Get();

        if ($this->course->id) {
            $navcreate = new LinksWidget();
            $navcreate->setTitle(_('Aktionen'));
            $navcreate->addLinkFromHTML($this->mp, new Icon('community+add'));
            $sidebar->addWidget($navcreate);
        }
    }

    public function addUser_action($group)
    {
        $mp    = MultiPersonSearch::load('supervisorgroupSelectUsers');
        $group = new SupervisorGroup($group);
        foreach ($mp->getAddedUsers() as $key) {
            $group->addUser($key);
        }
        //$this->render_nothing();
        $this->redirect($this->url_for('supervisorgroup'), ['cid' => $group->eportfolio_group->seminar_id]);
    }

    public function deleteUser_action($group_id, $user_id)
    {
        $group = new SupervisorGroup($group_id);
        $group->deleteUser($user_id);
        $this->redirect($this->url_for('supervisorgroup'), ['cid' => $group->eportfolio_group->seminar_id]);
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
}
