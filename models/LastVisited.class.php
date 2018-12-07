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
    protected static function configure($config = [])
    {
        $config['db_table'] = 'eportfolio_last_visited';
        parent::configure($config);
    }
    
    public static function getLastVisited($object_id, $user_id)
    {
        $entry = self::findBySQL('object_id = ? AND user_id = ?', [$object_id, $user_id]);
        if ($entry) {
            return $entry->time;
        } else {
            return false;
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
        return DBManager::get()->fetchColumn(
            'SELECT UNIX_TIMESTAMP(`chdate`) FROM `mooc_userprogress` WHERE `block_id` = ? AND `user_id` = ?',
            [$block_id, $user_id]);
    }
    
    public static function cwblock_last_changed($block_id)
    {
        return DBManager::get()->fetchColumn(
            'SELECT UNIX_TIMESTAMP(`chdate`) FROM `mooc_blocks` WHERE `block_id` = ?',
            [$block_id]);
    }
    
    private static function getAllChildBlocks($chapter_id)
    {
        $db        = DBManager::get();
        $blocks    = [];
        $query     = "SELECT `id` FROM `mooc_blocks` WHERE `parent_id` = :id ORDER BY `position` ASC";
        $statement = $db->prepare($query);
        $statement->execute([':id' => $chapter_id]);
        
        
        $query = "SELECT `id` FROM `mooc_blocks` WHERE `parent_id` = :id ORDER BY `position` ASC";
        $stm2  = $db->prepare($query);
        
        $query = "SELECT title, id FROM mooc_blocks WHERE parent_id = :id ORDER BY position ASC";
        $stm3  = $db->prepare($query);
        
        foreach ($statement->fetchAll() as $subchapter) {
            $blocks[] = $subchapter['id'];
            $stm2->execute([':id' => $subchapter['id']]);
            foreach ($statement->fetchAll() as $section) {
                $blocks[] = $section['id'];
                $stm3->execute([':id' => $section['id']]);
                foreach ($statement->fetchAll() as $block) {
                    $blocks[] = $block['id'];
                }
            }
        }
        
        return $blocks;
    }
}
