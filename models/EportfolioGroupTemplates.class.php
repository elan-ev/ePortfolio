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
    protected static function configure($config = [])
    {
        $config['db_table'] = 'eportfolio_group_templates';
        parent::configure($config);
    }

    /**
     * Setzt Abgabedatum für eine verteiltes Template als timestamp
     **/
    public static function setDeadline($group_id, $template_id, $date)
    {
        $query     = "UPDATE eportfolio_group_templates SET abgabe_datum = :datum WHERE group_id = :group_id AND Seminar_id = :template_id";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':datum' => $date, ':group_id' => $group_id, ':template_id' => $template_id]);
    }

    /**
     * Liefert Abgabedatum für eine verteiltes Template als timestamp
     **/
    public static function getDeadline($group_id, $template_id)
    {
        return DBManager::get()->fetchColumn('SELECT `abgabe_datum` FROM `eportfolio_group_templates` WHERE `Seminar_id` = ? AND `group_id` = ?', [$template_id, $group_id]);
    }

    /**EportfolioGroupTemplates::getGroupTemplates
     * Liefert alle verteilten Templates einer Gruppe
     **/
    public static function getGroupTemplates($group_id)
    {
        return DBManager::get()->fetchFirst("SELECT DISTINCT Seminar_id
            FROM eportfolio_group_templates
            WHERE group_id = ?
            ORDER BY mkdate DESC", [$group_id]);
    }

    /**
     * Liefert die Anzahl der verteilten Templates einer Gruppe
     **/
    public static function getNumberOfGroupTemplates($group_id)
    {
        return DBManager::get()->fetchColumn(
            "SELECT COUNT(Seminar_id) FROM eportfolio_group_templates WHERE group_id = :group_id",
            [':group_id' => $group_id]
        );
    }

    /**
     * Prüft ob ein Template schon verteilt wurde
     **/
    public static function checkIfGroupHasTemplate($group_id, $template_id)
    {
        return EportfolioGroupTemplates::countBySql("Seminar_id = :template_id AND group_id = :group_id",
                [':template_id' => $template_id, ':group_id' => $group_id]) > 0;
    }

    public static function getWannWurdeVerteilt($group_id, $template_id)
    {
        return DBManager::get()->fetchColumn('SELECT `mkdate` FROM `eportfolio_group_templates` WHERE `Seminar_id` = ? AND `group_id` = ?', [$template_id, $group_id]);
    }

    /**
     * Liefert den Names des Users der das Template verteilt hat als String
     * **/
    public static function getCreatorName($group_id, $template_id)
    {
        $user_id = DBManager::get()->fetchColumn('SELECT `verteilt_durch` FROM `eportfolio_group_templates` WHERE `Seminar_id` = ? AND `group_id` = ?', [$template_id, $group_id]);
        if (!$user_id) {
            return "Unknown";
        } else {
            return User::find($user_id)->getFullName();
        }
    }
}
