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

        
        $blocks = DBManager::get()->fetchAll(
            "SELECT DISTINCT vorlagen_block_id, mooc_blocks.seminar_id FROM eportfolio_block_infos
            JOIN mooc_blocks ON mooc_blocks.id = eportfolio_block_infos.vorlagen_block_id"
        );

        $blocks = array_column($blocks, NULL, 'vorlagen_block_id');

        foreach ($blocks as $block) {
            $stmt = DBManager::get()->prepare("UPDATE eportfolio_block_infos
                    SET template_id = :seminar_id
                    WHERE vorlagen_block_id = :vorlagen_block_id");
            $stmt->execute([":vorlagen_block_id" => $block["vorlagen_block_id"], ":seminar_id" => $block["seminar_id"]]);
        }
    }


    public function down ()
    {
    }
}
