  <?php

class CreateController extends StudipController {

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->current_plugin;

    }

    public function before_filter(&$action, &$args){

    }


    public function index_action()
    {

        foreach ($GLOBALS['SEM_TYPE'] as $id => $sem_type){ //get the id of ePortfolio Seminarclass
          if ($sem_type['name'] == 'ePortfolio') {
            $sem_type_id = $id;
          }
        }

        $userid           = $GLOBALS["user"]->id; //get userid
        $sem_name         = studip_utf8decode(strip_tags($_POST['name']));
        $sem_description  = studip_utf8decode(strip_tags($_POST['beschreibung']));

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

        $sem->addMember($userid, 'dozent');
        $sem->store();

        $eportfolio = new Seminar();
        $eportfolio_id = $eportfolio->createId();
        
        //table eportfolio
        $values = array('sem_id' => $sem_id, 'eportfolio_id' => $eportfolio_id, 'userid' => $userid);
        $query = "INSERT INTO eportfolio (Seminar_id, eportfolio_id, owner_id) VALUES (:sem_id, :eportfolio_id, :userid)" ;
        $statement = DBManager::get()->prepare($query);
        $statement->execute($values);
        
        //table eportfolio_user
        $values2 = array('userid' => $userid, 'Seminar_id' => $sem_id, 'eportfolio_id' => $eportfolio_id, );
        $query2 = "INSERT INTO eportfolio_user(user_id, Seminar_id, eportfolio_id, owner) VALUES (:userid, :Seminar_id , :eportfolio_id, 1)" ;
        $statement2 = DBManager::get()->prepare($query2);
        $statement2->execute($values2);

        PageLayout::postMessage(MessageBox::success(_("Portfolio wurde angelegt.")));
        //$this->response->add_header('X-Dialog-Close', '1');
        //$this->render_nothing();
        
    }
}
