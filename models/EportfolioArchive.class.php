<?php

/**
 * @author  <tgloeggl@uos.de>
 *
 * @property varchar $eportfolio_id
 */
class EportfolioArchive extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'eportfolio_archive';

        parent::configure($config);
    }
}
