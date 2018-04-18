<?php

require __DIR__.'/../vendor/autoload.php';

class LastVisitedTable extends Migration
{
    public function description () {
        return 'add tables for last visits of portfolio elements';
    }


    public function up () {
        $db = DBManager::get();
        $db->exec("CREATE TABLE `eportfolio_last_visited` (
          `object_id` varchar(32) NOT NULL,
          `user_id` varchar(32) NOT NULL DEFAULT '0',
          `time` int(11) NOT NULL,
          PRIMARY KEY (object_id, user_id)
        ) ");
       
        SimpleORMap::expireTableScheme();
    }


    public function down () {
        

        $db = DBManager::get();
        $db->exec("DROP TABLE eportfolio_last_visited");    
        SimpleORMap::expireTableScheme();

    }


}
