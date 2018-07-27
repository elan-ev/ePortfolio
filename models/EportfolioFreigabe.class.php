
<?php

include_once __DIR__.'/Eportfoliomodel.class.php';
include_once __DIR__.'/EportfolioGroup.class.php';
include_once __DIR__.'/SupervisorGroupUser.class.php';

/**
 * @author  <asudau@uos.de>
 *
 * @property string     $Seminar_id
 * @property string     $block_id
 * @property string     $user_id
 * @property int        $mkdate
 * @property int        $chdate
 */
class EportfolioFreigabe extends SimpleORMap
{

    public $errors = array();

    /**
     * Give primary key of record as param to fetch
     * corresponding record from db if available, if not preset primary key
     * with given value. Give null to create new record
     *
     * @param mixed $id primary key of table
     */
    public function __construct($id = null) {

        $this->db_table = 'eportfolio_freigaben';

        parent::__construct($id);
    }
    
    //EportfolioFreigabe::hasAccess($user_id, $seminar_id, $chapter_id)
    public static function hasAccess($user_id, $seminar_id, $chapter_id){
        
        $portfolio = Eportfoliomodel::findBySeminarId($seminar_id);
        
        //Wenn das Portfolio Teil einer Gruppe mit zugehöriger Supervisorgruppe ist:
        //checke ob user Teil der Supervisorgruppe ist und prüfe in diesem Fall Berechtigung für Supervisorgruppe
        if ($portfolio->group_id){
            $portfoliogroup = EportfolioGroup::findbySQL('seminar_id = :id', array(':id'=> $portfolio->group_id));
            
        } if ($portfoliogroup[0]->supervisor_group_id){
            $isUser = SupervisorGroupUser::findbySQL('supervisor_group_id = :id AND user_id = :user_id', 
                    array(':id'=> $portfoliogroup[0]->supervisor_group_id, ':user_id' => $user_id));
            
        } if ($isUser){
            $user_id = $portfoliogroup[0]->supervisor_group_id;
        }
        
        $hasAccess = EportfolioFreigabe::findBySQL('Seminar_id = :seminar_id AND block_id = :block_id AND user_id = :user_id',
                array(':seminar_id' => $seminar_id, ':block_id' => $chapter_id, ':user_id' => $user_id)); 
        $isOwner = Eportfoliomodel::isOwner($seminar_id, $user_id);
        
        if ($hasAccess || $isOwner){ 
            return true; 
        }
        else return false;
    }
    
    public static function setAccess($user_id, $seminar_id, $chapter_id, $status){
        if ($status && !self::hasAccess($user_id, $seminar_id, $chapter_id)){
            $access = new self();
            $access->mkdate  = time();
            $access->Seminar_id = $seminar_id;
            $access->block_id = $chapter_id;
            $access->user_id = $user_id;
            if($access->store()){
                Eportfoliomodel::sendNotificationToUser('freigabe', $seminar_id, $chapter_id, $user_id);
            }
        } else if (self::hasAccess($user_id, $seminar_id, $chapter_id)){
            self::deleteBySQL('Seminar_id = :seminar_id AND block_id = :block_id AND user_id = :user_id',
                array(':seminar_id' => $seminar_id, ':block_id' => $chapter_id, ':user_id' => $user_id));
        }
    }
    
    public static function getUserWithAccess($seminar_id, $chapter_id){
        return self::findBySQL('Seminar_id = :seminar_id AND block_id = :chapter_id', array(':seminar_id' => $seminar_id, ':chapter_id' => $chapter_id));
    }
    
    public static function hasAccessSince($user_id, $chapter_id){
        $hasAccessSince = EportfolioFreigabe::findOneBySQL('block_id = :block_id AND user_id = :user_id',
                array(':block_id' => $chapter_id, ':user_id' => $user_id)); 
        return $hasAccessSince->mkdate;
    }
}
