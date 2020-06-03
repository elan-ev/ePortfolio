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

    private static function loadCourseware()
    {
        $plugin_courseware = PluginManager::getInstance()->getPlugin('Courseware');
        require_once 'public/' . $plugin_courseware->getPluginPath() . '/vendor/autoload.php';
    }

    public static function hasAccess($user_id, $chapter_id)
    {
        $block = Mooc\DB\Block::find($chapter_id);
        return $block->hasApproval($user_id);
    }

    public static function getAccess($user_id, $chapter_id)
    {
        self::loadCourseware();

        // check, if $user_id as portfolio-group
        $approval_type = EportfolioGroup::findOneBySQL('supervisor_group_id = ?', [$user_id]) ? 'groups' : 'users';

        $block = Mooc\DB\Block::find($chapter_id);
        $list = $block->getApprovalList($approval_type);

        foreach ($list as $key => $id) {
            if ($id == $user_id) {
                return true;
            }
        }

        return false;
    }


    /**
     * Given a Portfolio and a Block of said Portfolio
     * return a string of all users with access to the Block
     *
     * @param string $seminar_id id of seminar(eportfolio)
     * @param int $chapter_id of courseware_chapter (Mooc\block)
     */
    public static function userList($seminar_id, $chapter_id)
    {
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
    public static function setAccess($user_id, $chapter_id, $status)
    {
        self::loadCourseware();

        // check, if $user_id as portfolio-group
        $approval_type = EportfolioGroup::findOneBySQL('supervisor_group_id = ?', [$user_id]) ? 'groups' : 'users';

        $block = Mooc\DB\Block::find($chapter_id);
        $list[$approval_type] = $block->getApprovalList($approval_type);

        $seminar_id = $block->seminar_id;

        if ($approval_type == 'groups') {
            // make sure, the appropriate status group exists
            if (!Statusgruppen::find($user_id)) {
                Statusgruppen::create([
                    'statusgruppe_id' => $user_id,
                    'name'            => 'Berechtigte für Portfolioarbeit',
                    'range_id'        => $seminar_id
                ]);
            }
        }

        if ($status && !self::getAccess($user_id, $chapter_id)) {
            $list[$approval_type][] = $user_id;

            Eportfoliomodel::sendNotificationToUser('freigabe', $seminar_id, $chapter_id, $user_id);
            //freigaben werden nur als globale activity aufgenommen wenn sie für die supervisoren erfolgten
            if (SupervisorGroup::find($user_id)) {
                EportfolioActivity::addActivity($seminar_id, $chapter_id, 'freigabe');
            }
        } else if (self::getAccess($user_id, $chapter_id)) {
            foreach ($list[$approval_type] as $key => $id) {
                if ($id == $user_id) {
                    unset($list[$approval_type][$key]);
                }
            }

            if (SupervisorGroup::find($user_id)) {
                EportfolioActivity::addActivity($seminar_id, $chapter_id, 'freigabe-entfernt');
            }
        }

        $block->setApprovalList(json_encode($list));
    }

    public static function hasAccessSince($user_id, $chapter_id)
    {
        $hasAccessSince = EportfolioFreigabe::findOneBySQL('block_id = :block_id AND user_id = :user_id',
            [':block_id' => $chapter_id, ':user_id' => $user_id]);
        return $hasAccessSince->mkdate;
    }

    /**
     * return number of shared chapters in template, considering supervisor group ONLY
     *
     * @param  string $cid      course_id of portfolio
     * @param  mixed $chapters  the chapters to check
     *
     * @return int              number of chapters shared with supervisor group
     */
    public static function sharedChaptersInTemplate($cid, $chapters)
    {
        $chapterIds = array_keys(array_column($chapters, NULL, 'id'));

        $stmt = DBManager::get()->prepare("SELECT COUNT(DISTINCT block_id)
            FROM eportfolio_freigaben
            JOIN eportfolio_groups AS g
                ON eportfolio_freigaben.user_id = g.supervisor_group_id
            WHERE block_id IN (:block_id)
                AND eportfolio_freigaben.Seminar_id = :seminar_id");

        $stmt->bindPAram(':block_id', $chapterIds, StudipPDO::PARAM_ARRAY);
        $stmt->execute([':seminar_id' => $cid]);

        return $stmt->fetchColumn();
    }

    /**
     * Delete entrys which belong to users no longer present
     *
     * @param  string $course_id
     *
     * @return void
     */
    public static function prune($course_id)
    {

        $results = DBManager::get()->query("SELECT DISTINCT f.seminar_id, f.user_id, f.block_id FROM eportfolio_freigaben f
            LEFT JOIN eportfolio_user u ON (
                f.seminar_id = u.seminar_id
                AND f.user_id = u.user_id
            )
            LEFT JOIN supervisor_group_user sup ON (
                sup.supervisor_group_id = f.user_id
            )
            WHERE u.seminar_id IS NULL
                AND sup.user_id IS NULL
        ");

        while ($data = $results->fetch(PDO::FETCH_ASSOC)) {
            self::deleteBySQL('Seminar_id = :seminar_id AND block_id = :block_id AND user_id = :user_id', $zw = [
                ':seminar_id' => $data['Seminar_id'],
                ':block_id'   => $data['block_id'],
                ':user_id' => $data['user_id']
            ]);
        }

    }
}
