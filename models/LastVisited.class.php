<?php

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
}
