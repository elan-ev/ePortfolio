<?php

include_once __DIR__.'/EportfolioGroup.class.php';

/**
 * @author  <asudau@uos.de>
 *
 * @property varchar     $Seminar_id
 * @property varchar     $eportfolio_id
 * @property varchar     $group_id
 * @property string      $templateStatus
 * @property varchar     $owner_id
 * @property varchar     $supervisor_id
 * @property json        $freigaben_kapitel //deprecated
 * @property varchar     $template_id
 * @property json        $settings //deprecated?
 * @property int         $favorite
*/
class Eportfoliomodel extends SimpleORMap
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

        $this->db_table = 'eportfolio';

        parent::__construct($id);
    }

    public static function getAllSupervisors($cid){
        $supervisoren = array();
        $portfolio = Eportfoliomodel::findBySQL('Seminar_id = :cid', array(':cid' => $cid));
        if ($portfolio[0]->group_id){
            array_push($supervisoren, EportfolioGroup::getAllSupervisors($portfolio[0]->group_id));
        }
        return $supervisoren[0];
    }

     public static function getOwner($cid){
        $portfolio = Eportfoliomodel::findBySQL('Seminar_id = :cid', array(':cid' => $cid));
        return $portfolio[0]->owner_id;
    }

    public function getPortfolioVorlagen(){

      global $perm;
      $seminare = array();

      $semId = Config::get()->getValue('SEM_CLASS_PORTFOLIO_VORLAGE');

      $db = DBManager::get();
      $query = "SELECT Seminar_id FROM seminare WHERE status = :semId";
      $statement = $db->prepare($query);
      $statement->execute(array(':semId'=> $semId));
      foreach ($statement->fetchAll() as $key) {
        if($perm->have_studip_perm('autor', $key[Seminar_id])){
            array_push($seminare, $key[Seminar_id]);
        }
      }

      return $seminare;

    }

    public static function getMyPortfolios(){

      $userid = $GLOBALS["user"]->id;
      $myportfolios = array();

      $semClass = Config::get()->getValue('SEM_CLASS_PORTFOLIO');
      $db = DBManager::get();
      $query = "SELECT Seminar_id FROM eportfolio WHERE owner_id = :userid";
      $statement = $db->prepare($query);
      $statement->execute(array(':userid'=> $userid));

      foreach ($statement->fetchAll() as $key) {
        if(Course::find($key[Seminar_id])->status == $semClass){
            array_push($myportfolios, $key[Seminar_id]);
        }
      }
      return $myportfolios;
    }

    /**
    * Gibt ein Array(title, id) mit alles Oberkapiteln einer Veranstaltung aus
    **/
    public static function getChapters($id){
        $db = DBManager::get();
        $query = "SELECT title, id FROM mooc_blocks WHERE seminar_id = :id AND type = 'Chapter' AND parent_id != '0' ORDER BY position ASC";
        $statement = $db->prepare($query);
        $statement->execute(array(':id'=> $id));
        $result = $statement->fetchAll();
        $return = array();
        foreach ($result as $key) {
          $tmp = array(
            'title' => $key[title],
            'id' => $key[id]
          );
          array_push($return, $tmp);
        }
        return $return;
    }

    public static function isVorlage($id)
    {
        if(Course::findById($id)){
            $seminar = Seminar::getInstance($id);
            $status = $seminar->getStatus();
            if ($status == Config::get()->getValue('SEM_CLASS_PORTFOLIO_VORLAGE')){
                return true;
            }
            else return false;
        }
        else return false;
    }

     public static function getAllBlocksInOrder($id){
        $db = DBManager::get();
        $blocks = array();
        $query = "SELECT title, id FROM mooc_blocks WHERE seminar_id = :id AND type = 'Chapter' AND parent_id != '0' ORDER BY position ASC";
        $statement = $db->prepare($query);
        $statement->execute(array(':id'=> $id));
        foreach($statement->fetchAll() as $chapter){
            array_push($blocks, $chapter[id]);
            $query = "SELECT title, id FROM mooc_blocks WHERE parent_id = :id ORDER BY position ASC";
            $statement = $db->prepare($query);
            $statement->execute(array(':id'=> $chapter[id]));
            foreach($statement->fetchAll() as $subchapter){
                array_push($blocks, $subchapter[id]);
                $query = "SELECT title, id FROM mooc_blocks WHERE parent_id = :id ORDER BY position ASC";
                $statement = $db->prepare($query);
                $statement->execute(array(':id'=> $subchapter[id]));
                foreach($statement->fetchAll() as $section){
                    array_push($blocks, $section[id]);
                    $query = "SELECT title, id FROM mooc_blocks WHERE parent_id = :id ORDER BY position ASC";
                    $statement = $db->prepare($query);
                    $statement->execute(array(':id'=> $section[id]));
                    foreach($statement->fetchAll() as $block){
                        array_push($blocks, $block[id]);
                    }
                }
            }
        }
        return $blocks;
    }

}
