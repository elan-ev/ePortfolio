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
        
        $config['additional_fields']['is_new']['get'] = function ($item) {
            if($item->group_id && User::findCurrent()->id){
                $seminar_id = $item->group_id; 
                $user_id = User::findCurrent()->id;
                //object_get_visit($object_id, $type, $mode = "last", $open_object_id = '', $user_id = '', $refresh_cache = false)
                $last_visit = object_get_visit($seminar_id, 'sem');
                return $item->mk_date > $last_visit;
            } else {
                return false;
            }
        };
        $config['additional_fields']['link']['get'] = function ($item) {
            return true;
        };
        
        $config['additional_fields']['message']['get'] = function ($item) {
            switch($item->type){
            case 'freigabe':
                $message = 'Ein neuer Abschnitt wurde für Ihren Zugriff freigegeben';
                break;
            case 'notiz':
                $message = 'Eine neue Notiz wurde erstellt';
                break;
            case 'supervisor-notiz':
                $message = 'Eine neue Notiz für die Gruppensupervisoren wurde erstellt';
                break;
            case 'aenderung':
                $message = 'Ein bereits freigegebener Abschnitt wurde verändert';
                break;
            case 'vorlage-erhalten':
                $message = 'In '. Course::find($item->group_id)->name .' wurden neue Portfolio-Inhalte verteilt';
                break;
            case 'vorlage-verteilt':
                $message = 'Es wurden neue Portfolio-Inhalte verteilt';
                break;
            }
            return $message;
        };

        parent::configure($config);
    }
    
    public static function newEntry($group_id, $eportfolio_id, $type, $block_id){
        $activity = new EportfolioActivity('xvyab');
        
    }
    
    public static function getActivitiesForGroup($seminar_id){
 
        return EportfolioActivity::findByGroup_id($seminar_id);
    }

    public function getDummyActivitiesForGroup($seminar_id){
        global $user;
        $activities = array();
        $activities[] = EportfolioActivity::getDummyActivity('freigabe', $user, 1532603297, URLHelper::getLink('dispatch.php/start'), true);
        $activities[] = EportfolioActivity::getDummyActivity('aenderung', $user, 1532403297, URLHelper::getLink('dispatch.php/start'), true);
        $activities[] = EportfolioActivity::getDummyActivity('freigabe', $user, 1532503297, URLHelper::getLink('dispatch.php/start'), true);
        $activities[] = EportfolioActivity::getDummyActivity('notiz', $user, 1532609297, URLHelper::getLink('dispatch.php/start'), true);
        $activities[] = EportfolioActivity::getDummyActivity('notiz', $user, 1532653297, URLHelper::getLink('dispatch.php/start'), true);
        return $activities;
    }
    
    public function getActivitiesOfGroupUser($seminar_id, $user){
        return EportfolioActivity::findBySQL('group_id = :seminar_id AND user_id = :user_id', array('seminar_id' => $seminar_id, ':user_id' => $user));
    }
    
    public function getDummyActivity($type, $user, $date, $link, $is_new) {

        $activity = new EportfolioActivity();
        $activity->type = $type;
        $activity->user_id = $user;
        $activity->mk_date = $date;
        $activity->link = $link;
        $activity->is_new = $is_new;
        
        return $activity;
    }
    
    public function addVorlagenActivity($group_id, $user_id){
        $activity = new EportfolioActivity();
        $activity->type = 'vorlage-verteilt';
        $activity->user_id = $user_id;
        $activity->mk_date = time();
        $activity->group_id = $group_id;
        $activity->store();
        
        foreach(EportfolioGroup::find($group_id)->getRelatedStudentPortfolios() as $portfolio){
            $activity = new EportfolioActivity();
            $activity->type = 'vorlage-erhalten';
            $activity->user_id = $user_id;
            $activity->mk_date = time();
            $activity->group_id = $group_id;
            $activity->eportfolio_id = $portfolio;
            $activity->store();
        }
    }
    
    public function addSupervisornotizActivity($portfolio_id, $user_id, $block_id){
        $activity = new EportfolioActivity();
        $activity->type = 'supervisor-notiz';
        $activity->user_id = $user_id;
        $activity->mk_date = time();
        $group_id = Eportfoliomodel::findBySeminarId($portfolio_id)->group_id;
        $activity->group_id = $group_id ? $group_id : NULL;
        $activity->block_id = intval($block_id);
        $activity->eportfolio_id = $portfolio_id;
        $activity->store();
    }
    
}