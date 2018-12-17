<?

/**
 * Class ajaxsupervisorController
 * @deprecated
 * Ich glaub, der Code wird nicht mehr genutzt
 */
class ajaxsupervisorController extends StudipController
{
    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->current_plugin;
        
        //check status and trigger query
        
        if (!$GLOBALS['perm']->have_perm('dozent')) {
            throw new Exception("Not Allowed");
        } else {
            $code = $this->getPortfolios(Request::get('userId'));
            echo json_encode($code);
        }
    }
    
    public function index_action()
    {
    }
    
    public function getCourseBeschreibung($cid)
    {
        $query     = "SELECT Beschreibung FROM seminare WHERE Seminar_id = :cid";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':cid' => $cid]);
        return $statement->fetchAll()[0]['Beschreibung'];
    }
    
    public function countViewer($cid)
    {
        $query     = "SELECT COUNT(Seminar_id) FROM eportfolio_user WHERE Seminar_id = :cid AND owner = 0";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':cid' => $cid]);
        echo $statement->fetchAll()[0][0];
        
    }

    public function getPortfolios($id)
    {
        $userid     = $id;
        $portfolios = [];
        
        $query     = "SELECT Seminar_id FROM eportfolio WHERE owner_id = :userid";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':userid' => $userid]);
        $querygetcid = $statement->fetchAll();
        
        foreach ($querygetcid as $key) {
            array_push($portfolios, $key[Seminar_id]);
        }
        
        return $portfolios;
    }
}
