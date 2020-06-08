<?php


/**
 * @author  <asudau@uos.de>
 *
 * @property varchar $supervisor_group_id
 * @property varchar $user_id
 */
class SupervisorGroupUser extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'supervisor_group_user';

        $config['has_one']['supervisor_group'] = [
            'class_name'        => 'SupervisorGroup',
            'assoc_foreign_key' => 'supervisor_group_id',
            'assoc_func'        => 'findById'
        ];

        $config['has_one']['user'] = [
            'class_name'        => 'User',
            'assoc_foreign_key' => 'user_id',
            'foreign_key' => 'user_id',
        ];

        parent::configure($config);
    }


    public static function findBySupervisorGroupId($id)
    {
        return SupervisorGroupUser::findBySQL('supervisor_group_id = ?', [$id]);
    }

    public static function getSupervisorGroups($user_id)
    {
        $array     = [];
        $groupUser = SupervisorGroupUser::findBySQL('user_id = ?', [$user_id]);
        foreach ($groupUser as $user) {
            $supervisor_group = new SupervisorGroup($user->supervisor_group_id);
            array_push($array, $supervisor_group);
        }
        return $array;
    }
}
