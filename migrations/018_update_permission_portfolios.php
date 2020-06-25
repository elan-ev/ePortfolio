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

        // EportfolioFreigabe::setAccess($user_id, $chapter_id, $status);

        // update permissions for supervisorgroups


        // update permissions for single users
    }


    public function down ()
    {
    }
}
