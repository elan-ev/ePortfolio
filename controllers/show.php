  <?php

class ShowController extends StudipController {

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;


        $user = get_username();

        $sidebar = Sidebar::Get();
        Sidebar::Get()->setTitle('e-Portfolio von '.$user );

        $widget = new SearchWidget();
        Sidebar::Get()->addWidget($widget);

    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        $this->set_layout($GLOBALS['template_factory']->open('layouts/base.php'));
    }


    public function index_action()
    {
        // echo $GLOBALS["user"]->id;
        $this->userId = $GLOBALS["user"]->id;
        $perm = get_global_perm($GLOBALS["user"]->id);
        if($perm == "dozent"){
          $output = $this->getFirstGroup($GLOBALS["user"]->id);
          if(!$output == '') {
            $this->linkId = $output;
          } else {
            $this->linkId = 'noId';
          }
        }

    }

    public function getMyPortfolios(){

      $db = DBManager::get();
      $userid = $GLOBALS["user"]->id;

      $myportfolios = array();
      $countPortfolios = 0;

      $querygetcid = $db->query("SELECT Seminar_id FROM eportfolio WHERE owner_id = '$userid'")->fetchAll();
      foreach ($querygetcid as $key) {
        array_push($myportfolios, $key[Seminar_id]);
        $countPortfolios++;
      }

      return $myportfolios;

    }

    public function getAccessPortfolio() {

      $db = DBManager::get();
      $userid = $GLOBALS["user"]->id;

      $accessPortfolios = array();
      $querygetcid = $db->query("SELECT Seminar_id FROM eportfolio_user WHERE user_id = '$userid' AND owner = '0'")->fetchAll();
      foreach ($querygetcid as $key) {
        array_push($accessPortfolios, $key[Seminar_id]);
      }

      return $accessPortfolios;
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

    private function getFirstGroup($userId){

      $q = DBManager::get()->query("SELECT seminar_id FROM eportfolio_groups WHERE owner_id = '$userId'")->fetchAll();
      return $q[0][0];

    }

    public function getUserGroups($userId){

      $q = DBManager::get()->query("SELECT seminar_id FROM eportfolio_groups_user WHERE user_id = '$userId'")->fetchAll();
      return $q;

    }
}
