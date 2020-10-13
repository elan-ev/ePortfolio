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

    /**
     * check if the user can access the passed chapter, including access via group
     *
     * @param  [type]  $user_id    Valid user_ids only
     * @param  [type]  $chapter_id [description]
     *
     * @return boolean             [description]
     */
    public static function hasAccess($user_id, $chapter_id)
    {
        self::loadCourseware();

        $block = Mooc\DB\Block::find($chapter_id);

        if (!$block) {
            return false;
        }

        // check, if $user_id is portfolio-group
        $approval_type = SupervisorGroup::find($user_id) ? 'groups' : 'users';

        return $block->hasGroupApproval($user_id, 'read')
            || $block->hasUserApproval($user_id, 'write');
    }

    /**
     * checks, if access for passed user or group is explicitly set.
     *
     * @param  [type] $user_id    user_id or supervisor_group_id
     * @param  [type] $chapter_id [description]
     *
     * @return [type]             [description]
     */
    public static function getAccess($user_id, $chapter_id)
    {
        self::loadCourseware();

        // check, if $user_id as portfolio-group
        $approval_type = SupervisorGroup::find($user_id) ? 'groups' : 'users';

        $block = Mooc\DB\Block::find($chapter_id);

        if (!$block) {
            return false;
        }

        $list = $block->getApprovalList($approval_type);

        if ($approval_type == 'groups') {
            $id = md5($user_id . $block->seminar_id);
        } else {
            $id = $user_id;
        }

        if (is_array($list[$approval_type])) {
            foreach ($list[$approval_type] as $b_user_id => $perm) {
                if ($id == $b_user_id && ($perm == 'write' || $perm == 'read')) {
                    return true;
                }
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
        $egroup        = SupervisorGroup::find($user_id);
        $approval_type = $egroup ? 'groups' : 'users';

        $block = Mooc\DB\Block::find($chapter_id);
        if (!$block) {
            return false;
        }

        // remove default read permission
        // get root node
        $stmt = DBManager::get()->prepare("SELECT id FROM mooc_blocks
            WHERE parent_id IS NULL
                AND type ='Courseware'
                AND seminar_id = ?");

        $stmt->execute([$block->seminar_id]);

        $root_block = Mooc\DB\Block::find($stmt->fetchColumn());
        $settings['settings']['defaultRead'] = false;
        $root_block->setApprovalList(json_encode($settings));
        // end - remove default read permission

        if ($approval_type == 'groups') {
            $id = md5($user_id . $block->seminar_id);
        } else {
            $id = $user_id;
        }

        $list = $block->getApprovalList($approval_type);

        $seminar_id = $block->seminar_id;

        if ($approval_type == 'groups') {
            // get all supervisors
            $supervisors = SupervisorGroup::find($user_id);

            // make sure, the appropriate status group exists
            if (!$sgroup = Statusgruppen::find($id)) {
                // create new statusgroup
                $sgroup = Statusgruppen::create([
                    'statusgruppe_id' => $id,
                    'name'            => 'Berechtigte für Portfolioarbeit',
                    'range_id'        => $seminar_id
                ]);
            }

            $current_members = [];
            $new_members = [];

            foreach ($sgroup->members as $user) {
                $current_members[] = $user->user_id;
            }

            foreach ($supervisors->user as $user) {
                $new_members[] = $user->user_id;
            }

            $remove_members = array_diff($current_members, $new_members);
            $add_members    = array_diff($new_members, $current_members);

            foreach ($remove_members as $uid) {
                if ($user = $sgroup->members->findOneByUserId($uid)) {
                    $user->delete();
                }
            }

            foreach ($add_members as $uid) {
                $sgroup->members[] = StatusgruppeUser::build([
                    'statusgruppe_id' => $id,
                    'user_id'         => $uid
                ]);
            }

            $sgroup->members->store();

        }

        if (!is_array($list[$approval_type])) {
            $list[$approval_type] = [];
        }

        // set permissions for supervisor group
        if ($approval_type == 'groups') {
            if ($status == 'true') {
                // do not overwrite write permissions
                if ($list[$approval_type][$id] != 'write') {
                    $list[$approval_type][$id] = 'read';
                }

                EportfolioActivity::addActivity($seminar_id, $chapter_id, 'freigabe');
            } else {
                $list[$approval_type][$id] = 'none';

                EportfolioActivity::addActivity($seminar_id, $chapter_id, 'freigabe-entfernt');
            }
        } else {
            if ($status == 'true') {
                // do not overwrite write permissions
                $list[$approval_type][$id] = 'write';
            } else {
                $list[$approval_type][$id] = 'none';
            }
        }

        $block->setApprovalList(json_encode([
            'settings' => [
                'defaultRead' => false
            ],
            $approval_type => $list[$approval_type]
        ]));
    }

    /**
     * return number of shared chapters in template, considering supervisor group ONLY
     *
     * @param  string $cours_id  course_id of portfolio
     * @param  mixed $chapters   the chapters to check
     *
     * @return int              number of chapters shared with supervisor group
     */
    public static function sharedChapters($course_id, $chapters)
    {
        $group    = SupervisorGroup::findOneBySQL('Seminar_id = ?', [$course_id]);
        $count    = 0;

        foreach ($chapters as $blocks) {
            foreach ($blocks as $chapter) {
                if (self::getAccess($group->id, $chapter['id'])) {
                    $count++;
                }
            }
        }

        return $count;
    }
}
