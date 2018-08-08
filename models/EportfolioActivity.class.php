<?php

include_once __DIR__.'/EportfolioGroup.class.php';
include_once __DIR__.'/Eportfoliomodel.class.php';

/**
 * @author  <asudau@uos.de>
 *
 * @property int        $id
 * @property string     $group_id (Seminar)
 * @property string     $eportfolio_id (Seminar)
 * @property string     $type
 * @property string     $user_id (User)
 * @property string     $block_id (Mooc\Block)
 * @property int        $mkdate
 * @property int        $chdate
 */

class EportfolioActivity extends SimpleORMap
{

/*
    public function __construct($id = null) {

        parent::__construct($id);
        
    }
  */  
    protected static function configure($config = array())
    {
        $config['db_table'] = 'eportfolio_activities';

        $config['belongs_to']['user'] = array(
            'class_name' => 'User',
            'foreign_key' => 'user_id', );

        $config['belongs_to']['group'] = array(
            'class_name' => 'EportfolioGroup',
            'foreign_key' => 'group_id', );
        
        $config['belongs_to']['eportfolio'] = array(
            'class_name' => 'Eportfoliomodel',
            'foreign_key' => 'eportfolio_id', );

        parent::configure($config);
    }
    
    public static function newEntry($group_id, $eportfolio_id, $type, $block_id){
        $activity = new EportfolioActivity('xvyab');
        
    }
    
    public static function getActivitiesForGroup($seminar_id){
 
        return EportfolioActivity::findByGroup_id($seminar_id);
    }

    public function getDummyActivities($seminar_id){
        global $user;
        $activities = array();
        //$activities[] = EportfolioActivity::getDummyActivity('freigabe', $user, 1532603297, URLHelper::getLink('dispatch.php/start'), true);
        //$activities[] = EportfolioActivity::getDummyActivity('aenderung', $user, 1532403297, URLHelper::getLink('dispatch.php/start'), true);
        //$activities[] = EportfolioActivity::getDummyActivity('freigabe', $user, 1532503297, URLHelper::getLink('dispatch.php/start'), true);
        //$activities[] = EportfolioActivity::getDummyActivity('notiz', $user, 1532609297, URLHelper::getLink('dispatch.php/start'), true);
        //$activities[] = EportfolioActivity::getDummyActivity('notiz', $user, 1532653297, URLHelper::getLink('dispatch.php/start'), true);
        return $activities;
    }
    
    public function getDummyActivitiesOfUser($seminar_id, $user){
        $activities = array();
        //$activities[] = EportfolioActivity::getDummyActivity('freigabe', $user, 1532653297, URLHelper::getLink('dispatch.php/start'), true);
        //$activities[] = EportfolioActivity::getDummyActivity('aenderung', $user, 1532413297, URLHelper::getLink('dispatch.php/start'), true);
        return $activities;
    }
    
    public function getDummyActivity($type, $user, $date, $link, $is_new) {

        $activity = new EportfolioActivity();
        $activity->type = $type;
        $activity->user = $user;
        $activity->date = $date;
        $activity->link = $link;
        $activity->is_new = $is_new;
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
        return $activity;
    }
    
}