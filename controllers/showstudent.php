<?

use Mooc\Container;

class ShowstudentController extends StudipController
{

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->current_plugin;

        $this->course = Course::find(Context::getId());

        if ($this->sem) {
            $this->groupid = Request::get('cid');
            $this->userid  = $GLOBALS["user"]->id;

            $this->groupTemplates = EportfolioGroupTemplates::getGroupTemplates(Request::get('cid'));
            $this->templistid     = $this->groupTemplates;

            $group                   = EportfolioGroup::findbySQL('seminar_id = :id', [':id' => $this->groupid]);
            $this->supervisorGroupId = $group[0]->supervisor_group_id;
        }

        PageLayout::setTitle(_('ePortfolio - Übersicht'));
    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (Course::findCurrent()) {
            Navigation::activateItem("course/eportfolioplugin");
        }

    }

    public function index_action()
    {
        $course = Course::findCurrent();
        $id     = $course->id;

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

        $this->id               = $id;
        $this->userid           = $GLOBALS["user"]->id;
        $this->group            = EportfolioGroup::find($id);
        $this->link_eportfolios = URLHelper::getLink('plugins.php/eportfolioplugin/show');
        $this->link_courseware  = URLHelper::getLink('plugins.php/courseware/courseware', ['cid' => EportfolioGroup::getPortfolioIdOfUserInGroup($this->userid, $this->id)]);

        //Wenn noch keine POrtfolios verteilt wurden oder nicht mal eine Gruppe existiert:
        //Hinweis an student dass noch keine inhalte verteilt wurden
        if (!$this->group) {
            echo "Es wurden noch keine Portfolios...";
            //Wenn noch keine POrtfolios verteilt wurden oder nicht mal eine Gruppe existiert Hinweis an student dass noch keine inhalte verteilt wurden
        } else {
            $this->portfolio_id   = EportfolioGroup::getPortfolioIdOfUserInGroup($this->userid, $id);
            $this->groupTemplates = EportfolioGroupTemplates::getGroupTemplates($id);
            if (!$this->groupTemplates) {
                $this->isThereAnyTemplate = false;
            } else {
                $this->isThereAnyTemplate = true;
            }
        }
        //andernfalls auflisten welche vorlagen wann verteilt wurden und direktlink ins portfolio des aktuellen studierenden
    }
}
