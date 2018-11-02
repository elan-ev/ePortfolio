<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class HelpText extends Migration
{
    public function description () {
        return 'add tables for portfolio plugin';
    }


    public function up () {

        $query = "INSERT IGNORE INTO `help_content` (`content_id`, `language`, `content`, `route`, `studip_version`, `position`, `custom`, `visible`, `author_email`, `installation_id`) VALUES
        ('5a90d1219dbeb07c124156592123d877', 'de', 'Sie haben hier eine Übersicht über Ihre Vorlagen und Informationen ob und wann diese in der aktuellen Veranstaltung bereits verteilt wurden. Sie können einen Abgabetermin definieren, bis wann die Studierenden die Inhalte bearbeitet und für Ihren Zugriff freigegeben haben sollen. Im unteren Teil finden Sie eine Übersicht über die Studierenden und ihren Bearbeitungsfortschritt. Im Menu links unter Activity Feed finden Sie eine Auflistung der aktuellen Aktivitäten (Freigaben etc.)', 'plugins.php/eportfolioplugin/showsupervisor', '4.1', 0, 0, 1, '', '')
        ";
        DBManager::get()->exec($query);
    }
    
    public function down () {
        
    }
}
    