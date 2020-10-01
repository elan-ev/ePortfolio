<?php

require __DIR__.'/../vendor/autoload.php';

class RemoveTemplateStatus extends Migration
{
    public function description () {
        return 'Update permissions for users in portfolios';
    }


    public function up ()
    {
        $db = DBManager::get();

        // clean out obsolete fields from eportfolio table
        $db->exec('ALTER TABLE eportfolio
            DROP templateStatus');
    }


    public function down ()
    {
    }
}
