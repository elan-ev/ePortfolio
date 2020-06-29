<?php

require __DIR__.'/../vendor/autoload.php';

class UpdatePermissionPortfolios extends Migration
{
    public function description () {
        return 'Update permissions for users in portfolios';
    }


    public function up ()
    {
        $db = DBManager::get();

        // clean out obsolete fields from eportfolio table
        $db->exec('ALTER TABLE eportfolio
            DROP supervisor_id');

        $db->exec('ALTER TABLE eportfolio
            DROP template_id');

        $db->exec('ALTER TABLE eportfolio
            DROP settings');

        $db->exec('ALTER TABLE eportfolio
            ADD INDEX Seminar_id (Seminar_id)');

        $db->exec('ALTER TABLE eportfolio
            ADD INDEX group_id (group_id)');

        // add Seminar_id to supervisor_group and remove eportfolio_groups afterwars, since they serve no further purpose
        $db->exec('ALTER TABLE supervisor_group
            ADD Seminar_id VARCHAR(32) NOT NULL AFTER id');

        $db->exec('ALTER TABLE supervisor_group
            DROP supervisor_group_id');

        $db->exec('ALTER TABLE supervisor_group
            ADD INDEX Seminar_id (Seminar_id)');

        $db->exec('UPDATE supervisor_group
            SET Seminar_id = (
                 SELECT Seminar_id FROM eportfolio_groups
                 WHERE supervisor_group_id = supervisor_group.id
            )');

        $db->exec('DROP TABLE eportfolio_groups');

        // check all portfolios, set supervisors to 'autor' and all other users (besides the owner) to 'user'
        $results = $db->query("SELECT seminar_user.Seminar_id, seminar_user.user_id FROM seminar_user
            JOIN eportfolio ON (seminar_user.Seminar_id = eportfolio.Seminar_id)
            LEFT JOIN supervisor_group AS sg  ON (sg.Seminar_id = eportfolio.group_id)
            LEFT JOIN supervisor_group_user AS sgu ON (
                sg.id = sgu.supervisor_group_id
                AND sgu.user_id = seminar_user.user_id
            )
            WHERE seminar_user.status = 'autor'
                AND sgu.user_id IS NULL
        ");

        $stmt = $db->prepare("UPDATE seminar_user SET status ='user'
            WHERE Seminar_id = ? AND user_id = ?");

        while ($data = $results->fetch(PDO::FETCH_ASSOC)) {
            $stmt->execute([$data['Seminar_id'], $data['user_id']]);
        }

        // convert all accesses from eportfolio_freigaben to courseware permissions

        // update permissions for supervisorgroups and single users
        $results = $db->query("SELECT user_id, block_id
            FROM eportfolio_freigaben");

        while ($data = $results->fetch(PDO::FETCH_ASSOC)) {
            EportfolioFreigabe::setAccess(
                $data['user_id'], $data['block_id'], true
            );
        }

        // $db->exec('DROP TABLE eportfolio_freigaben');
    }


    public function down ()
    {
    }
}
