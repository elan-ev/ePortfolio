  <?php

class ajaxsupervisorController extends StudipController {

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;

        //check status and trigger query
        $perm = get_global_perm($GLOBALS["user"]->id);
        if(!$perm == "dozent"){
            throw new Exception("Not Allowed");
        } else {
          $code = $this->getPortfolios($_GET["userId"]);
          echo json_encode($code);
        }

    }

    public function before_filter(&$action, &$args)
    {

    }

    public function index_action()
    {



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

    public function getPortfolios($id) {

      $db = DBManager::get();
      $userid = $id;

      $portfolios = array();

      $querygetcid = $db->query("SELECT Seminar_id FROM eportfolio WHERE owner_id = '$userid'")->fetchAll();
      foreach ($querygetcid as $key) {
        array_push($portfolios, $key[Seminar_id]);
      }

      return $portfolios;

    }


}
