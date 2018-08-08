
<?php

include_once __DIR__.'/Eportfoliomodel.class.php';
include_once __DIR__.'/EportfolioGroup.class.php';
include_once __DIR__.'/SupervisorGroupUser.class.php';

/**
 * @author  <mkipp@uos.de>
 *
 * @property varchar    $group_id
 * @property varchar    $Seminar_id
 * @property int        $favorite
 * @property int        $mkdate
 * @property int        $abgabe_datum
 */
class EportfolioGroupTemplates extends SimpleORMap
{

    public $errors = array();

    /**
     * Give primary key of record as param to fetch
     * corresponding record from db if available, if not preset primary key
     * with given value. Give null to create new record
     *
     * @param mixed $id primary key of table
     */
    public function __construct($id = null) {

        $this->db_table = 'eportfolio_group_templates';

        parent::__construct($id);
    }

    /**
    * Setzt Abgabedatum für eine verteiltes Template als timestamp
    **/
    public static function setDeadline($group_id, $template_id, $date){
      $query = "UPDATE eportfolio_group_templates SET abgabe_datum = :datum WHERE group_id = :group_id AND Seminar_id = :template_id";
      $statement = DBManager::get()->prepare($query);
      $statement->execute(array(':datum'=> $date, ':group_id'=> $group_id, 'template_id' => $template_id));
    }

    /**
    * Liefert Abgabedatum für eine verteiltes Template als timestamp
    **/
    public static function getDeadline($group_id, $template_id){
      $result = EportfolioGroupTemplates::findBySQL('group_id = :group_id AND seminar_id = :seminar_id', array(':group_id' => $group_id, ':seminar_id' => $template_id));
      return $result[0]->abgabe_datum;
    }

}
