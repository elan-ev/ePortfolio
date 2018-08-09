  <?php

class ajaxsupervisorController extends StudipController {

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->current_plugin;

        //check status and trigger query
        global $perm;
        if(!$perm->have_perm('dozent')){
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
      $query = "SELECT Beschreibung FROM seminare WHERE Seminar_id = :cid";
      $statement = $db->prepare($query);
      $statement->execute(array(':cid'=> $cid));
      return $statement->fetchAll()[0][Beschreibung];

    }

    public function countViewer($cid) {

      $db = DBManager::get();
      $query = "SELECT COUNT(Seminar_id) FROM eportfolio_user WHERE Seminar_id = :cid AND owner = 0";
      $statement = $db->prepare($query);
      $statement->execute(array(':cid'=> $cid));
      echo $statement->fetchAll()[0][0];

    }

    public function getPortfolios($id) {

      $db = DBManager::get();
      $userid = $id;

      $portfolios = array();

      $query = "SELECT Seminar_id FROM eportfolio WHERE owner_id = :userid";
      $statement = $db->prepare($query);
      $statement->execute(array(':userid'=> $userid));
      $querygetcid = $statement->fetchAll();
      
      foreach ($querygetcid as $key) {
        array_push($portfolios, $key[Seminar_id]);
      }

      return $portfolios;

    }


}
