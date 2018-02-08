<?php

class SupervisorgroupController extends StudipController {

  var $id = null;

  public function __construct($dispatcher){
    parent::__construct($dispatcher);
    $this->plugin = $dispatcher->plugin;

    $this->createSidebar();
    $this->checkGetId();
  }

  public function index_action(){
    $group = new Supervisorgroup($this->id);
    $this->title = $group->getName();

    $this->mp = MultiPersonSearch::get('supervisorgroupSelectUsers')
      ->setLinkText(_('Supervisoren hinzufügen'))
      ->setTitle(_('Personen zur Supervisorgruppe hinzufügen'))
      ->setSearchObject(new StandardSearch('user_id'))
      ->setJSFunctionOnSubmit()
      ->setExecuteURL(URLHelper::getLink('plugins.php/eportfolioplugin/supervisorgroup/addUser', array()))
      ->render();
  }

  private function createSidebar(){
    $sidebar = Sidebar::Get();
    Sidebar::Get()->setTitle('Supervisorgruppen');

    $navcreate = new LinksWidget();
    $navcreate->setTitle('Supervisorgruppen');
    $attr = array("onclick"=>"modalneueGruppe()");
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

  public function addUser_action($groupId, $userId){
    $group = new Supervisorgroup($groupId);
    $mp = MultiPersonSearch::load('supervisorgroupSelectUsers');
    foreach ($mp->getAddedUsers() as $key) {
      $group->addUser($key);
    }
  }


}
