<?php

require __DIR__.'/../vendor/autoload.php';

class MigrateFreigaben extends Migration
{
    public function description () {
        return 'Migrate table eportfolio_freigaben';
    }


    public function up ()
    {
        $db = DBManager::get();

        // convert all accesses from eportfolio_freigaben to courseware permissions
        $delete_stmt = $db->prepare("DELETE FROM eportfolio_freigaben
            WHERE user_id = ? AND block_id = ?");

        // update permissions for supervisorgroups and single users
        $results = $db->query("SELECT user_id, block_id
            FROM eportfolio_freigaben");

        while ($data = $results->fetch(PDO::FETCH_ASSOC)) {
            EportfolioFreigabe::setAccess(
                $data['user_id'], $data['block_id'], true
            );

            $delete_stmt->execute([$data['user_id'], $data['block_id']]);
        }

        // $db->exec('DROP TABLE eportfolio_freigaben');
    }


    public function down ()
    {
    }
}
