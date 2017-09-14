<?php

require __DIR__.'/../vendor/autoload.php';

class SetupPortfolioTables extends Migration
{
    public function description () {
        return 'add tables for portfolio plugin';
    }


    public function up () {
        $db = DBManager::get();
        $db->exec("CREATE TABLE `eportfolio` (
          `Seminar_id` varchar(32) NOT NULL,
          `eportfolio_id` varchar(32) NOT NULL,
          `templateStatus` int(11) NOT NULL DEFAULT '0',
          `owner_id` varchar(32) NOT NULL,
          `supervisor_id` varchar(32) DEFAULT NULL,
          `freigaben_kapitel` text,
          `template_id` varchar(60) DEFAULT NULL,
          `settings` text
        ) ");
        $db->exec("CREATE TABLE `eportfolio_groups` (
          `seminar_id` varchar(32) NOT NULL,
          `owner_id` varchar(32) NOT NULL,
          `templates` text
          )");
        $db->exec("CREATE TABLE `eportfolio_groups_user` (
          `seminar_id` varchar(32) NOT NULL,
          `user_id` varchar(32) NOT NULL,
          `status` tinytext
          )");
        $db->exec("CREATE TABLE `eportfolio_templates` (
          `id` int(11) NOT NULL,
          `temp_name` tinytext NOT NULL,
          `chapters` text NOT NULL,
          `description` text NOT NULL,
          `group_id` text NOT NULL,
          `img` text NOT NULL
          )");
        $db->exec("CREATE TABLE `eportfolio_user` (
          `user_id` varchar(32) NOT NULL,
          `Seminar_id` varchar(32) NOT NULL,
          `eportfolio_id` varchar(32) NOT NULL,
          `status` enum('user','autor','tutor','dozent') NOT NULL,
          `eportfolio_access` text,
          `owner` int(11) NOT NULL
          )");
        
        SimpleORMap::expireTableScheme();
    }


    public function down () {
        

        $db = DBManager::get();
        $db->exec("DROP TABLE eportfolio");
        $db->exec("DROP TABLE eportfolio_user");
        $db->exec("DROP TABLE eportfolio_groups");
        $db->exec("DROP TABLE eportfolio_groups_user");
        $db->exec("DROP TABLE eportfolio_templates");
        SimpleORMap::expireTableScheme();

    }


}
