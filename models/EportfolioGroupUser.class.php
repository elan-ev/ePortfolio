<?php


/**
 * @author  <asudau@uos.de>
 *
 * @property varchar     $seminar_id
 * @property varchar     $user_id
 */
class EportfolioGroupUser extends SimpleORMap
{

    public $errors = array();

     protected static function configure($config = array())
    {
        $config['db_table'] = 'eportfolio_groups_user';

        $config['belongs_to']['eportfolio_group'] = array(
            'class_name' => 'EportfolioGroup',
            'foreign_key' => 'seminar_id', );


         parent::configure($config);
    }


    /**
     * Give primary key of record as param to fetch
     * corresponding record from db if available, if not preset primary key
     * with given value. Give null to create new record
     *
     * @param mixed $id primary key of table
     */
    public function __construct($id = null) {

        parent::__construct($id);
    }

     public static function findByGroupId($id)
    {
        return static::findBySQL('seminar_id = ?', array($id));
    }

    /**
    * Gibt ein Array mit den Portfolio ID's eines Users
    * innerhalb einer Gruppe wieder
    **/
    public static function getPortfolioIDsFromUserinGroup($group_id, $user_id){
      $query = "SELECT * FROM eportfolio WHERE owner_id = :owner_id AND group_id = :group_id";
      $statement = DBManager::get()->prepare($query);
      $statement->execute(array(':owner_id'=> $user_id, ':group_id' => $group_id));
      $result = $statement->fetchAll();
      $return = array();
      foreach ($result as $key) {
        array_push($return, $key[0]);
      }
      return $return;
    }

    /**
    * Gibt die Anzahl der freigegeben Kapitel zurück
    **/
    public static function getAnzahlFreigegebenerKapitel($user_id, $group_id){
      $anzahl = 0;
      $templates = static::getPortfolioIDsFromUserinGroup($group_id, $user_id);
      foreach ($templates as $temp) {
        $query =  "SELECT COUNT(e1.Seminar_id) FROM eportfolio e1
                  JOIN eportfolio_freigaben e2 ON e1.Seminar_id = e2.Seminar_id
                  WHERE e1.Seminar_id = :id";

        $statement = DBManager::get()->prepare($query);
        $statement->execute(array(':id'=> $temp));
        $result = $statement->fetchAll();
        $anzahl += $result[0][0];
      }
      return $anzahl;
    }

    /**
    * Gibt die Verhältnis freigeben/gesamt in Prozent wieder
    **/
    public static function getGesamtfortschrittInProzent($user_id, $group_id){
      $oben = static::getAnzahlFreigegebenerKapitel($user_id, $group_id);
      $unten = EportfolioGroup::getAnzahlAllerKapitel($group_id);
      return $oben / $unten * 100;
    }

    /**
    * Gibt die Anzahl der Notizen für den Supervisor eines users
    * innerhalb einer Gruppe wieder
    **/
    public static function getAnzahlNotizen($user_id, $group_id){
      $anzahl = 0;
      $temps = static::getPortfolioIDsFromUserinGroup($group_id, $user_id);
      foreach ($temps as $temp) {
        $query = "SELECT COUNT(type) FROM mooc_blocks WHERE Seminar_id = :seminar_id AND type = 'PortfolioBlockSupervisor'";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array(':seminar_id'=> $temp));
        $result = $statement->fetchAll();
        $anzahl += $result[0][0];
      }
      return $anzahl;
    }

    /**
    * Gibt die ID des Portfolios des Nutzers in einer Gruppe zurück
    **/
    public static function getPortfolioIdOfUserInGroup($user_id, $group_id){
      $query = "SELECT Seminar_id FROM eportfolio WHERE owner_id = :owner_id AND group_id = :group_id";
      $statement = DBManager::get()->prepare($query);
      $statement->execute( array(':owner_id' => $user_id, ':group_id' => $group_id));
      $result = $statement->fetchAll();
      return $result[0][0];
    }


    public static function getAnzahlAnNeuerungen($userid, $groupid){
      return 3;
    }


}
