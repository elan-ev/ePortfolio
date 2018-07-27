<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class EportfolioActivity
{
    var $type;       // string
    var $message;   // String
    var $user;            // Object of Class User
    var $date;      //unix timestamp
    var $link;  //string
    var $is_new;    //bollean



    public function __construct($type, $user, $date, $link, $is_new) {

        //$this->db_table = 'eportfolio_block_infos';
        $this->type = $type;
        $this->user = $user;
        $this->date = $date;
        $this->link = $link;
        $this->is_new = $is_new;
        switch($type){
            case 'freigabe':
                $this->message = 'Ein neuer Abschnitt wurde für Ihren Zugriff freigegeben';
                break;
            case 'notiz':
                $this->message = 'Eine neue Notiz wurde erstellt';
                break;
            case 'aenderung':
                $this->message = 'Ein bereits freigegebener Abschnitt wurde verändert';
                break;
        }


        //parent::__construct($id);
    }

    public function getDummyActivities($seminar_id){
        global $user;
        $activities = array();
        $activities[] = new EportfolioActivity('freigabe', $user, 1532603297, URLHelper::getLink('dispatch.php/start'), true);
        $activities[] = new EportfolioActivity('aenderung', $user, 1532403297, URLHelper::getLink('dispatch.php/start'), true);
        $activities[] = new EportfolioActivity('freigabe', $user, 1532503297, URLHelper::getLink('dispatch.php/start'), true);
        $activities[] = new EportfolioActivity('notiz', $user, 1532609297, URLHelper::getLink('dispatch.php/start'), true);
        $activities[] = new EportfolioActivity('notiz', $user, 1532653297, URLHelper::getLink('dispatch.php/start'), true);
        return $activities;
    }
    
    public function getDummyActivitiesOfUser($seminar_id, $user){
        $activities = array();
        $activities[] = new EportfolioActivity('freigabe', $user, 1532653297, URLHelper::getLink('dispatch.php/start'), true);
        $activities[] = new EportfolioActivity('aenderung', $user, 1532413297, URLHelper::getLink('dispatch.php/start'), true);
        return $activities;
    }

}