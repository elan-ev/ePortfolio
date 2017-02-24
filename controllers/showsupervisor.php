  <?php

class ShowsupervisorController extends StudipController {

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;
        $user = get_username();

        //userData for Modal

        //sidebar
        $sidebar = Sidebar::Get();
        Sidebar::Get()->setTitle('Supervisionsansicht');

        $nav = new LinksWidget();
        $nav->setTitle(_('Supervisionsgrupppen'));
        $groups = $this->getGroups($GLOBALS["user"]->id);
        foreach ($groups as $key) {
          $seminar = new Seminar($key);
          $name = $seminar->getName();
          if($_GET['id'] == $key){
            $attr = array('class' => 'active-link');
          } else {
            $attr = array('class' => '');
          }

          $nav->addLink($name, "showsupervisor?id=".$key, null, $attr);
        }

        $navcreate = new LinksWidget();
        $navcreate->setTitle('Erstellen');

        $attr = array("data-toggle"=>"modal", "data-target" => "#myModal");
        $navcreate->addLink("Neue Gruppe anlegen", "#", "", $attr);

        $sidebar->addWidget($nav);
        $sidebar->addWidget($navcreate);

    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        $this->set_layout($GLOBALS['template_factory']->open('layouts/base.php'));
    }

    public function index_action()
    {

      if($_GET["create"]){
        $this->createSupervisorGroup($GLOBALS["user"]->id, $_POST["name"], $_POST["description"]);
      }

      $id = $_GET["id"];
      $this->id = $id;

      if(!$id == ''){
        $check = DBManager::get()->query("SELECT owner_id FROM eportfolio_groups WHERE seminar_id = '$id'")->fetchAll();

        //check permission
        if(!$check[0][0] == $GLOBALS["user"]->id){
          throw new AccessDeniedException(_("Sie haben keine Berechtigung"));
        } else {
          $this->groupList = $this->getGroupMember($id);

        }
      } else {

      }

      $this->userid = $GLOBALS["user"]->id;

      //not working MultiPersonSearch
      $mp = MultiPersonSearch::get('eindeutige_id')
        ->setLinkText(_('Person hinzufügen'))
        ->setTitle(_('Person zur Gruppe hinzufügen'))
        ->setExecuteURL($this->url_for('controller'))
        ->render();

      $this->mp = $mp;

      $this->url = $_SERVER['REQUEST_URI'];

    }

    public function getCourseBeschreibung($cid){

      $db = DBManager::get();
      $query = $db->query("SELECT Beschreibung FROM seminare WHERE Seminar_id = '$cid'")->fetchAll();
      return $query[0][Beschreibung];

    }

    public function countViewer($cid) {

      $query = DBManager::get()->query("SELECT  COUNT(Seminar_id) FROM eportfolio_user WHERE Seminar_id = '$cid' AND owner = 0")->fetchAll();
      echo $query[0][0];

    }

    public function createSupervisorGroup($owner, $title, $text) {

      $course = new Seminar();
      $id = $course->getId();
      $course->store();
      $course->addMember($owner, 'dozent', true);

      $edit = new Course($id);
      $edit->visible = 0;
      $edit->store();

      DBManager::get()->query("UPDATE seminare SET Name = '$title', Beschreibung = '$text', status = 142 WHERE Seminar_id = '$id' ");
      DBManager::get()->query("INSERT INTO eportfolio_groups (seminar_id, owner_id) VALUES ('$id', '$owner')");

      echo $id;
      die();

    }

    public function getGroups($id) {

      $q = DBManager::get()->query("SELECT seminar_id FROM eportfolio_groups WHERE owner_id = '$id'")->fetchAll();
      $array = array();
      foreach ($q as $key) {
        array_push($array, $key[0]);
      }
      return $array;

    }

    public function getGroupMember($cid) {

      $q = DBManager::get()->query("SELECT user_id FROM eportfolio_groups_user WHERE Seminar_id = '$cid'")->fetchAll();
      $array = array();
      foreach ($q as $key) {
        array_push($array, $key[0]);
      }
      return $array;

      }


}
