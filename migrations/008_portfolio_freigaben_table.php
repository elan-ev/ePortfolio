<?php

require __DIR__.'/../vendor/autoload.php';

class PortfolioFreigabenTable extends Migration
{
    public function description () {
        return 'add tables for portfolio freigaben';
    }


    public function up () {
        $db = DBManager::get();
        $db->exec("CREATE TABLE IF NOT EXISTS `eportfolio_freigaben` (
          `Seminar_id` varchar(32) NOT NULL,
          `block_id` int(11) NOT NULL DEFAULT '0',
          `user_id` varchar(32) NOT NULL,
          `mkdate` int(11) NOT NULL,
          `chdate` int(11) NOT NULL,
          PRIMARY KEY (Seminar_id, block_id, user_id)
        ) ");

        SimpleORMap::expireTableScheme();
    }


    public function down () {


        $db = DBManager::get();
        $db->exec("DROP TABLE IF EXISTS eportfolio_freigaben");    
        SimpleORMap::expireTableScheme();

    }


}
