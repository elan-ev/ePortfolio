<?

/**
 * @author  <asudau@uos.de>
 *
 * @property string $object_id
 * @property string $user_id
 * @property int $time
 */
class LastVisited extends SimpleORMap
{
    
    public $errors = [];
    
    /**
     * Give primary key of record as param to fetch
     * corresponding record from db if available, if not preset primary key
     * with given value. Give null to create new record
     *
     * @param mixed $id primary key of table
     */
    public function __construct($id = null)
    {
        
        $this->db_table = 'eportfolio_last_visited';
        
        parent::__construct($id);
    }
    
    //TODO belongs to user ondelete -> delete
    
    public static function getLastVisited($object_id, $user_id)
    {
        $entry = self::findBySQL('object_id = :object_id AND user_id = :user_id');
        if ($entry) {
            return $entry->time;
        } else return false;
    }
    
    public static function setVisited($object_id, $user_id)
    {
        $entry = self::getLastVisited($object_id, $user_id);
        if ($entry) {
            $entry->time = time();
            $entry->store();
        } else {
            $entry            = new LastVisited();
            $entry->object_id = $object_id;
            $entry->user_id   = $user_id;
            $entry->time      = time();
            $entry->store();
        }
    }
    
    public static function chapter_last_visited($block_id, $user_id)
    {
        $last_visited = self::cwblock_last_visited($block_id, $user_id);
        
        //block get children
        $blocks = self::getAllChildBlocks($block_id);
        //foreach block id get userprogress 
        foreach ($blocks as $block) {
            //if any userprogress > last changed -> content changed = false
            if (self::cwblock_last_visited($block, $user_id) > $last_visited) {
                $last_visited = self::cwblock_last_visited($block, $user_id);
            }
        }
        return $last_visited;
    }
    
    public static function cwblock_last_visited($block_id, $user_id)
    {
        $db   = DBManager::get();
        $stmt = $db->prepare('SELECT * FROM mooc_userprogress WHERE block_id = :block_id AND user_id = :user_id');
        $stmt->execute([':block_id' => $block_id, ':user_id' => $user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return 0;
        } else {
            $time = new DateTime($row['chdate']);
            return $time->getTimestamp();
        }
    }
    
    public static function chapter_changed_since_last_visit($block_id, $user_id)
    {
        $content_changed = true;
        
        //block get children
        $blocks = self::getAllChildBlocks($block_id);
        //foreach block id get userprogress 
        foreach ($blocks as $block) {
            //if any userprogress > last changed -> content changed = false
            if (self::cwblock_last_changed($block) < self::cwblock_last_visited($block, $user_id)) {
                $content_changed = false;
            }
        }
        return $content_changed;
    }
    
    public static function cwblock_last_changed($block_id)
    {
        $db   = DBManager::get();
        $stmt = $db->prepare('SELECT * FROM mooc_blocks WHERE id = :block_id');
        $stmt->execute([':block_id' => $block_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return false;
        } else {
            return $row['chdate'];
        }
    }
    
    private static function getAllChildBlocks($chapter_id)
    {
        $db        = DBManager::get();
        $blocks    = [];
        $query     = "SELECT title, id FROM mooc_blocks WHERE parent_id = :id ORDER BY position ASC";
        $statement = $db->prepare($query);
        $statement->execute([':id' => $chapter_id]);
        foreach ($statement->fetchAll() as $subchapter) {
            array_push($blocks, $subchapter[id]);
            $query     = "SELECT title, id FROM mooc_blocks WHERE parent_id = :id ORDER BY position ASC";
            $statement = $db->prepare($query);
            $statement->execute([':id' => $subchapter[id]]);
            foreach ($statement->fetchAll() as $section) {
                array_push($blocks, $section[id]);
                $query     = "SELECT title, id FROM mooc_blocks WHERE parent_id = :id ORDER BY position ASC";
                $statement = $db->prepare($query);
                $statement->execute([':id' => $section[id]]);
                foreach ($statement->fetchAll() as $block) {
                    array_push($blocks, $block[id]);
                }
            }
        }
        return $blocks;
    }
}
