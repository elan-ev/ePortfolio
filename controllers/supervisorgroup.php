<?php

class SupervisorgroupController extends PluginController
{
    public $id = null;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!$GLOBALS['perm']->have_studip_perm('dozent', Context::getId())) {
            throw new AccessDeniedException();
        }

        $this->course = Course::find(Context::getId());

        if ($this->course) {
            $this->groupid = $this->course->id;
            $this->userid = $GLOBALS['user']->id;

            $this->groupTemplates = EportfolioGroupTemplates::getGroupTemplates($this->course->id);

            $this->templistid = $this->groupTemplates;

            $this->supervisorGroup = SupervisorGroup::findOnebySQL('seminar_id = :id',
                [':id' => $this->course->id]
            );

        }
    }

    public function index_action()
    {
        Navigation::activateItem('/course/eportfolioplugin/supervisorgroup');

        PageLayout::setTitle(Context::getHeaderLine() . ' - Berechtigungen Portfolioarbeit');

        $this->title = $this->supervisorGroup->name;
        $this->groupId = $this->supervisorGroup->id;
        $this->linkId = Context::getId();

        $search_obj = new SQLSearch(
            "SELECT auth_user_md5.user_id, username, perms,
                CONCAT(auth_user_md5.nachname, ', ', auth_user_md5.vorname, ' (' , auth_user_md5.email, ')' ) as fullname
            FROM seminar_user
            LEFT JOIN auth_user_md5 USING(user_id)
            WHERE
                Seminar_id = '$this->linkId'
                AND (
                    CONCAT(auth_user_md5.Vorname, \" \", auth_user_md5.Nachname) LIKE :input
                    OR CONCAT(auth_user_md5.Nachname, \" \", auth_user_md5.Vorname) LIKE :input
                    OR auth_user_md5.username LIKE :input
                )
                AND auth_user_md5.perms IN ('dozent', 'tutor')
                AND auth_user_md5.user_id NOT IN (
                    SELECT supervisor_group_user.user_id FROM supervisor_group_user
                        WHERE supervisor_group_user.supervisor_group_id = '" . $this->supervisorGroup->id . "'
                )
            ORDER BY Vorname, Nachname ",
            _("Teilnehmer suchen"), "username");
        $course_users = $this->course->members->filter(function ($value) {
            return $value['status'] == 'dozent';
        })->pluck('user_id');
        $deputies = $this->course->deputies->pluck('user_id');
        if(!empty($deputies)) {
            $course_users = array_merge($course_users, $deputies);
        }

        $this->mp = MultiPersonSearch::get('supervisorgroupSelectUsers')
            ->setLinkText(_('Weitere Zugriffsrechte vergeben'))
            ->setLinkIconPath('')
            ->setTitle(_('Personen Zugriffsrechte gewähren'))
            ->setSearchObject($search_obj)
            ->setExecuteURL($this->url_for('supervisorgroup/addUser/' . $this->groupId, ['id' => $this->groupId, 'redirect' => $this->url_for('showsupervisor/supervisorgroup/' . $this->linkId)]))
            ->setDefaultSelectableUser($course_users)
            ->addQuickFilter('Lehrende und Tutor/innen dieser Veranstaltung', $course_users)
            ->render();

        $this->usersOfGroup = $this->supervisorGroup->user;

        // Sidebar
        $sidebar = Sidebar::Get();

        if ($this->course->id) {
            $navcreate = new LinksWidget();
            $navcreate->setTitle(_('Aktionen'));
            $navcreate->addLinkFromHTML($this->mp, Icon::create('community+add'));
            $sidebar->addWidget($navcreate);
        }
    }

    public function addUser_action()
    {
        $group = SupervisorGroup::findOneBySQL('Seminar_id = ?', [$this->course->id]);

        if (!$group) {
            $group = SupervisorGroup::create([
                'id' => md5(uniqid()),
                'Seminar_id' => $this->course->id,
                'name' => $this->course->name
            ]);
        }

        $mp = MultiPersonSearch::load('supervisorgroupSelectUsers');
        foreach ($mp->getAddedUsers() as $key) {
            try {
                $group->addUser($key);
            } catch (PDOException $e) {

            }
        }

        $this->redirect($this->url_for('supervisorgroup', ['cid' => $this->course->id]));
    }

    public function deleteUser_action($group_id, $user_id)
    {
        $group = new SupervisorGroup($group_id);
        $group->deleteUser($user_id);
        $this->redirect($this->url_for('supervisorgroup', ['cid' => $this->course->id]));
    }
}