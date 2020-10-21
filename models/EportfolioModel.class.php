<?

/**
 * @author  <asudau@uos.de>
 *
 * @property varchar $Seminar_id
 * @property varchar $owner_id
 * @property varchar $group_id

 */
class EportfolioModel extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'eportfolio';

        $config['belongs_to']['seminar'] = [
            'class_name'  => 'Course',
            'foreign_key' => 'Seminar_id'
        ];

        $config['belongs_to']['owner'] = [
            'class_name'  => 'User',
            'foreign_key' => 'owner_id'
        ];

        parent::configure($config);
    }

    public static function getPortfolioVorlagen()
    {
        $query = "
            SELECT  DISTINCT `seminare`.*
            FROM `seminare`
            JOIN `seminar_user` USING(`Seminar_id`)
            WHERE `seminare`.`status` = ? AND `seminar_user`.`status` IN ('autor', 'tutor', 'dozent')
            AND `seminar_user`.`user_id` = ?
            ORDER BY `mkdate` DESC
        ";
        return DBManager::get()->fetchAll($query, [Config::get()->SEM_CLASS_PORTFOLIO_VORLAGE, User::findCurrent()->id], 'Course::buildExisting');
    }

    public static function findBySeminarId($sem_id)
    {
        $eportfolio = EportfolioModel::findOneBySQL('seminar_id = :id', [':id' => $sem_id]);
        return $eportfolio;
    }

    public static function isOwner($sem_id, $user_id)
    {
        $eportfolio = EportfolioModel::findBySeminarId($sem_id);
        return $eportfolio->owner_id == $user_id;
    }


    public static function getMyPortfolios()
    {
        return Course::findBySQL(
            'INNER JOIN `eportfolio` ON `eportfolio`.`Seminar_id` = `seminare`.`Seminar_id`
            WHERE `eportfolio`.`owner_id` = ? AND `seminare`.`status` = ?',
            [$GLOBALS["user"]->id, Config::get()->getValue('SEM_CLASS_PORTFOLIO')]
        );
    }

    /**
     * Gibt ein Array(title, id) mit allen Oberkapiteln einer Veranstaltung aus
     **/
    public static function getChapters($id)
    {
        $query     = "SELECT `title`, `id` FROM `mooc_blocks`
            WHERE `seminar_id` = :id AND `type` = 'Chapter' AND `parent_id` != '0'
            ORDER BY `position` ASC";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':id' => $id]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gibt ein Array(title, id) mit allen Unterkapiteln eines Oberkapitels aus
     **/
    public static function getSubChapters($chapter_id)
    {
        $query     = "SELECT `title`, `id`  FROM `mooc_blocks`
            WHERE `parent_id` = :parent_id AND `type` = 'Subchapter'
            ORDER BY `position` ASC";

        $statement = DBManager::get()->prepare($query);
        $statement->execute([':parent_id' => $chapter_id]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getChapterInformation($portfolio_id)
    {
        return DBManager::get()->fetchAll("SELECT mooc_blocks.title, mooc_blocks.id
            FROM mooc_blocks
            WHERE mooc_blocks.seminar_id = :portfolio_id AND mooc_blocks.type = 'Chapter' AND mooc_blocks.parent_id != '0'
            ORDER BY mooc_blocks.id ASC",
        ["portfolio_id" => $portfolio_id]);
    }

    /**
     * Prüft ob in in einem Kaptiel einer Courseware eine Resonanz auf
     * eine Supervisorennotiz gegeben wurde
     **/
    public static function checkSupervisorResonanz($chapter_id)
    {
        $supervisorResponses = DBManager::get()->fetchAll(
            "SELECT mooc_fields.json_data
            FROM mooc_fields
            JOIN mooc_blocks ON mooc_blocks.id = mooc_fields.block_id
            JOIN mooc_blocks AS mcb ON mcb.id = mooc_blocks.parent_id
            WHERE mooc_fields.name = 'supervisorcontent' AND mooc_blocks.type = 'PortfolioBlockSupervisor' AND mcb.parent_id IN (
                SELECT id
                FROM mooc_blocks
                WHERE parent_id = :parent_id)",
            [':parent_id' => $chapter_id]
        );

        if(empty($supervisorResponses)) {
            return false;
        }

        foreach($supervisorResponses as $response) {
            if($response['json_data'] === '""') {
                return false;
            }
        }
        return true;
    }

    /**
     * Prüft ob in in einem Unterkaptiel einer Courseware eine Resonanz auf
     * eine Supervisorennotiz gegeben wurde
     **/
    public static function checkSupervisorResonanzInSubchapter($subchapter_ids)
    {
        $supervisorResponses = DBManager::get()->fetchAll(
            "SELECT json_data
            FROM mooc_fields
            JOIN mooc_blocks ON mooc_blocks.id = mooc_fields.block_id
            JOIN mooc_blocks AS mcb ON mcb.id = mooc_blocks.parent_id
            WHERE mooc_fields.name = 'supervisorcontent' AND mooc_blocks.type = 'PortfolioBlockSupervisor' AND mcb.parent_id IN (:subchapter_ids)",
            [':subchapter_ids' => $subchapter_ids]
        );

        if(empty($supervisorResponses)) {
            return false;
        }

        foreach($supervisorResponses as $response) {
            if($response['json_data'] === '""') {
                return false;
            }
        }
        return true;
    }

    /**
     * Prüft ob ein Kapitel freigeschaltet wurde
     **/
    public static function checkKapitelFreigabe($chapter_id)
    {
        // check, if the passed chapter is accessible by the responsible supervisor group
        $block = Mooc\DB\Block::find($chapter_id);
        if (!$block) {
            return false;
        }

        $group = SupervisorGroup::findOneBySQL('Seminar_id = ?', [$block->seminar_id]);

        return EportfolioFreigabe::getAccess($group->id, $chapter_id);
    }

    /**
     * Prüft ob es eine SupervisorNotiz in einem Kapitel gibt
     **/
    public static function checkSupervisorNotiz($id)
    {
        return DBManager::get()->fetchAll(
            "SELECT json_data
            FROM mooc_fields
            JOIN mooc_blocks ON mooc_blocks.id = mooc_fields.block_id
            JOIN mooc_blocks AS mcb ON mcb.id = mooc_blocks.parent_id
            WHERE mooc_fields.name = 'content' AND mooc_blocks.type = 'PortfolioBlockSupervisor' AND mcb.parent_id IN (
                SELECT id
                FROM mooc_blocks
                WHERE parent_id = :parent_id
            )",
            [':parent_id' => $id]
        );
    }

    /**
     * Prüft ob es eine SupervisorNotiz in einem Kapitel gibt
     **/
    public static function countSupervisorNotiz($id)
    {
        return count(DBManager::get()->fetchAll(
            "SELECT json_data
            FROM mooc_fields
            JOIN mooc_blocks ON mooc_blocks.id = mooc_fields.block_id
            JOIN mooc_blocks AS mcb ON mcb.id = mooc_blocks.parent_id
            WHERE mooc_fields.name = 'content' AND mooc_blocks.type = 'PortfolioBlockSupervisor' AND mcb.parent_id IN (
                SELECT id
                FROM mooc_blocks
                WHERE parent_id IN (:parent_id)
            )",
            [':parent_id' => $id]
        ));
    }

    /**
     * Gibt die passende BlockId des EPortfolios anhand der VorlagenblockID zurück
     * $seminar_id ist hier die seminar_id des Portfolios des Users
     **/
    public static function getUserPortfolioBlockId($seminar_id, $block_id)
    {
        return DBManager::get()->fetchColumn(
            "SELECT `block_id` FROM `eportfolio_block_infos` WHERE `seminar_id` = :seminar_id AND `vorlagen_block_id` = :block_id"
            , [':seminar_id' => $seminar_id, ':block_id' => $block_id]);
    }

    /**
     * Liefert Timestamp eines Kapitels
     **/
    public static function getTimestampOfChapter($block_id)
    {
        return DBManager::get()->fetchColumn(
            "SELECT mkdate FROM mooc_blocks WHERE id = :block_id",
            [':block_id' => $block_id]
        );
    }

    /**
     * liefert ParentId eines Blocks
     **/
    public static function getParentId($block_id)
    {
        return DBManager::get()->fetchColumn(
            "SELECT parent_id FROM mooc_blocks WHERE id = :id",
            [':id' => $block_id]
        );
    }

    public static function checkSupervisorNoteInSubchapter($subchapter_ids)
    {
        return DBManager::get()->fetchAll(
            "SELECT json_data
            FROM mooc_fields
            JOIN mooc_blocks ON mooc_blocks.id = mooc_fields.block_id
            JOIN mooc_blocks AS mcb ON mcb.id = mooc_blocks.parent_id
            WHERE mooc_fields.name = 'content' AND mooc_blocks.type = 'PortfolioBlockSupervisor' AND mcb.parent_id IN (:subchapter_ids)",
            [':subchapter_ids' => $subchapter_ids]
        );
    }

    public static function isVorlage($id)
    {
        if (Course::findById($id)) {
            $seminar = Seminar::getInstance($id);
            $status  = $seminar->getStatus();
            if ($status == Config::get()->SEM_CLASS_PORTFOLIO_VORLAGE) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function getAllBlocksInOrder($id)
    {
        $db        = DBManager::get();
        $blocks    = [];
        $query     = "SELECT title, id FROM mooc_blocks WHERE seminar_id = :id AND type = 'Chapter' AND parent_id != '0' ORDER BY position ASC";
        $statement = $db->prepare($query);
        $statement->execute([':id' => $id]);
        foreach ($statement->fetchAll() as $chapter) {
            array_push($blocks, $chapter['id']);
            $query     = "SELECT title, id FROM mooc_blocks WHERE parent_id = :id ORDER BY position ASC";
            $statement = $db->prepare($query);
            $statement->execute([':id' => $chapter['id']]);
            foreach ($statement->fetchAll() as $subchapter) {
                array_push($blocks, $subchapter['id']);
                $query     = "SELECT title, id FROM mooc_blocks WHERE parent_id = :id ORDER BY position ASC";
                $statement = $db->prepare($query);
                $statement->execute([':id' => $subchapter['id']]);
                foreach ($statement->fetchAll() as $section) {
                    array_push($blocks, $section['id']);
                    $query     = "SELECT title, id FROM mooc_blocks WHERE parent_id = :id ORDER BY position ASC";
                    $statement = $db->prepare($query);
                    $statement->execute([':id' => $section['id']]);
                    foreach ($statement->fetchAll() as $block) {
                        array_push($blocks, $block['id']);
                    }
                }
            }
        }
        return $blocks;
    }

    public static function sendNotificationToUser($case, $portfolio_id, $block_id, $user_id)
    {

        $portfolio = EportfolioModel::findBySeminarId($portfolio_id);
        $owner     = get_fullname($portfolio->owner->id);
        $link      = $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'plugins.php/courseware/courseware?cid=' . $portfolio_id . '&selected=' . $block_id;
        switch ($case) {
            default:
            case 'supervisornotiz':
                $mail_subj = 'Neue Portfolio-Notiz für Supervisoren von ' . $owner;
                $mail_msg  = sprintf(
                    _("Neue Notiz von '%s'\n"
                        . "in: %s \n"
                        . "Direkt zur Notiz:\n %s"),
                    $owner, Course::find($portfolio->seminar_id)->name, $link
                );
                break;
            case 'freigabe':
                $mail_subj = 'Neue Portfolio Freigabe von ' . $owner;
                $mail_msg  = sprintf(
                    _("Neue Freigabe von '%s'\n"
                        . "in: %s \n"
                        . "Direkt zum freigegebenen Inhalt:\n %s"),
                    $owner, Course::find($portfolio->seminar_id)->name, $link
                );
                break;
        }


        $rec_uname = [];
        //id ist kein user sondern supervisorgruppe
        if (!User::find($user_id)) {
            $supervisor_group_user = SupervisorGroup::find($user_id)->user;

            foreach ($supervisor_group_user as $group_user) {
                $rec_uname[] = get_username($group_user->user_id);
            }
        } else $rec_uname[] = $user_id;

        $messaging                = new messaging();
        $messaging->send_as_email = true;
        $messaging->insert_message(
            $mail_msg,
            $rec_uname,
            '____%system%____',
            '',
            '',
            '',
            null,
            $mail_subj,
            "",
            'normal',
            trim(Request::get("message_tags")) ?: null
        );
    }


    /**
     * Liefert die zuverbleibenden Tage (gerundet) zwischen
     * jetzt und Abgabetermin des passenden Templates
     * der Gruppe. Liefert 0 wenn das Abgabedatum überschritten wurde
     **/
    public static function getDaysLeft($deadline)
    {
        $now      = time();

        if ($now < $deadline) {
            $daysleft = abs($now - $deadline) / 60 / 60 / 24;
            return round($daysleft, 0);
        } else {
            return 0;
        }
    }

    /**
     * Liefert einen CoursewareLink für das erste Kapitel eines Templates eines Users
     **/
    public static function getLinkOfFirstChapter($template_id, $seminar_id)
    {
        $templateChapters   = EportfolioModel::getChapters($template_id);
        $vorlagenchapter    = $templateChapters[0]['id'];
        $portfolio_block_id = BlockInfo::findOneBySQL(
            'vorlagen_block_id = :vorlagenchapter AND Seminar_id = :cid',
            [':cid' => $seminar_id, ':vorlagenchapter' => $vorlagenchapter]);
        return URLHelper::getURL('plugins.php/courseware/courseware', ['cid' => $seminar_id, 'selected' => $portfolio_block_id->block_id]);
    }

    public static function getLastOwnerEdit($sem_id)
    {
        $last_edit     = DBManager::get()->fetchColumn(
            "SELECT chdate FROM mooc_blocks WHERE Seminar_id = :id ORDER BY chdate DESC",
            [':id' => $sem_id]
        );
        $last_freigabe = EportfolioActivity::getLastFreigabeOfPortfolio($sem_id);

        $date = max([$last_edit, $last_freigabe]);

        if ($date) {
            return date('d.m.Y, H:i:s', $date);
        }

        return _('unbekannt');
    }

    /**
     * TODO: Kann in createPortfolio_action evtl. eingebaut werden
     * Erstellt für einen User ein Portfolio
     * Gibt die Seminar_id des Portfolios zurück
     * **/
    public static function createPortfolioForUser($group_id, $user_id, $plugin)
    {
        $group       = SupervisorGroup::findOneById($group_id);
        $groupname   = Seminar::GetInstance($group->Seminar_id);
        $sem_type_id = EportfolioModel::getPortfolioSemId();

        $owner            = User::find($user_id);
        $owner_fullname   = $owner['Vorname'] . ' ' . $owner['Nachname'];
        $sem_name         = "Veranstaltungsportfolio: " . $groupname->getName() . " (" . $owner_fullname . ")";
        $sem_description  = "Dieses Portfolio wurde Ihnen von einem Supervisor zugeteilt";
        $current_semester = Semester::findCurrent();

        $sem              = new Seminar();
        $sem->Seminar_id  = $sem->createId();
        $sem->name        = $sem_name;
        $sem->description = $sem_description;
        $sem->status      = $sem_type_id;
        $sem->read_level  = 1;
        $sem->write_level = 1;
        $sem->institut_id = Config::Get()->STUDYGROUP_DEFAULT_INST;
        $sem->visible     = 0;
        $sem_id           = $sem->Seminar_id;

        // set portfolio icon as as course avatar
        $avatar = CourseAvatar::getAvatar($sem_id);
        $avatar->createFrom($plugin->getpluginPath() . '/assets/images/avatare/eportfolio.png');

        $sem->addMember($user_id, 'dozent'); // add user to his seminar

        /**
         * Alle Supervisoren hinzufügen
         * **/

        foreach ($group->user as $supervisor) {
            $sem->addMember($supervisor->user_id, 'autor');
        }

        $sem->store();

        self::create([
            'Seminar_id' => $sem_id,
            'owner_id'   => $user_id,
            'group_id'   => $group->Seminar_id
        ]);

        // create portfolio statusgroup in new portfolio-seminar
        $id = md5($group_id . $sem_id);

        if (!$sgroup = Statusgruppen::find($id)) {
            // create new statusgroup
            $sgroup = Statusgruppen::create([
                'statusgruppe_id' => $id,
                'name'            => 'Berechtigte für Portfolioarbeit',
                'range_id'        => $sem_id
            ]);
        }

        // create basic courseware block, prevents creation of dummy blocks by courseware
        $block = new Mooc\DB\Block();

        $block->setData(array(
            'seminar_id' => $sem_id,
            'parent_id'  => null,
            'type'       => 'Courseware',
            'title'      => 'Courseware',
            'position'   => 0
        ));

        $block->store();

        return $sem->Seminar_id;
    }

    /**
     * Gibt eine Liste mit den Template_ids zurück
     * die einem Nutzer noch nicht verteilt wurden
     * innerhalb einer Veranstaltung
     **/
    public static function getNotSharedTemplatesOfUserInGroup($group_id, $user_id, $portfolio_id)
    {
        $return = [];

        $template_list = EportfolioGroupTemplates::getGroupTemplates($group_id);
        foreach ($template_list as $template) {
            $template_chapters = EportfolioModel::getChapters($template);
            foreach ($template_chapters as $chapter) {
                if (!EportfolioModel::getUserPortfolioBlockId($portfolio_id, $chapter['id'])) {
                    array_push($return, $template);
                }
            }
        }

        return array_unique($return);
    }

    public static function getPortfolioSemId()
    {
        foreach ($GLOBALS['SEM_TYPE'] as $id => $sem_type) { //get the id of ePortfolio Seminarclass
            if ($sem_type['name'] == 'ePortfolio') {
                return $id;
            }
        }
    }

    /**
     * Gibt die ID des Portfolios des Nutzers in einer Gruppe zurück
     **/
    public static function getPortfolioIdOfUserInGroup($user_id, $course_id)
    {
        return self::findOneBySql('owner_id = ? AND group_id = ?', [
            $user_id, $course_id
        ])->Seminar_id;
    }

    public static function getGroupMembers($course_id)
    {
        $users   = [];

        $course = Course::find($course_id);
        $user_ids = $course->members->filter(function ($a) {
            return $a['status'] === 'autor';
        })->pluck('user_id');

        $users = User::findMany($user_ids, "ORDER BY Nachname, Vorname");

        return $users;
    }

    /**
     * Gibt die Anzahl aller Kapitel (Chapter) in den Templates wieder
     **/
    public static function getAnzahlAllerKapitel($group_id)
    {
        return (int)DBManager::get()->fetchColumn(
            "SELECT COUNT(`type`)
                FROM `mooc_blocks`
                WHERE `seminar_id` IN (SELECT `Seminar_id` FROM `eportfolio_group_templates` WHERE `group_id` = ?) AND `type` = 'Chapter'",
            [$group_id]
        );
    }

    public static function getRelatedStudentPortfolios($course_id)
    {
        $course     = new Course($course_id);
        $members    = $course->members;
        $portfolios = [];

        foreach ($members as $key) {
            $portfolio = EportfolioModel::findBySQL('group_id = :groupid AND owner_id = :value', [
                ':groupid' => $course_id, ':value' => $key->user_id
            ]);

            if ($portfolio) {
                array_push($portfolios, $portfolio[0]->Seminar_id);
            }
        }

        if (!empty($portfolios)) {
            return $portfolios;
        } else {
            return null;
        }
    }
}
