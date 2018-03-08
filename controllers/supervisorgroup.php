<?php

class SupervisorgroupController extends StudipController {

  var $id = null;

  public function __construct($dispatcher){
    parent::__construct($dispatcher);
    $this->plugin = $dispatcher->current_plugin;
    $this->id = $_GET['cid'];
    $this->createSidebar();
    $this->checkGetId();
  }

  public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
    }

  public function index_action(){
    $group = new Supervisorgroup($this->id);
    $this->title = $group->getName();
    $this->groupId = $group->getId();
    $this->linkId = $this->id;

    $this->mp = MultiPersonSearch::get('supervisorgroupSelectUsers')
      ->setLinkText(_('Supervisoren hinzufügen'))
      ->setTitle(_('Personen zur Supervisorgruppe hinzufügen'))
      ->setSearchObject(new StandardSearch('user_id'))
      ->setJSFunctionOnSubmit()
      ->setExecuteURL(URLHelper::getLink('plugins.php/eportfolioplugin/supervisorgroup/addUser', array()))
      ->render();

    $this->usersOfGroup = $group->getUsersOfGroup();
  }

  private function createSidebar(){
    $sidebar = Sidebar::Get();
    Sidebar::Get()->setTitle('Supervisorgruppen');

    $navcreate = new LinksWidget();
    $navcreate->setTitle('Supervisorgruppen');
    $attr = array("onclick"=>"showModalNewSupervisorGroupAction()");
    $navcreate->addLink("Neue Gruppe anlegen", "#", "", $attr);

    $navgroups = new LinksWidget();
    $navgroups->setTitle("Supervisorgruppen");
    foreach ($this->getSupervisorgroups() as $group) {
      $navgroups->addLink($group[name], "supervisorgroup?id=".$group[id]);
    }

    $sidebar->addWidget($navcreate);
    $sidebar->addWidget($navgroups);
  }

  private function getSupervisorgroups(){
    return DBManager::get()->query("SELECT * FROM supervisor_group")->fetchAll();
  }

  private function checkGetId(){
    if ($_GET[id] == null) {
      $this->id = $this->getFirstGroupId();
    }
  }

  private function getFirstGroupId(){
    $query = DBManager::get()->query("SELECT id FROM supervisor_group")->fetchAll();
    return $query[0][id];
  }

  public function addUser_action($group){
    $mp = MultiPersonSearch::load('supervisorgroupSelectUsers');
    $group = new SupervisorGroup($group);
    foreach ($mp->getAddedUsers() as $key) {
      $group->addUser($key);
    }
    //$this->render_nothing();
    $this->redirect($this->url_for('showsupervisor/supervisorgroup/'. $group->eportfolio_group->seminar_id), array('cid' => $group->eportfolio_group->seminar_id ));
  }

  public function deleteUser_action(){
    $groupId = $_POST['groupId'];
    $userId = $_POST['userId'];

    $group = new Supervisorgroup($groupId);
    $group->deleteUser($userId);
  }

  public function newGroup_action(){
    $name = $_POST['groupName'];
    Supervisorgroup::newGroup($name);
  }

  public function deleteGroup_action(){
    $id = $_GET['cid'];
    Supervisorgroup::deleteGroup($id);
  }

    function url_for($to = '')
    {
        $args = func_get_args();

        # find params
        $params = array();
        if (is_array(end($args))) {
        $params = array_pop($args);
        }

        # urlencode all but the first argument
        $args = array_map('urlencode', $args);
        $args[0] = $to;

        return PluginEngine::getURL($this->dispatcher->current_plugin, $params, join('/', $args));
    }

}
