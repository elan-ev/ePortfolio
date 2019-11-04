<?

/**
 * @author  <asudau@uos.de>
 *
 * @property int $id
 * @property string $group_id (Seminar)
 * @property string $eportfolio_id (Seminar)
 * @property string $type
 * @property string $user_id (User)
 * @property string $block_id (Mooc\Block)
 * @property int $mk_date
 */
class EportfolioActivity extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'eportfolio_activities';

        $config['belongs_to']['user'] = [
            'class_name'  => 'User',
            'foreign_key' => 'user_id',];

        $config['belongs_to']['group'] = [
            'class_name'  => 'EportfolioGroup',
            'foreign_key' => 'group_id',];

        $config['belongs_to']['eportfolio'] = [
            'class_name'  => 'Eportfoliomodel',
            'foreign_key' => 'eportfolio_id',];

        $config['additional_fields']['is_new']['get'] = function ($item) {
            if ($item->group_id && User::findCurrent()->id && (User::findCurrent()->id != $item->user_id)) {
                $seminar_id = $item->group_id;

                $last_visit = object_get_visit($seminar_id, 'sem');
                return $item->mk_date > $last_visit;
            } else {
                return false;
            }
        };
        $config['additional_fields']['link']['get']   = function ($item) {
            switch ($item->type) {
                case 'vorlage-erhalten':
                    $link = URLHelper::getURL('plugins.php/eportfolioplugin/eportfolioplugin', ['cid' => $item->eportfolio_id]);
                    break;
                case 'vorlage-verteilt':
                    $link = URLHelper::getURL('plugins.php/eportfolioplugin/showsupervisor', ['cid' => $item->group_id]);
                    break;
                case 'freigabe-entfernt':
                    $link = URLHelper::getURL('plugins.php/eportfolioplugin/eportfolioplugin', ['cid' => $item->eportfolio_id]);
                    break;
                default:
                    $link = URLHelper::getURL('plugins.php/courseware/courseware', ['cid' => $item->eportfolio_id, 'selected' => $item->block_id]);
                    break;
            }
            return $link;
        };

        $config['additional_fields']['message']['get'] = function ($item) {
            switch ($item->type) {
                case 'freigabe':
                    $message = 'hat einen neuen Abschnitt für Ihren Zugriff freigegeben';
                    break;
                case 'freigabe-entfernt':
                    $message = 'hat die Freigabe für einen Abschnitt zurückgenommen';
                    break;
                case 'notiz':
                    $message = 'hat eine neue Notiz erstellt';
                    break;
                case 'supervisor-notiz':
                    $message = 'hat eine neue Notiz für die Gruppensupervisoren erstellt';
                    break;
                case 'supervisor-answer':
                    $message = 'hat auf eine Anfrage geantwortet';
                    break;
                case 'aenderung':
                    $message = 'hat einen bereits freigegebenen Abschnitt verändert';
                    break;
                case 'vorlage-erhalten':
                    $message = 'In ' . Course::find($item->group_id)->name . ' wurden neue Portfolio-Inhalte verteilt';
                    break;
                case 'vorlage-verteilt':
                    $message = 'hat neue Portfolio-Inhalte verteilt';
                    break;
            }
            return $message;
        };

        parent::configure($config);
    }

    public static function getActivitiesForGroup($seminar_id)
    {
        return EportfolioActivity::findBySQL('group_id = ? ORDER BY mk_date DESC LIMIT 100', [$seminar_id]);
    }

    public function getActivitiesOfGroupUser($seminar_id, $user_id)
    {
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }
        return EportfolioActivity::findBySQL(
            'group_id = ? AND user_id = ?',
            [$seminar_id, $user_id]);
    }

    public function newActivities($seminar_id)
    {
        return EportfolioActivity::findBySQL(
            'group_id = ?  AND mk_date > ? AND user_id != ? ORDER BY mk_date DESC',
            [$seminar_id, object_get_visit($seminar_id, 'sem'), $GLOBALS['user']->id]);
    }

    public function addVorlagenActivity($group_id, $user_id)
    {
        $activity           = new EportfolioActivity();
        $activity->type     = 'vorlage-verteilt';
        $activity->user_id  = $user_id;
        $activity->mk_date  = time();
        $activity->group_id = $group_id;
        $activity->store();

        foreach (EportfolioGroup::find($group_id)->getRelatedStudentPortfolios() as $portfolio) {
            $activity                = new EportfolioActivity();
            $activity->type          = 'vorlage-erhalten';
            $activity->user_id       = $user_id;
            $activity->mk_date       = time();
            $activity->group_id      = $group_id;
            $activity->eportfolio_id = $portfolio;
            $activity->store();
        }
    }

    public function addActivity($portfolio_id, $block_id, $notification)
    {
        $activity = new EportfolioActivity();

        switch ($notification) {
            case 'UserDidPostSupervisorNotiz':
                $activity->type = 'supervisor-notiz';
                break;
            case 'SupervisorDidPostAnswer':
                $activity->type = 'supervisor-answer';
                break;
            default:
                $activity->type = $notification;
                break;
        }

        $activity->user_id       = User::findCurrent()->id;
        $activity->mk_date       = time();
        $group_id                = Eportfoliomodel::findBySeminarId($portfolio_id)->group_id;
        $activity->group_id      = $group_id ? $group_id : null;
        $activity->block_id      = intval($block_id);
        $activity->eportfolio_id = $portfolio_id;
        $activity->store();
    }

    public static function getLastFreigabeOfPortfolio($portfolio_id)
    {
        $freigabe = self::findOneBySQL("eportfolio_id = ? AND type = 'freigabe' ORDER BY mk_date DESC", [$portfolio_id]);
        return $freigabe ? $freigabe->mk_date : 0;
    }
}
