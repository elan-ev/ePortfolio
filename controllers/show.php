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
      $querygetcid = $db->query("SELECT Seminar_id FROM seminar_user WHERE user_id = '$userid' AND status != 'dozent' AND eportfolio_access != '' ")->fetchAll();
      foreach ($querygetcid as $key) {
        array_push($accessPortfolios, $key[Seminar_id]);
      }

      return $accessPortfolios;
    }

}
