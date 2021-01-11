<?

/**
 * @author  <asudau@uos.de>
 *
 * @property varchar $id
 * @property varchar $seminar_id
 * @property string $name
 * @property SupervisorGroupUser[] $user
 */
class SupervisorGroup extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'supervisor_group';

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
        // check, if user is already in group

        $user = SupervisorGroupUser::findOneBySQL('supervisor_group_id = ? AND user_id = ?',
            [$this->id, $user_id]
        );

        if ($user) {
            return false;
        }

        $user = SupervisorGroupUser::build([
                'supervisor_group_id' => $this->id,
                'user_id'             => $user_id
            ]
        );

        if ($user->store()) {
            //als user in alle ePortfolios der StudentInnen eintragen
            $seminare = EportfolioModel::getRelatedStudentPortfolios($this->seminar_id);

            if ($seminare) {
                foreach ($seminare as $seminar) {
                    $seminar = Seminar::GetInstance($seminar);
                    $seminar->addMember($user_id, 'autor');
                    $seminar->store();
                }
            }
        }

        return true;
    }

    public function deleteUser($user_id)
    {
        //aus Supervisorgruppe austragen
        $user = SupervisorGroupUser::findOneBySQL('user_id = :user_id AND supervisor_group_id = :supervisor_group_id',
            [':user_id' => $user_id, ':supervisor_group_id' => $this->id]);

        if ($user && $user->delete()) {
            //als user aus allen ePortfolios der StudentInnen austragen
            $seminare = EportfolioModel::getRelatedStudentPortfolios($this->seminar_id);

            if ($seminare) {
                foreach ($seminare as $seminar) {
                    $seminar = Seminar::GetInstance($seminar);
                    $seminar->deleteMember($user_id);
                    $seminar->store();
                }
            }
        }
    }

    public function isUserInGroup($user_id, $course_id)
    {
        $supervisor_group_id = SupervisorGroup::findBySQL('Seminar_id = ?', [$course_id])[0]->id;
        $supervisor_users = SupervisorGroupUser::findBySupervisorGroupId($supervisor_group_id);

        foreach ($supervisor_users as $user) {
            if($user->user_id === $user_id) {
                return true;
            }
        }
        return false;
    }
}
