<?php


/**
 * @author  <asudau@uos.de>
 *
 * @property varchar    $user_id
 * @property varchar    $Seminar_id
 * @property varchar    $eportfolio_id
 * @property string     $status
 * @property text       $eportfolio_access
 * @property int        $owner
 */
class EportfolioUser extends SimpleORMap
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

        $this->db_table = 'eportfolio_user';

        parent::__construct($id);
    }

    /**
    * Liefert den Status eines Nutzers innerhalb einer Vorlage
    * 2   = grau (kein Abgabetermin festgelegt)
    * 1   = grün
    * 0   = orange
    * -1  = rot
    **/
    public static function getStatusOfUserInTemplate($template_id, $group_id, $seminar_id){
      $deadline = EportfolioGroupTemplates::getDeadline($group_id, $template_id);
      if ($deadline == 0) return 2;

      $timestampXTageVorher = strtotime('-4 day', $deadline);
      $now = time();

      $anzahlDerFreischaltungen = Eportfoliomodel::getNumberOfSharedChaptersOfTemplateFromUser($template_id, $seminar_id);
      $anzahlDerGesamtFreischaltungen = Eportfoliomodel::getNumberOfChaptersFromTemplate($template_id);

      if ($now < $timestampXTageVorher || $anzahlDerFreischaltungen == $anzahlDerGesamtFreischaltungen) {
        return 1;
      } else {
        if ($now > $timestampXTageVorher && $now < $deadline) {
          return 0;
        } else {
          return -1;
        }
      }

    }

    /**
    * Liefert den Status des Users in einer Gruppe
    * Status wird erzeugt aus den als Favorit makierten templates
    * Kleinster Status wird zurückgegeben
    **/
    public static function getStatusOfUserInGroup($user_id, $group_id, $seminar_id){
      $results = array();
      $templates = EportfolioGroup::getAllMarkedAsFav($group_id);
      foreach ($templates as $template) {
        $x = EportfolioUser::getStatusOfUserInTemplate($template, $group_id, $seminar_id);
        if ($x < 2) {
          array_push($results, $x);
        }
      }
      return (!empty($results)) ? min($results) : '1';
    }


}
