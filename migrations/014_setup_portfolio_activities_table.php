<?php

require __DIR__.'/../vendor/autoload.php';

class SetupPortfolioActivitiesTable extends Migration
{
    public function description () {
        return 'add activities table for portfolio plugin';
    }


    public function up () {
        $db = DBManager::get();
        $db->exec("CREATE TABLE `eportfolio_activities` (
          `id` INT NOT NULL AUTO_INCREMENT,
          `group_id` varchar(32) NULL,
          `eportfolio_id` varchar(32) NULL,
          `type` varchar(32) NOT NULL DEFAULT '0',
          `user_id` varchar(32) NOT NULL,
          `block_id` int(11) DEFAULT NULL,
          `mk_date` int(11) DEFAULT NULL,
          PRIMARY KEY (id)
        ) ");

        SimpleORMap::expireTableScheme();
    }


    public function down () {


        $db = DBManager::get();
        $db->exec("DROP TABLE eportfolio_activities");
        SimpleORMap::expireTableScheme();

    }


}
