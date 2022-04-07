<?php

use Mooc\Container;

class ShowsupervisorController extends PluginController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->course_id = Context::getId();

        // Aktuelle Seite
        if ($this->course_id) {
            PageLayout::setTitle(Context::getHeaderLine() . '- ePortfolio Administration');
            Navigation::activateItem('course/eportfolioplugin');

            $this->course = Course::find($this->course_id);
        }

        if ($this->course) {
            $this->userId = $GLOBALS['user']->id;

            $this->groupId = $this->course->id;
            $this->group = SupervisorGroup::findOneBySQL('seminar_id = ?', [$this->groupId]);

            if (!$this->group) {
                $this->group = SupervisorGroup::create([
                    'id'         => md5(uniqid()),
                    'Seminar_id' => $this->course->id,
                    'name'       => $this->course->name
                ]);
            }

            $this->supervisorGroupId = $this->group->id;
            $this->distributedPortfolios = EportfolioGroupTemplates::getGroupTemplates($this->groupId);
        }
    }

    public function index_action()
    {
        object_set_visit(Context::getId(), 'sem');

        Navigation::activateItem('/course/eportfolioplugin/supervision');

        $this->member = EportfolioModel::getGroupMembers($this->course_id);
        $this->portfolios = EportfolioModel::getPortfolioVorlagen();

        /* remove archived portfolios from list */
        $this->portfolios = array_filter($this->portfolios, function ($portfolios) {
            return @empty(EportfolioArchive::find($portfolios->id));
        });

        $this->portfolioChapters = EportfolioModel::getAnzahlAllerKapitel($this->groupId);

        // Sidebar
        /*
        if ($GLOBALS['perm']->have_perm('root')) {
            $sidebar = Sidebar::get();

            $navcreate = new LinksWidget();
            $navcreate->setTitle(_('Aktionen'));
            $navcreate->addLink(
                'Blockzuordnungen reparieren',
                $this->url_for('showsupervisor/fixportfolio'),
                Icon::create('admin')
            );

            $sidebar->addWidget($navcreate);
        }
        */
    }

    /*
    public function fixportfolio_action()
    {
        VorlagenCopy::fixBlocks($this->course_id);

        PageLayout::postSuccess('Blöcke wurden zugeordnet/korrigiert.');

        $this->redirect('showsupervisor');
    }
    */

    public function createportfolio_action($master)
    {
        $this->seminar_list = [];
        $this->masterid = $master;

        $this->groupId = $this->course->id;
        $this->group = SupervisorGroup::findOneBySQL('seminar_id = ?', [$this->groupId]);

        $user_found = false;
        foreach($this->group->user as $rel) {
            if ($rel->user_id == $GLOBALS['user']->id) {
                $user_found = true;
            }
        }

        if (!$user_found) {
            PageLayout::postError('Sie sind nicht in der Gruppe der Berechtigten für die '
                . 'Portfolioarbeit und können deshalb keine Vorlage verteilen!');
            $this->redirect('showsupervisor');
            return;
        }

        $members = EportfolioModel::getGroupMembers($this->course_id);
        /**
         * Jeden User in der Gruppe einzeln behandeln
         * **/

        foreach ($members as $member) {

            /**
             * Überprüfen ob es für den Nutzer schon ein Portfolio-Seminar gibt
             * **/
            $portfolio = EportfolioModel::findOneBySQL('owner_id = ? AND group_id = ?', [
                $member->id, $this->course_id
            ]);

            if (!empty($portfolio->Seminar_id)) {

                /**
                 * Wenn ja: Template einfach wie gehabt Kopieren
                 * bzw. in die seminar_list anfügen und später triggern
                 * **/

                array_push($this->seminar_list, $portfolio->Seminar_id);

            } else {

                /**
                 * Wenn nein: Neues Portfolio-Seminar für den User anlegen und ausgewähltes Template kopieren
                 * in seminar_list anfügen
                 * **/
                $portfolio_id_add = EportfolioModel::createPortfolioForUser(
                    $this->supervisorGroupId,
                    $member->id,
                    $this->dispatcher->current_plugin
                );
                array_push($this->seminar_list, $portfolio_id_add);

            }

        }

        $template_entry = EportfolioGroupTemplates::find([$this->course_id, $this->masterid]);

        if (!$template_entry) {
            $template_entry = EportfolioGroupTemplates::create(
                [
                    'group_id'   => $this->course_id,
                    'Seminar_id' => $this->masterid,
                    'verteilt_durch' => $GLOBALS["user"]->id,
                ]
            );
        }

        // make sure that every eportfolio has the members of the portfolio group as tutor
        $add_teacher = DBManager::get()->prepare("REPLACE INTO
            seminar_user (Seminar_id, user_id, status)
            VALUES (?, ?, 'dozent')");
        $add_tutor = DBManager::get()->prepare("REPLACE INTO
            seminar_user (Seminar_id, user_id, status)
            VALUES (?, ?, 'tutor')");
        $tutors = $this->group->user->toArray();

        $owner_teacher = DBManager::get()->fetchColumn('SELECT user_id FROM seminar_user WHERE status = "dozent" and seminar_id = ?', [$master]);

        // Add Supervervisors
        foreach($tutors as $tutor) {
            if($tutor['user_id'] !== $owner_teacher) {
                $add_teacher->execute([$master, $tutor['user_id']]);
            }
        }
        foreach ($this->seminar_list as $semid) {
            foreach ($tutors as $supervisor) {
                $add_tutor->execute([$semid, $supervisor['user_id']]);
            }
        }

        VorlagenCopy::copyCourseware(
            Seminar::GetInstance($this->masterid),
            $this->seminar_list,
            $this->supervisorGroupId
        );
        EportfolioActivity::addVorlagenActivity($this->course_id, User::findCurrent()->id);

        PageLayout::postSuccess(_('Vorlage wurde verteilt.'));
        $this->redirect('showsupervisor?cid=' . $this->course_id);
    }

    public function updatevorlage_action($vorlage_id)
    {
        if (!$GLOBALS['perm']->have_studip_perm('dozent', $vorlage_id)) {
            throw new AccessDeniedException(_('Sie haben keine Berechtigung die Vorlage zu bearbeiten'));
        }

        $seminar = Seminar::getInstance($vorlage_id);

        if (Request::submitted('updatevorlage')) {
            $sem_name = strip_tags(Request::get('name'));
            $sem_description = strip_tags(Request::get('description'));

            $seminar->name = $sem_name;
            $seminar->description = $sem_description;

            $seminar->store();

            PageLayout::postSuccess(sprintf(_('Vorlage "%s" wurde aktualisiert.'), htmlReady($sem_name)));

            $this->response->add_header('X-Dialog-Close', '1');
            $this->render_nothing();
        } else {
            $this->template_name = $seminar->name;
            $this->template_description = $seminar->description;
        }
    }

    public function memberdetail_action($group_id, $user_id)
    {
        $this->group_id = $group_id;
        $this->group_title = Course::findCurrent()->name;

        $this->user = new User($user_id);
        $this->user_id = $user_id;

        $this->portfolio_id = EportfolioModel::getPortfolioIdOfUserInGroup($user_id, $group_id);
        $this->templates = EportfolioGroupTemplates::getUserChapterInfos($group_id, $this->portfolio_id);

        $this->portfolioSharedChapters = EportfolioFreigabe::sharedChapters($this->course_id, $this->templates);
        $this->chapterCount = EportfolioModel::getAnzahlAllerKapitel($this->groupId);
        $this->notesCount = EportfolioUser::getAnzahlNotizen($this->portfolio_id);

        // check, if current user is in supervisor group
        $this->userIsSupervisor = sizeof($this->group->user->findBy('user_id', $GLOBALS['user']->id)) ? true : false;

        /**
         * get all deadlines, shareDates from PortfolioInformation and titles and correct number of chapters from chapterInformation
         * reindex both arrays so both arrays can be combined
         */
        $portfolioInformation = EportfolioUser::getPortfolioInformationInGroup($group_id, $this->portfolio_id);
        $portfolioInformation = array_column($portfolioInformation, null, 'id');

        $chapters = EportfolioModel::getChapterInformation($this->portfolio_id);
        $chapters = array_column($chapters, null, 'id');


        $this->chapterInfos = [];
        foreach ($chapters as $key => $val) {
            $this->chapterInfos[$key] = array_key_exists($key, $portfolioInformation) ? array_merge($val, $portfolioInformation[$key]) : $val;

            if (is_array($this->templates[$this->chapterInfos[$key]['template_id']])) {
                foreach ($this->templates[$this->chapterInfos[$key]['template_id']] as $template_chapter) {
                    if ($template_chapter['id'] == $key) {
                        $this->chapterInfos[$key]['template_title'] = $template_chapter['title'];
                    }
                }
            }
        }

        $this->lastVisit = object_get_visit(Context::getId(), 'sem');
    }

    public function activityfeed_action()
    {
        Navigation::activateItem('/course/eportfolioplugin/portfoliofeed');
        $this->activities = EportfolioActivity::getActivitiesForGroup($this->course_id);
        $this->countActivities = sizeof(EportfolioActivity::newActivities($this->course_id) ?: []);

        // Sidebar
        $sidebar = Sidebar::get();

        $navcreate = new LinksWidget();
        $navcreate->setTitle(_('Aktionen'));
        $navcreate->addLink(
            'Aktivitäten exportieren',
            $this->url_for('showsupervisor/export_activities'),
            Icon::create('community+add')
        );

        $sidebar->addWidget($navcreate);
    }

    public function export_activities_action()
    {
        $activities = EportfolioActivity::findBySQL('group_id = ?
            ORDER BY user_id, mk_date ASC', [$this->course_id]);

        $course = Course::find($this->course_id);

        $stmt = DBManager::get()->prepare("SELECT title FROM mooc_blocks
            WHERE id = ?");

        define('NL', "\n");  // if we need to change the newline-marker
        define('TR', ';');   // divider for entrys

        // set correct header
        session_write_close();
        ob_end_clean();

        header("Content-type: text/comma-separated-values; charset=utf-8");
        header("Content-Disposition: attachment; filename=Aktivitaeten_Portfolio_"
            . urlencode($course->getFullname('number-name-semester')));
        header("Pragma: public");

        echo "\xEF\xBB\xBF";   // byte order marker for utf-8

        echo "NutzerIn" . TR;
        echo "Portfolio" . TR;
        echo "Atkivität" . TR;
        echo "Kapitel" . TR;
        echo "Datum" . NL;

        $cache = [];

        $translations = [
            'freigabe'          => 'Freigabe erstellt',
            'freigabe-entfernt' => 'Freigabe entfernt',
            'supervisor-answer' => 'Anwort von Supervisor/in',
            'supervisor-notiz'  => 'Notiz an Supervisor/in',
            'UserDidPostNotiz'  => 'Notiz an Supervisor/in',
            'vorlage-erhalten'  => 'Vorlage erhalten',
            'vorlage-verteilt'  => 'Vorlage verteilt'
        ];

        foreach ($activities as $activity) {
            $row = [];

            if (!$cache['usernames'][$activity->user_id]) {
                $cache['usernames'][$activity->user_id] = get_fullname($activity->user_id);
            }

            $row[] = $cache['usernames'][$activity->user_id];

            if ($activity->type == 'vorlage-verteilt') {
                if (!$cache['portfolios'][$activity->group_id]) {
                    $cache['portfolios'][$activity->group_id] = Course::find($activity->group_id);
                }

                $row[] = $cache['portfolios'][$activity->group_id]->name;
            } else {
                if (!$cache['portfolios'][$activity->eportfolio_id]) {
                    $cache['portfolios'][$activity->eportfolio_id] = Course::find($activity->eportfolio_id);
                }

                $row[] = $cache['portfolios'][$activity->eportfolio_id]->name;
            }

            $row[] = $translations[$activity->type] ?: $activity->type;

            if (!$cache['chapters'][$activity->block_id]) {
                $stmt->execute([$activity->block_id]);
                $cache['chapters'][$activity->block_id] = $stmt->fetchColumn();
            }

            $row[] = $cache['chapters'][$activity->block_id]->name;

            $row[] = date('d.m.Y, H:i:s', $activity->mk_date);

            echo '"' . implode('"' . TR . '"', $row) . '"';
            echo NL;
        }


        die;
    }

    public function templatedates_action($group_id, $template_id)
    {
        $this->group_id = $group_id;
        $this->template_id = $template_id;

        $timestamp = EportfolioGroupTemplates::getDeadline($group_id, $template_id);
        $this->abgabe = date('Y-m-d', $timestamp ?: time());
    }

    public function settemplatedates_action($group_id, $template_id)
    {
        if (!Request::get('begin')) {
            $timestamp = 0;
        } else {
            $dtime = DateTime::createFromFormat("Y-m-d", Request::get('begin'));
            $timestamp = $dtime->getTimestamp();
        }
        EportfolioGroupTemplates::setDeadline($group_id, $template_id, $timestamp);
        $this->redirect('showsupervisor?cid=' . $group_id);
    }

    public function createlateportfolio_action($group_id, $user_id, $userPortfolioId)
    {

        /**
         *     1.   Hat ein nutzer überhaput schon ein Portfolio in der Gruppe ?
         *          Wenn nicht, muss eins erstellt werden.
         *     2.   Welche Templates fehlem dem Nutzer ? Diese müssen dann verteilt werden.
         **/

        if (!$userPortfolioId) {
            /**
             * Der User hat noch kein Portfilio
             * in die das Template importiert werden kann
             * **/
            $userPortfolioId = EportfolioModel::createPortfolioForUser(
                $this->supervisorGroupId, $user_id, $this->dispatcher->current_plugin
            );

            $template_list_not_shared = EportfolioGroupTemplates::getGroupTemplates($group_id);
            //array_push($portfolio_list, $portfolio_id_in_array);
        } else {
            /**
             * Welche Templates wurden dem Nutzer noch nicht Verteilt?
             * **/
            $template_list_not_shared = EportfolioModel::getNotSharedTemplatesOfUserInGroup(
                $group_id, $user_id, $userPortfolioId
            );
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
            VorlagenCopy::copyCourseware(new Seminar($current_template_id), [$user_id => $userPortfolioId]);

            /**
             * TODO:
             * Hier vielleicht einen neuen Aktivitättypen einführen
             * für das nachträgliche Verteilen von Templates
             * z.B. Es wurden 5 Templates nachträglich an User XY verteilt
             * **/
            EportfolioActivity::addVorlagenActivity($group_id, User::findCurrent()->id);
        }

        $this->redirect('showsupervisor?cid=' . $this->course_id);
    }

    public function deleteportfolio_action($portfolio_id, $source = 'seminar')
    {
        //don't delete master portfolio if already distributed
        if (!EportfolioGroupTemplates::isDistributed($portfolio_id)) {
            $portfolio = Seminar::getInstance($portfolio_id);
            $portfolio->delete();
            $this->deleteCourseware($portfolio_id);

            PageLayout::postSuccess(_('Die Vorlage wurde gelöscht.'));
        }

        if ($source == 'profile') {
            $this->redirect('show');
        } else {
            $this->redirect('showsupervisor?cid=' . $this->course_id);
        }
    }

    private function deleteCourseware($portfolio_id)
    {
        $deleteFields = DBManager::get()->prepare('DELETE FROM mooc_fields
            WHERE block_id IN (SELECT id FROM mooc_blocks WHERE seminar_id = ?)'
        );
        $deleteFields->execute([$portfolio_id]);

        $statement = DBManager::get()->prepare('DELETE FROM mooc_blocks
            WHERE seminar_id = ?'
        );
        $statement->execute([$portfolio_id]);
    }
}
