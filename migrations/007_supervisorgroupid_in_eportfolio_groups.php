<?php

require __DIR__.'/../vendor/autoload.php';

class SupervisorgroupidInEportfolioGroups extends Migration
{
    public function description () {
        return 'add Supervisorgroupid column';
    }


    public function up () {
        $db = DBManager::get();
        $db->exec("ALTER TABLE eportfolio_groups ADD COLUMN supervisor_group_id varchar(32) NOT NULL");
        
        
        SimpleORMap::expireTableScheme();
    }


    public function down () {


    }


}
