<?

/**
 * @author  <asudau@uos.de>
 *
 * @property string $Seminar_id
 * @property string $block_id
 * @property string $user_id
 * @property int $mkdate
 * @property int $chdate
 */
class EportfolioFreigabe extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'eportfolio_freigaben';
        parent::configure($config);
    }

    public static function hasAccess($user_id, $seminar_id, $chapter_id)
    {
        $portfolio = Eportfoliomodel::findBySeminarId($seminar_id);

        //Wenn das Portfolio Teil einer Gruppe mit zugehöriger Supervisorgruppe ist:
        //checke ob user Teil der Supervisorgruppe ist und prüfe in diesem Fall Berechtigung für Supervisorgruppe
        if ($portfolio->group_id) {
            $portfoliogroup = EportfolioGroup::findbySQL('seminar_id = :id', [':id' => $portfolio->group_id]);

        }
        if ($portfoliogroup[0]->supervisor_group_id) {
            $isUser = SupervisorGroupUser::findbySQL('supervisor_group_id = :id AND user_id = :user_id',
                [':id' => $portfoliogroup[0]->supervisor_group_id, ':user_id' => $user_id]);

        }
        if ($isUser) {
            $user_id = $portfoliogroup[0]->supervisor_group_id;
        }

        $hasAccess = EportfolioFreigabe::findBySQL('Seminar_id = :seminar_id AND block_id = :block_id AND user_id = :user_id',
            [':seminar_id' => $seminar_id, ':block_id' => $chapter_id, ':user_id' => $user_id]);
        $isOwner   = Eportfoliomodel::isOwner($seminar_id, $user_id);

        if ($hasAccess || $isOwner) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Given a Portfolio and a Block of said Portfolio
     * return a string of all users with access to the Block
     * 
     * @param string $seminar_id id of seminar(eportfolio)
     * @param int $chapter_id of courseware_chapter (Mooc\block)
     */
    public static function userList($seminar_id, $chapter_id) {
        $accessList = EportfolioFreigabe::findBySQL('Seminar_id = :seminar_id AND block_id = :block_id',
            [':seminar_id' => $seminar_id, ':block_id' => $chapter_id]);
        
        $users = array();
        foreach ($accessList as $user) {
            $users[] = User::find($user["user_id"])->getFullname();
        }
        usort($users, "strcmp");

        return implode(", ", $users);
    }

    /**
     * Give primary key of record as param to fetch
     * corresponding record from db if available, if not preset primary key
     * with given value. Give null to create new record
     *
     * @param string $seminar_id id of seminar(eportfolio)
     * @param int $chapter_id of courseware_chapter (Mooc\block)
     * @param boolean $status true => user_id gets access to chapter)
     */
    public static function setAccess($user_id, $seminar_id, $chapter_id, $status)
    {
        if ($status && !self::hasAccess($user_id, $seminar_id, $chapter_id)) {
            $access             = new self();
            $access->mkdate     = time();
            $access->Seminar_id = $seminar_id;
            $access->block_id   = $chapter_id;
            $access->user_id    = $user_id;
            if ($access->store()) {
                Eportfoliomodel::sendNotificationToUser('freigabe', $seminar_id, $chapter_id, $user_id);
                //freigaben werden nur als globale activity aufgenommen wenn sie für die supervisoren erfolgten
                if (SupervisorGroup::find($user_id)) {
                    EportfolioActivity::addActivity($seminar_id, $chapter_id, 'freigabe');
                }
            }
        } else if (self::hasAccess($user_id, $seminar_id, $chapter_id)) {
            self::deleteBySQL('Seminar_id = :seminar_id AND block_id = :block_id AND user_id = :user_id',
                [':seminar_id' => $seminar_id, ':block_id' => $chapter_id, ':user_id' => $user_id]);
            if (SupervisorGroup::find($user_id)) {
                EportfolioActivity::addActivity($seminar_id, $chapter_id, 'freigabe-entfernt');
            }
        }
    }

    public static function hasAccessSince($user_id, $chapter_id)
    {
        $hasAccessSince = EportfolioFreigabe::findOneBySQL('block_id = :block_id AND user_id = :user_id',
            [':block_id' => $chapter_id, ':user_id' => $user_id]);
        return $hasAccessSince->mkdate;
    }
}
