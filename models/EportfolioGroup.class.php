<?

/**
 * @author  <asudau@uos.de>
 *
 * @property varchar $seminar_id
 * @property varchar $owner_id
 * @property text $templates
 * @property varchar $supervisor_group_id
 * @property CourseMember[] $user
 */
class EportfolioGroup extends SimpleORMap
{
    //testen
    protected static function configure($config = [])
    {
        $config['db_table'] = 'eportfolio_groups';

        $config['belongs_to']['group_owner'] = [
            'class_name'  => 'User',
            'foreign_key' => 'owner_id',];

        $config['has_many']['user'] = [
            'class_name'        => 'CourseMember',
            'assoc_foreign_key' => 'seminar_id',
            'assoc_func'        => 'findByCourse',
        ];

        parent::configure($config);
    }


    public static function getGroupMember($group)
    {
        $users   = [];
        $userIds = $group->user->filter(function ($a) {
            return $a['status'] === 'autor';
        })->pluck('user_id');
        $users   = User::findMany($userIds, "ORDER BY Nachname, Vorname");

        return $users;
    }

    public static function getAllSupervisors($id)
    {
        $group           = new EportfolioGroup($id);
        $supervisorGroup = new SupervisorGroup($group->supervisor_group_id);
        $array           = [];
        foreach ($supervisorGroup->user as $user) {
            array_push($array, $user->user_id);
        }
        return $array;
    }

    //TODO anpassen
    public static function newGroup($owner, $sem_id)
    {

        $course = Course::find($sem_id);

        $supervisorgroup       = new SupervisorGroup();
        $supervisorgroup->name = $course->name;
        $supervisorgroup->store();

        $group                      = new EportfolioGroup($sem_id);
        $group->supervisor_group_id = $supervisorgroup->id;
        $group->owner_id            = $owner;
        $group->store();

        $supervisorgroup->eportfolio_group = $group;
        $supervisorgroup->store();
        $supervisorgroup->addUser($owner);

        return $group;
    }

    public static function getOwner($course_id)
    {
        return DBManager::get()->fetchColumn("SELECT owner_id FROM eportfolio_groups WHERE seminar_id = ?", [$course_id]);
    }

    public static function getAllGroupsOfUser($user_id)
    {
        return DBManager::get()->fetchAll(
            "SELECT seminar_id FROM eportfolio_groups_user WHERE user_id = ?",
            [$user_id]
        );
    }

    //brauchen wir auf jeden Fall
    public static function getAllGroupsOfSupervisor($userId)
    {
        $ownGroups   = EportfolioGroup::findBySQL('owner_id = :id', [':id' => $userId]);
        $addedGroups = SupervisorGroupUser::getSupervisorGroups($userId);

        $array = [];
        foreach ($ownGroups as $group) {
            array_push($array, $group->seminar_id);
        }

        foreach ($addedGroups as $group) {
            if ($group->eportfolio_group->seminar_id) {
                array_push($array, $group->eportfolio_group->seminar_id);
            }
        }

        return array_unique($array);
    }

    public static function getSupervisorGroupId($id)
    {
        $group = self::find($id);
        return $group->supervisor_group_id;
    }

    public function getRelatedStudentPortfolios()
    {
        $member     = $this->user;
        $portfolios = [];

        foreach ($member as $key) {
            $portfolio = Eportfoliomodel::findBySQL('group_id = :groupid AND owner_id = :value', [':groupid' => $this->seminar_id, ':value' => $key->user_id]);
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

    public static function deleteGroup($cid)
    {
        #supervisorgroup holen
        $supervisor_group_id = self::findById($cid)->supervisor_group_id;

        // #eportfolio_groups löschen
        $group = new EportfolioGroup($cid);
        $group->delete();

        // #eportfolio_groups_user löschen
        $query     = "DELETE FROM eportfolio_groups_user WHERE seminar_id = :seminar_id";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':seminar_id' => $cid]);

        // #seminar mit id löschen
        $course = new Seminar($cid);
        $course->delete();

        #supervisor_group löschen
        SupervisorGroup::deleteGroup($supervisor_group_id);

        #eportfolio mit group_id löschen
        $eportfolio = new Eportfoliomodel($cid);
        $eportfolio->delete();
    }

    /**
     * Erstellt einen Eintrag in der eportfolio_group_templates Tabelle
     * Damit es nicht knallt wenn beim verteilen einer Vorlage mal was schief geht machen wir hier ein INSERT IGNORE
     * Langfristig könnte man beim Verteilen von Vorlagen noch was drehen, dass das Template nur eingetragen wird, wenn wirklich alles rund gelaufen ist..
     * user_id ist in diesem Fall die User_id des Nutzers der die Vorlage verteilt
     **/
    public static function createTemplateForGroup($group_id, $template_id, $user_id)
    {
        $template_entry                 = new EportfolioGroupTemplates();
        $template_entry->group_id       = $group_id;
        $template_entry->Seminar_id     = $template_id;
        $template_entry->verteilt_durch = $user_id;
        $template_entry->store();
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

    //all activities in Group ($user can be used to check if activity is a new one for given user)
    public function getActivities($user = null)
    {
        $activities = EportfolioActivity::getActivitiesForGroup($this->seminar_id);
        return $activities;
    }

    public static function getActivitiesOfUser($seminar_id, $user)
    {
        $activities = EportfolioActivity::getActivitiesOfGroupUser($seminar_id, $user);
        return $activities;
    }

    /**
     * Gibt die der neuen Aktivitäten eines Nutzers in der Gruppe zurück
     **/
    public function getNumberOfNewActivities()
    {
        $activities = EportfolioActivity::newActivities($this->seminar_id);
        if (is_array($activities)) {
            return count($activities);
        }
    }

    /**
     * Gibt die ID des Portfolios des Nutzers in einer Gruppe zurück
     **/
    public static function getPortfolioIdOfUserInGroup($user_id, $group_id)
    {
        return DBManager::get()->fetchColumn(
            "SELECT Seminar_id FROM eportfolio WHERE owner_id = :owner_id AND group_id = :group_id",
            [':owner_id' => $user_id, ':group_id' => $group_id]
        );
    }

    public function isSupervisor($user_id)
    {
        return SupervisorGroupUser::findBySQL('$supervisor_group_id = :group_id AND user_id = :user_id', [':group_id' => $this->supervisor_group_id, ':user_id' => $user_id]);
    }
}
