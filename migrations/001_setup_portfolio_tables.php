<?php

require __DIR__ . '/../vendor/autoload.php';

class SetupPortfolioTables extends Migration
{
    public function description()
    {
        return 'add tables for portfolio plugin';
    }
    
    public function up()
    {
        $db = DBManager::get();
        $db->exec("CREATE TABLE `eportfolio` (
          `Seminar_id` varchar(32) NOT NULL,
          `eportfolio_id` varchar(32) NOT NULL,
          `templateStatus` int(11) NOT NULL DEFAULT '0',
          `owner_id` varchar(32) NOT NULL,
          `supervisor_id` varchar(32) DEFAULT NULL,
          `template_id` varchar(60) DEFAULT NULL,
          `settings` text,
          PRIMARY KEY (eportfolio_id)
        ) ");
        $db->exec("CREATE TABLE `eportfolio_groups` (
          `seminar_id` varchar(32) NOT NULL,
          `owner_id` varchar(32) NOT NULL,
          PRIMARY KEY (seminar_id)
          )");
        $db->exec("CREATE TABLE `eportfolio_user` (
          `user_id` varchar(32) NOT NULL,
          `Seminar_id` varchar(32) NOT NULL,
          `eportfolio_id` varchar(32) NOT NULL,
          `status` enum('user','autor','tutor','dozent') NOT NULL,
          `owner` int(11) NOT NULL,
          PRIMARY KEY (eportfolio_id, user_id)
          )");
        $db->exec("CREATE TABLE `supervisor_group` (
          `id` varchar(32) NOT NULL,
          `supervisor_group_id` varchar(32) NOT NULL,
          `name` varchar(100) NOT NULL,
          PRIMARY KEY (id)
          )");
        $db->exec("CREATE TABLE `supervisor_group_user` (
          `supervisor_group_id` varchar(32) NOT NULL,
          `user_id` varchar(32) NOT NULL,
          PRIMARY KEY (supervisor_group_id, user_id)
          )");
        $db->exec("CREATE TABLE `eportfolio_group_templates` (
         `group_id` varchar(32) NOT NULL,
         `Seminar_id` varchar(32) NOT NULL,
         `favorite` int,
         `mkdate` int(11),
         `abgabe_datum` int(11),
         `verteilt_durch` varchar(32),
         PRIMARY KEY (group_id, Seminar_id)
         )");
        
        SimpleORMap::expireTableScheme();
    }
    
    
    public function down()
    {
        
        
        $db = DBManager::get();
        $db->exec("DROP TABLE eportfolio");
        $db->exec("DROP TABLE eportfolio_user");
        $db->exec("DROP TABLE eportfolio_groups");
        $db->exec("DROP TABLE eportfolio_user");
        $db->exec("DROP TABLE supervisor_group");
        $db->exec("DROP TABLE supervisor_group_user");
        $db->exec("DROP TABLE eportfolio_group_templates");
        SimpleORMap::expireTableScheme();
        
    }
    
    
}
