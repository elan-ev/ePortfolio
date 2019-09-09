<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class PortfolioArchive extends Migration
{
    public function description () {
        return 'add tables for portfolio plugin';
    }


    public function up ()
    {
        $db = DBManager::get();
        $db->exec('CREATE TABLE IF NOT EXISTS `eportfolio_archive` (
          `eportfolio_id` varchar(32) NOT NULL,
          PRIMARY KEY (eportfolio_id)
      )');
    }

    public function down ()
    {
        DBManager::get()->exec('DROP TABLE IF EXISTS eportfolio_archive');
    }
}
