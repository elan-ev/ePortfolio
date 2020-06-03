<?php

use Mooc\Container;

class ShowsupervisorController extends PluginController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->course = Course::find(Context::getId());

        if ($this->course) {
            $this->userId  = $GLOBALS['user']->id;

            $this->groupId = $this->course->id;
            $this->group = EportfolioGroup::findbySQL('seminar_id = :id', [':id' => $this->groupId])[0];

            $this->supervisorGroupId = $this->group->supervisor_group_id;

            $this->distributedPortfolios = EportfolioGroupTemplates::getGroupTemplates($this->groupId);
        }

        // Aktuelle Seite
        PageLayout::setTitle(Context::getHeaderLine() . '- ePortfolio Administration');

        Navigation::activateItem('course/eportfolioplugin');
    }

    public function index_action()
    {
        Navigation::activateItem('/course/eportfolioplugin/supervision');

        $this->member     = EportfolioGroup::getGroupMember($this->group);
        $this->portfolios = Eportfoliomodel::getPortfolioVorlagen();

        /* remove archived portfolios from list */
        $this->portfolios = array_filter($this->portfolios, function($portfolios) use ($id) {
            return @empty(EportfolioArchive::find($portfolios->id));
        });

        $this->portfolioChapters = EportfolioGroup::getAnzahlAllerKapitel($this->groupId);

        EportfolioFreigabe::prune($this->course->id);
    }

    public function createportfolio_action($master)
    {
        $this->seminar_list = [];
        $this->masterid           = $master;
        $this->groupid            = Course::findCurrent()->id;
        $group              = EportfolioGroup::find($this->groupid);

        $members = EportfolioGroup::getGroupMember($group);
        $groupowner        = $group->owner_id;
        $groupname         = new Seminar($this->groupid);
        $supervisorgroupid = EportfolioGroup::getSupervisorGroupId($this->groupid);

        /**
         * Jeden User in der Gruppe einzeln behandeln
         * **/

        foreach ($members as $member) {

            /**
             * Überprüfen ob es für den Nutzer schon ein Portfolio-Seminar gibt
             * **/

            $portfolio_id = EportfolioGroup::getPortfolioIdOfUserInGroup($member->id, $this->groupid);

            if (!empty($portfolio_id)) {

                /**
                 * Wenn ja: Template einfach wie gehabt Kopieren
                 * bzw. in die seminar_list anfügen und später triggern
                 * **/

                array_push($this->seminar_list, $portfolio_id);

            } else {

                /**
                 * Wenn nein: Neues Portfolio-Seminar für den User anlegen und ausgewähltes Template kopieren
                 * in seminar_list anfügen
                 * **/

                $portfolio_id_add = EportfolioModel::createPortfolioForUser($this->groupid, $member->id, $this->dispatcher->current_plugin);
                array_push($this->seminar_list, $portfolio_id_add);

            }

        }
        EportfolioGroup::createTemplateForGroup($this->groupid, $this->masterid, $GLOBALS["user"]->id);

        VorlagenCopy::copyCourseware(new Seminar($this->masterid), $this->seminar_list);
        EportfolioActivity::addVorlagenActivity($this->groupid, User::findCurrent()->id);

        PageLayout::postMessage(MessageBox::success('Vorlage wurde verteilt.'));
        $this->redirect('showsupervisor?cid=' . $this->groupid);
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

        $this->user           = new User($user_id);
        $this->user_id  = $user_id;

        $this->portfolio_id = EportfolioGroup::getPortfolioIdOfUserInGroup($user_id, $group_id);
        $this->templates  = EportfolioGroupTemplates::getUserChapterInfos($group_id, $this->portfolio_id);

        $this->portfolioSharedChapters = EportfolioUser::portfolioSharedChapters($this->portfolio_id, $this->templates);
        $this->chapterCount = EportfolioGroup::getAnzahlAllerKapitel($this->groupId);
        $this->notesCount = EportfolioUser::getAnzahlNotizen($this->portfolio_id);

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
        $group                 = EportfolioGroup::findOneBySQL('Seminar_id = :cid', [':cid' => Request::get('cid')]);
        $this->activities      = $group->getActivities();
        $this->countActivities = $group->getNumberOfNewActivities();
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
            $userPortfolioId = EportfolioModel::createPortfolioForUser($group_id, $user_id, $this->dispatcher->current_plugin);

            $template_list_not_shared = EportfolioGroupTemplates::getGroupTemplates($group_id);
            //array_push($portfolio_list, $portfolio_id_in_array);

        } else {
            /**
             * Welche Templates wurden dem Nutzer noch nicht Verteilt?
             * **/
            $template_list_not_shared = EportfolioModel::getNotSharedTemplatesOfUserInGroup($group_id, $user_id, $userPortfolioId);
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

        $this->redirect('showsupervisor?cid=' . $group_id);
    }

}
