<?php

require __DIR__.'/../vendor/autoload.php';

class GroupidInEportfolios extends Migration
{
    public function description () {
        return 'add groupid column';
    }


    public function up () {
        $db = DBManager::get();
        $db->exec("ALTER TABLE eportfolio ADD COLUMN group_id varchar(32) NOT NULL");
        
        
        SimpleORMap::expireTableScheme();
    }


    public function down () {


    }


}
