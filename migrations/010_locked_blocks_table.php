<?php

require __DIR__.'/../vendor/autoload.php';

class LockedBlocksTable extends Migration
{
    public function description () {
        return 'add tables for blocks that cant be edited after distribution';
    }


    public function up () {
        $db = DBManager::get();
        $db->exec("CREATE TABLE IF NOT EXISTS `eportfolio_locked_blocks` (
          `Seminar_id` varchar(32) NOT NULL,
          `block_id` int(11) NOT NULL DEFAULT '0',
          `mkdate` int(11) NOT NULL,
          `chdate` int(11) NOT NULL,
          PRIMARY KEY (block_id)
        ) ");

        SimpleORMap::expireTableScheme();
    }


    public function down () {


        $db = DBManager::get();
        $db->exec("DROP TABLE IF EXISTS eportfolio_locked_blocks");    
        SimpleORMap::expireTableScheme();

    }


}
