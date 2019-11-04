<?

use Mooc\Container;

class ShowsupervisorController extends StudipController
{

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

        // Aktuelle Seite
        PageLayout::setTitle(Context::getHeaderLine() . '- ePortfolio Administration');

        Navigation::activateItem('course/eportfolioplugin');
    }

    public function index_action()
    {
        Navigation::activateItem('/course/eportfolioplugin/supervision');

        //berechtigung prüfen (group-owner TODO:refactoring //ggf das hier nur für Supervisor,
        //das würde dann aber schon in der Pluginklasse passieren
        /**
         *if(!$id == ''){
         *    $query = "SELECT owner_id FROM eportfolio_groups WHERE seminar_id = :id";
         *    $statement = DBManager::get()->prepare($query);
         *    $statement->execute(array(':id'=> $id));
         *    $check = $statement->fetchAll();
         *
         *    //check permission
         *    if(!$check[0][0] == $GLOBALS["user"]->id){
         *      throw new AccessDeniedException(_("Sie haben keine Berechtigung"));
         *    }
         *}
         */

        $this->id     = $this->course->id;
        $this->userid = $GLOBALS["user"]->id;
        $this->group  = EportfolioGroup::find($this->course->id);

        //noch kein Portfoliogruppeneintrag für dieses Seminar vorhanden: Gruppe erstellen
        if (!$this->group) {
            EportfolioGroup::newGroup($this->userid, $this->course->id);
        }
        $this->courseName = $this->course->name;
        $this->member     = EportfolioGroup::getGroupMember($this->course->id);

        $this->portfolios = Eportfoliomodel::getPortfolioVorlagen();
    }

    public function countViewer($cid)
    {
        $query     = "SELECT  COUNT(Seminar_id) FROM eportfolio_user WHERE Seminar_id = :cid AND owner = 0";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':cid' => $cid]);
        echo $statement->fetchAll()[0][0];

    }

    public function getChapters($id)
    {
        $query     = "SELECT title, id FROM mooc_blocks WHERE seminar_id = :id AND type = 'Chapter' ORDER BY position ASC";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':id' => $id]);
        return $statement->fetchAll();
    }

    public function generateRandomString($length = 32)
    {
        $characters       = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString     = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function createportfolio_action($master)
    {
        $this->seminar_list = [];
        $masterid           = $master;
        $groupid            = Course::findCurrent()->id;
        $group              = EportfolioGroup::find($groupid);

        $members = EportfolioGroup::getGroupMember($groupid);
        $groupowner        = $group->owner_id;
        $groupname         = new Seminar($groupid);
        $supervisorgroupid = EportfolioGroup::getSupervisorGroupId($groupid);

        /**
         * Jeden User in der Gruppe einzeln behandeln
         * **/

        foreach ($members as $member) {

            /**
             * Überprüfen ob es für den Nutzer schon ein Portfolio-Seminar gibt
             * **/

            $portfolio_id = EportfolioGroup::getPortfolioIDsFromUserinGroup($groupid, $member->id);

            if (!empty($portfolio_id)) {

                /**
                 * Wenn ja: Template einfach wie gehabt Kopieren
                 * bzw. in die seminar_list anfügen und später triggern
                 * **/

                $portfolio_id_add = $portfolio_id[0];
                array_push($this->seminar_list, $portfolio_id_add);

            } else {

                /**
                 * Wenn nein: Neues Portfolio-Seminar für den User anlegen und ausgewähltes Template kopieren
                 * in seminar_list anfügen
                 * **/

                $portfolio_id_add = EportfolioModel::createPortfolioForUser($groupid, $member->id, $this->dispatcher->current_plugin);
                array_push($this->seminar_list, $portfolio_id_add);

            }

        }

        EportfolioGroup::createTemplateForGroup($groupid, $masterid, $GLOBALS["user"]->id);

        $this->masterid = $masterid;
        $this->groupid  = $groupid;

        VorlagenCopy::copyCourseware(new Seminar($masterid), $this->seminar_list);
        EportfolioActivity::addVorlagenActivity($groupid, User::findCurrent()->id);

        PageLayout::postMessage(MessageBox::success('Vorlage wurde verteilt.'));

        $this->redirect('showsupervisor?cid=' . $groupid);
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

    public function addAsFav_action($group_id, $template_id)
    {
        EportfolioGroup::markTemplateAsFav($group_id, $template_id);
        $this->redirect('showsupervisor?cid=' . $group_id);
    }

    public function deleteAsFav_action($group_id, $template_id)
    {
        EportfolioGroup::deletetemplateAsFav($group_id, $template_id);
        $this->redirect('showsupervisor?cid=' . $group_id);
    }

    public function memberdetail_action($group_id, $user_id)
    {
        $this->portfolio_id = EportfolioGroup::getPortfolioIdOfUserInGroup($user_id, $group_id);
        $this->chapters     = Eportfoliomodel::getChapters($this->portfolio_id);
        $this->group_id     = $group_id;
        $this->group_title  = Course::findCurrent()->name;

        $user           = new User($user_id);
        $this->user     = $user;
        $this->user_id  = $user_id;
        $this->vorname  = $user['Vorname'];
        $this->nachname = $user['Nachname'];

        $this->AnzahlFreigegebenerKapitel = EportfolioGroup::getAnzahlFreigegebenerKapitel($user_id, $group_id);
        $this->AnzahlAllerKapitel         = EportfolioGroup::getAnzahlAllerKapitel($group_id);
        $this->GesamtfortschrittInProzent = EportfolioGroup::getGesamtfortschrittInProzent($user_id, $group_id);
        $this->AnzahlNotizen              = EportfolioGroup::getAnzahlNotizen($user_id, $group_id);
        $this->templates                  = EportfolioGroupTemplates::getGroupTemplates($group_id);
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

    public function createlateportfolio_action($group_id, $user_id)
    {

        /**
         *     1.   Hat ein nutzer überhaput schon ein Portfolio in der Gruppe ?
         *          Wenn nicht, muss eins erstellt werden.
         *     2.   Welche Templates fehlem dem Nutzer ? Diese müssen dann verteilt werden.
         **/

        $portfolio_id = EportfolioGroup::getPortfolioIDsFromUserinGroup($group_id, $user_id);

        if (!$portfolio_id) {
            /**
             * Der User hat noch kein Portfilio
             * in die das Template importiert werden kann
             * **/
            $portfolio_id = EportfolioModel::createPortfolioForUser($group_id, $user_id, $this->dispatcher->current_plugin);
            $portfolio_id = $portfolio_id;

            $template_list_not_shared = EportfolioGroupTemplates::getGroupTemplates($group_id);
            //array_push($portfolio_list, $portfolio_id_in_array);

        } else {

            $portfolio_id = $portfolio_id[0];
            /**
             * Welche Templates wurden dem Nutzer noch nicht Verteilt?
             * **/
            $template_list_not_shared = EportfolioModel::getNotSharedTemplatesOfUserInGroup($group_id, $user_id, $portfolio_id);
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
            VorlagenCopy::copyCourseware(new Seminar($current_template_id), [$user_id => $portfolio_id]);

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
