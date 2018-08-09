<?php

require __DIR__.'/../vendor/autoload.php';

class BlockInfoTable extends Migration
{
    public function description () {
        return 'add tables for additional block infos';
    }


    public function up () {
        $db = DBManager::get();
        $db->exec("CREATE TABLE `eportfolio_block_infos` (
          `block_id` int(11) NOT NULL,
          `Seminar_id` varchar(32) NOT NULL DEFAULT '0',
          `vorlagen_block_id` int(11) NOT NULL DEFAULT '0',
          `blocked` boolean NOT NULL,
          `mkdate` int(11) NOT NULL,
          `chdate` int(11) NOT NULL,
          PRIMARY KEY (block_id)
        ) ");
       
        SimpleORMap::expireTableScheme();
    }


    public function down () {
        

        $db = DBManager::get();
        $db->exec("DROP TABLE eportfolio_block_infos");    
        SimpleORMap::expireTableScheme();

    }


}
