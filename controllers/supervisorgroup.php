<?php

class SupervisorgroupController extends StudipController {

  var $id = null;

  public function __construct($dispatcher){
    parent::__construct($dispatcher);
    $this->plugin = $dispatcher->plugin;
    $this->id = $_GET['id'];
    $this->createSidebar();
    $this->checkGetId();
  }

  public function index_action(){
    $group = new Supervisorgroup($this->id);
    $this->title = $group->getName();
    $this->groupId = $group->getId();

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

  public function addUser_action(){
    $group = new Supervisorgroup($this->id);
    $mp = MultiPersonSearch::load('supervisorgroupSelectUsers');
    foreach ($mp->getAddedUsers() as $key) {
      $group->addUser($key);
    }
  }

  public function deleteUser_action(){
    $groupId = $_POST['groupId'];
    $userId = $_POST['userId'];

    $group = new Supervisorgroup($groupId);
    $group->deleteUser($userId);
  }

  public function newGroup_action(){
    $name = $_POST['groupName'];
    $group = new Supervisorgroup();
    $group->setName($name);
    $group->save();
  }



}
