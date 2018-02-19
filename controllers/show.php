  <?php

class ShowController extends StudipController {

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;

        $this->userId = $GLOBALS["user"]->id;
        $perm = get_global_perm($GLOBALS["user"]->id);
        $this->perm = $perm;
        if($perm == "dozent"){
          $this->linkId = $output;
          $output = Group::getFirstGroupOfUser($GLOBALS["user"]->id);
          if(!$output == '') {
            $this->linkId = $output;
          } else {
            $this->linkId = '';
          }
        }

        $user = get_username();

        $sidebar = Sidebar::Get();
        Sidebar::Get()->setTitle('ePortfolio von '.$user );

        $navcreate = new LinksWidget();
        $navcreate->setTitle('Navigation');
        $attr = array('onclick' => 'newPortfolioModal()');
        $navcreate->addLink("Eigenes ePortfolio erstellen", "#", null, $attr);
        if ($perm == "dozent") {
          $output = Group::getFirstGroupOfUser($GLOBALS["user"]->id);
          if(!$output == '') {
            $linkIdMenu = $output;
          } else {
            $linkIdMenu = '';
          }
          $navcreate->addLink("Supervisionsansicht", "showsupervisor?id=".$linkIdMenu);
        }
        $sidebar->addWidget($navcreate);

    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        $this->set_layout($GLOBALS['template_factory']->open('layouts/base.php'));
        PageLayout::setTitle('ePortfolio');
    }


    public function index_action()
    {


    }

    public function getMyPortfolios(){

      $db = DBManager::get();
      $userid = $GLOBALS["user"]->id;

      $myportfolios = array();
      $countPortfolios = 0;

      $query = "SELECT Seminar_id FROM eportfolio WHERE owner_id = :userid";
      $statement = $db->prepare($query);
      $statement->execute(array(':userid'=> $userid));
      foreach ($statement->fetchAll() as $key) {
        array_push($myportfolios, $key[Seminar_id]);
        $countPortfolios++;
      }

      return $myportfolios;

    }

    public function getAccessPortfolio() {

      $db = DBManager::get();
      $userid = $GLOBALS["user"]->id;

      $accessPortfolios = array();
      $query = "SELECT Seminar_id FROM eportfolio_user WHERE user_id = :userid AND owner = '0'";
      $statement = $db->prepare($query);
      $statement->execute(array(':userid'=> $userid));
      foreach ($statement->fetchAll() as $key) {
        array_push($accessPortfolios, $key[Seminar_id]);
      }

      return $accessPortfolios;
    }

    public function getTemplates(){

      $semId;
      $seminare = array();

      foreach ($GLOBALS['SEM_TYPE'] as $id => $sem_type){ //get the id of ePortfolio Seminarclass
        if ($sem_type['name'] == 'Portfolio - Vorlage') {
          $semId = $id;
        }
      }

      $db = DBManager::get();
      $query = "SELECT Seminar_id FROM seminare WHERE status = :semId";
      $statement = $db->prepare($query);
      $statement->execute(array(':semId'=> $semId));
      foreach ($statement->fetchAll() as $key) {
        array_push($seminare, $key[Seminar_id]);
      }

      return $seminare;

    }

    public function getCourseBeschreibung($cid){

      $db = DBManager::get();
      $query = "SELECT Beschreibung FROM seminare WHERE Seminar_id = :cid";
      $statement = $db->prepare($query);
      $statement->execute(array(':cid'=> $cid));
      return $statement->fetchAll()[0][Beschreibung];

    }

    public function getOwnerName($cid){
      $db = DBManager::get();
      $query = "SELECT * FROM eportfolio WHERE Seminar_id = :cid";
      $statement = $db->prepare($query);
      $statement->execute(array(':cid'=> $cid));
      $ownerid = $statement->fetchAll()[0]["owner_id"];
      
      $query = "SELECT * FROM auth_user_md5 WHERE user_id = :ownerid";
      $statement = $db->prepare($query);
      $statement->execute(array(':ownerid'=> $ownerid));
      $result = $statement->fetchAll();
      $name = $result[0]["Vorname"]." ".$result[0]["Nachname"];
      return $name;
    }

    public function countViewer($cid) {

      $db = DBManager::get();
      $query = "SELECT COUNT(Seminar_id) FROM eportfolio_user WHERE Seminar_id = :cid AND owner = 0";
      $statement = $db->prepare($query);
      $statement->execute(array(':cid'=> $cid));
      echo $statement->fetchAll()[0][0];

    }

    public function newvorlage_action(){

      foreach ($GLOBALS['SEM_TYPE'] as $id => $sem_type){ //get the id of ePortfolio Seminarclass
        if ($sem_type['name'] == 'Portfolio - Vorlage') {
          $sem_type_id = $id;
        }
      }

      $userid           = $GLOBALS["user"]->id; //get userid
      $sem_name         = $_POST['name'];
      $sem_description  = $_POST['beschreibung'];

      $sem              = new Seminar();
      $sem->Seminar_id  = $sem->createId();
      $sem->name        = $sem_name;
      $sem->description = $sem_description;
      $sem->status      = $sem_type_id;
      $sem->read_level  = 1;
      $sem->write_level = 1;
      $sem->institut_id = Config::Get()->STUDYGROUP_DEFAULT_INST;
      $sem->visible     = 1;

      $sem_id = $sem->Seminar_id;
      echo $sem_id;

      $sem->addMember($userid, 'dozent');
      $sem->store();

      $eportfolio = new Seminar();
      $eportfolio_id = $eportfolio->createId();
      $db = DBManager::get();
      $query = "INSERT INTO eportfolio (Seminar_id, eportfolio_id, owner_id) VALUES (:sem_id, :eportfolio_id, :userid)";
      $statement = $db->prepare($query);
      $statement->execute(array(':sem_id'=> $sem_id, ':eportfolio_id'=> $eportfolio_id, ':userid'=> $userid)); //table eportfolio
      $query = "INSERT INTO eportfolio_user(user_id, Seminar_id, eportfolio_id, owner) VALUES (:userid, :Seminar_id , :eportfolio_id, 1)";
      $statement = $db->prepare($query);
      $statement->execute(array(':Seminar_id'=> $Seminar_id, ':eportfolio_id'=> $eportfolio_id, ':userid'=> $userid)); //table eportfollio_user

    }

}
