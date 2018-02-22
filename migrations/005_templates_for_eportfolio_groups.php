<?php

require __DIR__.'/../vendor/autoload.php';

class TemplatesForEportfolioGroups extends Migration
{
    public function description () {
        return 'add templates column';
    }


    public function up () {
        $db = DBManager::get();
        $db->exec("ALTER TABLE eportfolio_groups ADD COLUMN templates text NOT NULL");
        
        
        SimpleORMap::expireTableScheme();
    }


    public function down () {


    }


}
