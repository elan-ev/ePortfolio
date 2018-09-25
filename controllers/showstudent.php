<?php

use Mooc\Export\XmlExport;
use Mooc\Import\XmlImport;
use Mooc\Container;
use Mooc\UI\Courseware\Courseware;

# require_once 'plugins_packages/virtUOS/Courseware/controllers/exportportfolio.php';

class ShowstudentController extends StudipController {

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->current_plugin;
        $user = get_username();

        $id = $_GET["cid"];
        $this->sem = Course::findById($id);

        if($this->sem){
            $this->groupid = $id;
            $this->userid = $GLOBALS["user"]->id;
            $this->ownerid = $GLOBALS["user"]->id;

            $this->groupTemplates = EportfolioGroupTemplates::getGroupTemplates($id);
            $this->templistid = $this->groupTemplates;

            $group = EportfolioGroup::findbySQL('seminar_id = :id', array(':id'=> $this->groupid));
            $this->supervisorGroupId = $group[0]->supervisor_group_id;

            //object_set_visit($this->groupid, "portfolio-group");
        }

        # Aktuelle Seite
        PageLayout::setTitle('ePortfolio - Übersicht');

        //sidebar
        $sidebar = Sidebar::Get();

    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if(Course::findCurrent()){
            Navigation::activateItem("course/eportfolioplugin");
        }

    }

    public function index_action()
    {
        $course = Course::findCurrent();
        $id = $course->id;

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

        $this->id = $id;
        $this->userid = $GLOBALS["user"]->id;
        $this->group = EportfolioGroup::find($id);

        //Wenn noch keine POrtfolios verteilt wurden oder nicht mal eine Gruppe existiert:
        //Hinweis an student dass noch keine inhalte verteilt wurden
        if(!$this->group){
            //Wenn noch keine POrtfolios verteilt wurden oder nicht mal eine Gruppe existiert Hinweis an student dass noch keine inhalte verteilt wurden
        }
        //andernfalls auflisten welche vorlagen wann verteilt wurden und direktlink ins portfolio des aktuellen studierenden

    }

}
