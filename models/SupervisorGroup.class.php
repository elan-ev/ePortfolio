<?

/**
 * @author  <asudau@uos.de>
 *
 * @property varchar $id
 * @property varchar $eportfolio_group
 * @property string $name
 * @property SupervisorGroupUser[] $user
 */
class SupervisorGroup extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'supervisor_group';

        $config['has_one']['eportfolio_group'] = [
            'class_name'        => 'EportfolioGroup',
            'assoc_foreign_key' => 'supervisor_group_id',
        ];

        $config['has_many']['user'] = [
            'class_name'        => 'SupervisorGroupUser',
            'assoc_foreign_key' => 'supervisor_group_id',
            'assoc_func'        => 'findBySupervisorGroupId',
            'on_delete'         => 'delete',
            'on_store'          => 'store',
        ];

        parent::configure($config);
    }

    public function addUser($user_id)
    {
        $user = SupervisorGroupUser::build([
                'supervisor_group_id' => $this->id,
                'user_id'             => $user_id
            ]
        );

        if ($user->store()) {
            //als user in alle ePortfolios der StudentInnen eintragen
            $group    = $this->eportfolio_group;
            $seminare = $group->getRelatedStudentPortfolios();

            if ($seminare) {
                foreach ($seminare as $seminar) {
                    $seminar = Seminar::GetInstance($seminar);
                    $seminar->addMember($user_id, 'autor');
                    $seminar->store();
                }
            }
        }
    }

    public function deleteUser($user_id)
    {
        //aus Supervisorgruppe austragen
        $user = SupervisorGroupUser::findOneBySQL('user_id = :user_id AND supervisor_group_id = :supervisor_group_id',
            [':user_id' => $user_id, ':supervisor_group_id' => $this->id]);

        if ($user->delete()) {
            //als user aus allen ePortfolios der StudentInnen austragen
            $group    = $this->eportfolio_group;
            $seminare = $group->getRelatedStudentPortfolios();
            foreach ($seminare as $seminar) {
                $seminar = Seminar::GetInstance($seminar);
                $seminar->deleteMember($user_id);
                $seminar->store();
            }
        }
    }

    public static function newGroup($name)
    {
        $group       = new SupervisorGroup();
        $group->name = $name;
        $group->store();
    }

    public static function deleteGroup($group_id)
    {
        $group = new SupervisorGroup($group_id);
        $group->delete();
    }


    public function isUserInGroup($user_id, $course_id)
    {
        $supervisor_group_id = EportfolioGroup::findById($course_id)[0]->supervisor_group_id;

        if (!$supervisor_group_id) {
            // only privileged users are allowed to be the first person in the supervisor group
            if ($GLOBALS['perm']->have_studip_perm('tutor', $course_id, $user_id)) {
                $group = EportfolioGroup::newGroup($user_id, $course_id);
                $supervisor_group_id = $group->supervisor_group_id;
            }
        }

        $supervisor_users = SupervisorGroupUser::findBySupervisorGroupId($supervisor_group_id);

        foreach ($supervisor_users as $user) {
            if($user->user_id === $user_id) {
                return true;
            }
        }
        return false;
    }
}
