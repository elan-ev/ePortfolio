<?

/**
 * @author  <mkipp@uos.de>
 *
 * @property varchar $group_id
 * @property varchar $Seminar_id
 * @property int $favorite
 * @property int $mkdate
 * @property int $abgabe_datum
 * @property varchar $verteilt_durch
 */
class EportfolioGroupTemplates extends SimpleORMap
{
    
    public $errors = [];
    
    /**
     * Give primary key of record as param to fetch
     * corresponding record from db if available, if not preset primary key
     * with given value. Give null to create new record
     *
     * @param mixed $id primary key of table
     */
    public function __construct($id = null)
    {
        
        $this->db_table = 'eportfolio_group_templates';
        
        parent::__construct($id);
    }
    
    /**
     * Setzt Abgabedatum für eine verteiltes Template als timestamp
     **/
    public static function setDeadline($group_id, $template_id, $date)
    {
        $query     = "UPDATE eportfolio_group_templates SET abgabe_datum = :datum WHERE group_id = :group_id AND Seminar_id = :template_id";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':datum' => $date, ':group_id' => $group_id, ':template_id' => $template_id]);
        // $object = EportfolioGroupTemplates::findOneBySQL('group_id = :group_id AND Seminar_id = :template_id', array(':group_id'=> $group_id, ':template_id' => $template_id));
        // $object->abgabe_datum = $date;
        // $object->store();
    }
    
    /**
     * Liefert Abgabedatum für eine verteiltes Template als timestamp
     **/
    public static function getDeadline($group_id, $template_id)
    {
        $result = EportfolioGroupTemplates::findBySQL('group_id = :group_id AND seminar_id = :seminar_id', [':group_id' => $group_id, ':seminar_id' => $template_id]);
        return $result[0]->abgabe_datum;
    }
    
    /**
     * Liefert alle verteilten Templates einer Gruppe
     **/
    public static function getGroupTemplates($group_id)
    {
        $query     = "SELECT Seminar_id FROM eportfolio_group_templates WHERE group_id = :group_id";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':group_id' => $group_id]);
        $result  = $statement->fetchAll(PDO::FETCH_ASSOC);
        $sem_ids = [];
        foreach ($result as $sem) {
            $sem_ids[] = $sem['Seminar_id'];
        }
        return $sem_ids;
    }
    
    /**
     * Liefert die Anzahl der verteilten Templates einer Gruppe
     **/
    public static function getNumberOfGroupTemplates($group_id)
    {
        $query     = "SELECT COUNT(Seminar_id) FROM eportfolio_group_templates WHERE group_id = :group_id";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':group_id' => $group_id]);
        $result = $statement->fetchAll();
        return $result[0][0];
    }
    
    /**
     * Prüft ob ein Template schon verteilt wurde
     **/
    public static function checkIfGroupHasTemplate($group_id, $template_id)
    {
        $result = EportfolioGroupTemplates::findBySQL("Seminar_id = :template_id AND group_id = :group_id", [':template_id' => $template_id, ':group_id' => $group_id]);
        if (empty($result)) {
            return false;
        } else {
            return true;
        }
    }
    
    public static function getWannWurdeVerteilt($group_id, $template_id)
    {
        $result = EportfolioGroupTemplates::findBySQL("Seminar_id = :template_id AND group_id = :group_id", [':template_id' => $template_id, ':group_id' => $group_id]);
        return $result[0]["mkdate"];
    }
    
    /**
     * Liefert den Names des Users der das Template verteilt hat als String
     * **/
    public static function getCreatorName($group_id, $template_id)
    {
        $result  = EportfolioGroupTemplates::findBySQL("Seminar_id = :template_id AND group_id = :group_id", [':template_id' => $template_id, ':group_id' => $group_id]);
        $user_id = $result[0]["verteilt_durch"];
        if (!$user_id) {
            return "Unknown";
        } else {
            $user = new User($user_id);
            return $user["Vorname"] . " " . $user["Nachname"];
        }
    }
}
