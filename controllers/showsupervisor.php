<?php

use Mooc\Container;

class ShowsupervisorController extends PluginController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->course_id = Context::getId();
        $this->course = Course::find($this->course_id);

        if ($this->course) {
            $this->userId  = $GLOBALS['user']->id;

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

        // Aktuelle Seite
        PageLayout::setTitle(Context::getHeaderLine() . '- ePortfolio Administration');

        Navigation::activateItem('course/eportfolioplugin');
    }

    public function index_action()
    {
        Navigation::activateItem('/course/eportfolioplugin/supervision');

        $this->member     = EportfolioModel::getGroupMembers($this->course_id);
        $this->portfolios = EportfolioModel::getPortfolioVorlagen();

        /* remove archived portfolios from list */
        $this->portfolios = array_filter($this->portfolios, function($portfolios) use ($id) {
            return @empty(EportfolioArchive::find($portfolios->id));
        });

        $this->portfolioChapters = EportfolioModel::getAnzahlAllerKapitel($this->groupId);
    }

    public function createportfolio_action($master)
    {
        $this->seminar_list = [];
        $this->masterid  = $master;

        $members    = EportfolioModel::getGroupMembers($this->course_id);

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
                $portfolio_id_add = EportfolioModel::createPortfolioForUser($this->supervisorGroupId, $member->id, $this->dispatcher->current_plugin);
                array_push($this->seminar_list, $portfolio_id_add);

            }

        }

        // create template for group
        $template_entry                 = new EportfolioGroupTemplates();
        $template_entry->group_id       = $this->course_id;
        $template_entry->Seminar_id     = $this->masterid;
        $template_entry->verteilt_durch = $GLOBALS["user"]->id;
        $template_entry->store();

        VorlagenCopy::copyCourseware(new Seminar($this->masterid), $this->seminar_list);
        EportfolioActivity::addVorlagenActivity($this->course_id, User::findCurrent()->id);

        PageLayout::postMessage(MessageBox::success('Vorlage wurde verteilt.'));
        $this->redirect('showsupervisor?cid=' . $this->course_id);
    }

    public function updatevorlage_action($vorlage_id)
    {
        if (!$GLOBALS['perm']->have_studip_perm('dozent', $vorlage_id)) {
            throw new AccessDeniedException(_("Sie haben keine Berechtigung die Vorlage zu bearbeiten"));
        }

        $seminar = Seminar::getInstance($vorlage_id);

        if(Request::submitted('updatevorlage')) {
            $sem_name        = strip_tags(Request::get('name'));
            $sem_description = strip_tags(Request::get('description'));

            $seminar->name = $sem_name;
            $seminar->description = $sem_description;

            $seminar->store();

            PageLayout::postMessage(MessageBox::success(sprintf(_('Vorlage "%s" wurde angelegt.'), $sem_name)));

            $this->response->add_header('X-Dialog-Close', '1');
            $this->render_nothing();
        } else {
            $this->template_name = $seminar->name;
            $this->template_description = $seminar->description;
        }
    }

    public function memberdetail_action($group_id, $user_id)
    {
        $this->group_id     = $group_id;
        $this->group_title  = Course::findCurrent()->name;

        $this->user         = new User($user_id);
        $this->user_id      = $user_id;

        $this->portfolio_id = EportfolioModel::getPortfolioIdOfUserInGroup($user_id, $group_id);
        $this->templates    = EportfolioGroupTemplates::getUserChapterInfos($group_id, $this->portfolio_id);

        $this->portfolioSharedChapters = EportfolioFreigabe::sharedChapters($this->course_id, $this->templates);
        $this->chapterCount = EportfolioModel::getAnzahlAllerKapitel($this->groupId);
        $this->notesCount   = EportfolioUser::getAnzahlNotizen($this->portfolio_id);

        // check, if current user is in supervisor group
        $this->userIsSupervisor = sizeof($this->group->user->findBy('user_id', $GLOBALS['user']->id)) ? true : false;

        /**
         * get all deadlines, shareDates from PortfolioInformation and titles and correct number of chapters from chapterInformation
         * reindex both arrays so both arrays can be combined
         */
        $portfolioInformation = EportfolioUser::getPortfolioInformationInGroup($group_id, $this->portfolio_id, $GLOBALS['user']->id);
        $portfolioInformation = array_column($portfolioInformation, null, 'id');

        $chapters = EportfolioModel::getChapterInformation($this->portfolio_id);
        $chapters = array_column($chapters, null, 'id');


        $this->chapterInfos = array();
        foreach($chapters as $key => $val) {
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
        /* object_set_visit() has to be called twice, so the current time will be moved into last_visited
        so that the red asteriks will only be shown on the first visit */
        object_set_visit(Context::getId(), 'sem');
        object_set_visit(Context::getId(), 'sem');
    }

    public function activityfeed_action()
    {
        Navigation::activateItem('/course/eportfolioplugin/portfoliofeed');
        $this->activities      = EportfolioActivity::getActivitiesForGroup($this->course_id);
        $this->countActivities = sizeof(EportfolioActivity::newActivities($this->seminar_id) ?: []);
    }

    public function templatedates_action($group_id, $template_id)
    {
        $this->group_id    = $group_id;
        $this->template_id = $template_id;

        $timestamp    = EportfolioGroupTemplates::getDeadline($group_id, $template_id);
        $this->abgabe = date('Y-m-d', $timestamp ?: time());
    }

    public function settemplatedates_action($group_id, $template_id)
    {
        if (!Request::get('begin')) {
            $timestamp = 0;
        } else {
            $dtime     = DateTime::createFromFormat("Y-m-d", Request::get('begin'));
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

    public function activitydownload_action()
    {
        $blanks = array("\t", "\t");
        $f = fopen('php://output', 'w');
        $members     = EportfolioModel::getGroupMembers($this->course_id);

        fputcsv($f, array("Aktivitätsübersicht vom " . date('d.m.Y - H:i:s', time())), ',');
        fputcsv($f, $blanks, ',');

        foreach ($members as $user) {
            $activities = DBManager::get()->fetchAll("SELECT * FROM `eportfolio_activities`
                                                    WHERE group_id = 'd0cd791307eaf4852573a26f89c90e76'
                                                    AND user_id = 'e7a0a84b161f3e8c09b4a0a2e8a58147'
                                                    AND type = 'freigabe'",
                                                    [":group_id" => $this->course_id, ":user_id" => $user['user_id']]);
                                                    

            $user_info = array($user['Vorname'] . " " . $user['Nachname'] . " (" . $user['username'] . ")");
            fputcsv($f, $user_info, ',');
            
            $header = array("Datum", "Block");
            fputcsv($f, $header, ',');
            
            foreach ($activities as $activity) {
                $activity_info = array(date('d.m.Y - H:i:s', $activity['mk_date']), Mooc\DB\Block::find($activity['block_id'])->title . " wurde freigegeben");
                fputcsv($f, $activity_info, ',');
            }
            
            fputcsv($f, $blanks, ',');
        }

        $this->set_content_type('application/csv');
        $this->response->add_header(
            'Content-disposition',
            'attachment;' . encode_header_parameter('filename', "activities.csv")
        );
        $this->response->add_header('Content-Length', filesize($f));
        $this->render_text(file_get_contents($f));
    }
}
