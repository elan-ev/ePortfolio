<?php

class SettingsController extends PluginController
{
    public function index_action($cid = null)
    {
        $userid          = $GLOBALS['user']->id;
        $course          = Course::findCurrent();
        $this->isVorlage = EportfolioModel::isVorlage($course->id);
        $eportfolio      = EportfolioModel::findBySeminarId($course->id);

        $seminar = new Seminar($course->id);

        # Aktuelle Seite
        PageLayout::setTitle($course->getFullname() . ' - Zugriffsrechte');

        //autonavigation
        Navigation::activateItem("course/settings");

        $sidebar = Sidebar::Get();
        $sidebar->setTitle(_('Navigation'));

        $views = new ViewsWidget();
        $views->setTitle(_('Rechte'));
        $views->addLink(_('Zugriffsrechte vergeben'), '')->setActive(true);
        Sidebar::get()->addWidget($views);

        $chapters      = EportfolioModel::getChapters($course->id);
        $viewers       = $course->getMembersWithStatus('autor');
        $supervisor_id = $this->getSupervisorGroupOfPortfolio($course->id);

        $search_obj = new SQLSearch("SELECT auth_user_md5.user_id, CONCAT(auth_user_md5.nachname, ', ', auth_user_md5.vorname, ' (' , auth_user_md5.email, ')' ) as fullname, username, perms "
            . "FROM auth_user_md5 "
            . "WHERE (CONCAT(auth_user_md5.Vorname, \" \", auth_user_md5.Nachname) LIKE :input "
            . "OR CONCAT(auth_user_md5.Nachname, \" \", auth_user_md5.Vorname) LIKE :input "
            . "OR auth_user_md5.username LIKE :input)"
            . "AND auth_user_md5.user_id NOT IN "
            . "(SELECT seminar_user.user_id FROM seminar_user WHERE seminar_user.Seminar_id = '" . $course->id . "')  "
            . "ORDER BY Vorname, Nachname ",
            _("Nutzer suchen"), "username");

        $this->mp = MultiPersonSearch::get('selectFreigabeUser')
            ->setLinkText(_('Zugriffsrechte vergeben'))
            ->setLinkIconPath('')
            ->setTitle(_('NutzerInnen zur Verwaltung von Zugriffsrechten hinzufügen'))
            ->setSearchObject($search_obj)
            ->setExecuteURL(URLHelper::getLink('plugins.php/eportfolioplugin/settings/addZugriff/' . $course->id))
            ->render();

        // Sidebar
        $sidebar = Sidebar::Get();

        if ($course->id) {
            $navcreate = new LinksWidget();
            $navcreate->setTitle(_('Aktionen'));
            $navcreate->addLinkFromHTML($this->mp, new Icon('community+add'));
            $sidebar->addWidget($navcreate);
        }

        $this->cid           = $course->id;
        $this->userid        = $userid;
        $this->title         = $course->getFullname();
        $this->chapterList   = $chapters;
        $this->viewerList    = $viewers;
        $this->numberChapter = count($chapters);
        $this->supervisorId  = $supervisor_id;
        $supervisors         = SupervisorGroupUser::findBySupervisorGroupId($supervisor_id);

        foreach ($supervisors as $supervisor) {
            $this->supervisor_list[]                 = htmlReady($supervisor->user->getFullname());
            $this->supervisors[$supervisor->user_id] = true;
        }

    }

    public function setAccess_action()
    {
        EportfolioFreigabe::setAccess(
            Request::get('user_id'),
            Request::get('chapter_id'),
            Request::get('status')
        );
        $status = EportfolioFreigabe::getAccess(
            Request::get('user_id'),
            Request::get('chapter_id')
        );

        //check, if setAccess changed accessibility according to request
        if ($status == filter_var(Request::get('status'), FILTER_VALIDATE_BOOLEAN)) {
            echo MessageBox::success(_('Die Zugriffsrechte wurden geändert.'));
        } else {
            echo MessageBox::error(_('Die Zugriffsrechte konnten nicht geändert werden!'));
        }
        $this->render_nothing();
    }

    /**
     * TOTO refactoring gehört in ePortfoliomodel
     * @param $id
     * @return bool
     */
    public function getSupervisorGroupOfPortfolio()
    {
        $course_id = Context::getId();
        $portfolio = EportfolioModel::findBySeminarId($course_id);

        if ($portfolio->group_id) {
            $portfoliogroup = SupervisorGroup::findOneBySql('Seminar_id = ?', [$portfolio->group_id]) ;
        }

        if ($portfoliogroup) {
            return $portfoliogroup->id;
        } else {
            return false;
        }
    }

    public function addZugriff_action()
    {
        $mp            = MultiPersonSearch::load('selectFreigabeUser');
        $seminar       = new Seminar(Context::getId());
        $eportfolio    = EportfolioModel::findBySeminarId(Context::getId());
        $eportfolio_id = $eportfolio->eportfolio_id;
        $userRole      = 'user';

        foreach ($mp->getAddedUsers() as $userId) {
            $seminar->addMember($userId, $userRole);
            $statement->execute([':id' => Context::getId(), ':userId' => $userId, ':eportfolio_id' => $eportfolio_id]);
        }

        $this->redirect('settings/index/' . Context::getId());
    }

    public function deleteUserAccess_action()
    {
        $user_id       = Request::get('userId');
        $seminar       = new Seminar(Context::getId());
        $eportfolio    = EportfolioModel::findBySeminarId(Context::getId());
        $eportfolio_id = $eportfolio->eportfolio_id;

        $course   = Course::findCurrent();
        $chapters = EportfolioModel::getChapters($course->id);

        foreach ($chapters as $chapter) {
            if (EportfolioFreigabe::hasAccess($user_id, $chapter['id'])) {
                EportfolioFreigabe::setAccess($user_id, $chapter['id'], false);
            }
        }

        $seminar->deleteMember($user_id);

        $this->render_nothing();
    }
}
