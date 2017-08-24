  <?php

class CreateController extends StudipController {

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;

    }

    public function before_filter(&$action, &$args){

    }


    public function index_action()
    {

        // function generateRandomString($length = 32) {
        //   $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        //   $charactersLength = strlen($characters);
        //   $randomString = '';
        //   for ($i = 0; $i < $length; $i++) {
        //       $randomString .= $characters[rand(0, $charactersLength - 1)];
        //     }
        //   return $randomString;
        // }
        //
        // $userid = $GLOBALS["user"]->id;
        // $Seminar_id = generateRandomString();
        // $eportfolio_id = generateRandomString();
        // $Institut_id = "7a4f19a0a2c321ab2b8f7b798881af7c";
        // $VeranstaltungsNummer = "25252525";
        // $name = $_POST[name];
        // $status = 124;
        // $Beschreibung = $_POST[text];
        // $Lesezugriff = 1;
        // $Schreibzugriff = 1;
        // $start_time = 1459461600;
        // $duration_time = 0;
        //
        // $statususer = "dozent";
        // $bind_calendar = 1;
        // $visibleuser = "yes";
        // $position = 0;
        // $gruppe = 5;
        // $notification = 0;
        // $bind_calendar = 1;
        //
        // $db = DBManager::get();
        // $result = $db->query("INSERT INTO seminare (Seminar_id, VeranstaltungsNummer, Institut_id, Name, status, Beschreibung, Lesezugriff, Schreibzugriff, start_time, duration_time, mkdate, chdate) VALUES ('$Seminar_id', '$VeranstaltungsNummer', '$Institut_id', '$name', '$status', '$Beschreibung', '$Lesezugriff', '$Schreibzugriff', '$start_time', '$duration_time', 'UNIX_TIMESTAMP()', 'UNIX_TIMESTAMP()'); ");
        // $resultuser = $db->query("INSERT INTO seminar_user (Seminar_id, user_id, status, position, gruppe, notification, visible, bind_calendar, mkdate) VALUES ('$Seminar_id', '$userid', '$statususer', '$position', '$gruppe', '$notification', '$visibleuser', '$bind_calendar', 'UNIX_TIMESTAMP()');");
        //
        // $result_eportfolioTable = $db->query("INSERT INTO eportfolio (Seminar_id, eportfolio_id, owner_id) VALUES ('$Seminar_id', '$eportfolio_id', '$userid'); ");
        //
        // $db->query("INSERT INTO eportfolio_user (user_id, Seminar_id, eportfolio_id, owner) VALUES ('$userid', '$Seminar_id' , '$eportfolio_id', 1)");
        //
        // // $deleteCoursewareStandard = $db->query("DELETE FROM mooc_blocks WHERE type != 'Courseware' AND seminar_id = '".$Seminar_id."';");
        // $createCoursewareTemplate = $db->query("SELECT * FROM mooc_blocks WHERE type = 'Courseware' AND seminar_id = '".$Seminar_id."'; ")->fetchAll();
        // foreach ($createCoursewareTemplate as $block) {
        //   $block_id = $block[id];
        //
        // }
        //
        // echo $Seminar_id;
        // die();

        foreach ($GLOBALS['SEM_TYPE'] as $id => $sem_type){ //get the id of ePortfolio Seminarclass
          if ($sem_type['name'] == 'ePortfolio') {
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
        DBManager::get()->query("INSERT INTO eportfolio (Seminar_id, eportfolio_id, owner_id) VALUES ('$sem_id', '$eportfolio_id', '$userid')"); //table eportfolio
        DBManager::get()->query("INSERT INTO eportfolio_user(user_id, Seminar_id, eportfolio_id, owner) VALUES ('$userid', '$Seminar_id' , '$eportfolio_id', 1)"); //table eportfollio_user

    }
}
