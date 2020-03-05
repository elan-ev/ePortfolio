<?php

require __DIR__.'/../vendor/autoload.php';

class ChangeBlockInfosAndFavorites extends Migration
{
    public function description () {
        return 'Add template_id to block_infos and remove favorite flag from group_templates';
    }


    public function up () {
        $db = DBManager::get();

        $db->exec("ALTER TABLE `eportfolio_block_infos`
            ADD COLUMN `template_id` varchar(32) NOT NULL DEFAULT '0'
            AFTER `vorlagen_block_id`");

        $db->exec("ALTER TABLE eportfolio_group_templates
            DROP COLUMN `favorite`");

        SimpleORMap::expireTableScheme();
    }


    public function down ()
    {
    }
}
