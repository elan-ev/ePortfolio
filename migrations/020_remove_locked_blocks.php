<?php

require __DIR__.'/../vendor/autoload.php';

class RemoveLockedBlocks extends Migration
{
    public function description () {
        return 'Remove obsolete table eportfolio_locked_blocks';
    }


    public function up ()
    {
        $db = DBManager::get();

        $db->exec('DROP TABLE eportfolio_locked_blocks');
    }


    public function down ()
    {
    }
}
