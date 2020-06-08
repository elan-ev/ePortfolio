<?php

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
        $hasAccess = false;
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
            $hasAccess = EportfolioFreigabe::findBySQL('Seminar_id = :seminar_id AND block_id = :block_id AND user_id = :user_id',
                [':seminar_id' => $seminar_id, ':block_id' => $chapter_id, ':user_id' => $portfoliogroup[0]->supervisor_group_id]);
        }

        // if user has no access for example through the supervisor grpup, check if access is allowed on per user basis
        if (!$hasAccess) {
            $hasAccess = EportfolioFreigabe::findBySQL('Seminar_id = :seminar_id AND block_id = :block_id AND user_id = :user_id',
                [':seminar_id' => $seminar_id, ':block_id' => $chapter_id, ':user_id' => $user_id]);
        }
        $isOwner   = Eportfoliomodel::isOwner($seminar_id, $user_id);

        if ($hasAccess || $isOwner) {
            return true;
        } else {
            return false;
        }
    }

    public static function getAccess($user_id, $seminar_id, $chapter_id)
    {
        return EportfolioFreigabe::findBySQL('Seminar_id = :seminar_id AND block_id = :block_id AND user_id = :user_id',
            [':seminar_id' => $seminar_id, ':block_id' => $chapter_id, ':user_id' => $user_id]);
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
        if ($status && !self::getAccess($user_id, $seminar_id, $chapter_id)) {
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
        } else if (self::getAccess($user_id, $seminar_id, $chapter_id)) {
            self::deleteBySQL('Seminar_id = :seminar_id AND block_id = :block_id AND user_id = :user_id',
                [':seminar_id' => $seminar_id, ':block_id' => $chapter_id, ':user_id' => $user_id]);
            if (SupervisorGroup::find($user_id)) {
                EportfolioActivity::addActivity($seminar_id, $chapter_id, 'freigabe-entfernt');
            }
        }
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
