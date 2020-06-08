<?php

/**
 * @author  <mkipp@uos.de>
 *
 * @property varchar $group_id
 * @property varchar $Seminar_id
 * @property int $mkdate
 * @property int $abgabe_datum
 * @property varchar $verteilt_durch
 */
class EportfolioGroupTemplates extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'eportfolio_group_templates';
        parent::configure($config);
    }

    /**
     * Setzt Abgabedatum für eine verteiltes Template als timestamp
     **/
    public static function setDeadline($group_id, $template_id, $date)
    {
        $query     = "UPDATE eportfolio_group_templates SET abgabe_datum = :datum WHERE group_id = :group_id AND Seminar_id = :template_id";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([':datum' => $date, ':group_id' => $group_id, ':template_id' => $template_id]);
    }

    /**
     * Liefert Abgabedatum für eine verteiltes Template als timestamp
     **/
    public static function getDeadline($group_id, $template_id)
    {
        return DBManager::get()->fetchColumn('SELECT `abgabe_datum` FROM `eportfolio_group_templates` WHERE `Seminar_id` = ? AND `group_id` = ?', [$template_id, $group_id]);
    }

    /**EportfolioGroupTemplates::getGroupTemplates
     * Liefert alle verteilten Templates einer Gruppe
     **/
    public static function getGroupTemplates($groupId)
    {
        $query = "SELECT DISTINCT seminare.*
            FROM seminare
            JOIN eportfolio_group_templates USING (Seminar_id)
            WHERE seminare.status = ?
                AND eportfolio_group_templates.group_id = ?
            ORDER BY `mkdate` DESC";
        return DBManager::get()->fetchAll($query, [Config::get()->SEM_CLASS_PORTFOLIO_VORLAGE, $groupId], 'Course::buildExisting');
    }


    public static function getWannWurdeVerteilt($group_id, $template_id)
    {
        return DBManager::get()->fetchColumn('SELECT `mkdate` FROM `eportfolio_group_templates` WHERE `Seminar_id` = ? AND `group_id` = ?', [$template_id, $group_id]);
    }

    /**
     * überprüft, ob der angegebene User alle in der Veranstaltung verteilten Templates erhalten hat
     */
    public static function checkMissingTemplate($groupId, $userPortfolioId, $groupChapters)
    {
        if(!$userPortfolioId) {
            return true;
        }

        //Alle Chapter, die User hat
        $userChapters = DBManager::get()->fetchAll("SELECT COUNT(eportfolio_block_infos.block_id) FROM eportfolio_group_templates
            JOIN mooc_blocks USING(Seminar_id)
            JOIN eportfolio_block_infos ON mooc_blocks.id = eportfolio_block_infos.vorlagen_block_id
            WHERE mooc_blocks.type = 'Chapter' AND mooc_blocks.parent_id != '0'
            AND eportfolio_group_templates.group_id = :groupId
            AND eportfolio_block_infos.Seminar_id = :seminarId",
            [":groupId" => $groupId, ":seminarId" => $userPortfolioId]);

        if($groupChapters > $userChapters) {
            return true;
        }
        return false;
    }

    public static function getGroupTemplateInformation($groupId, $portfolios)
    {
        $portfolioIds = array_map(function($portfolio) {
            return $portfolio->id;
        }, $portfolios);

        $data = DBManager::get()->fetchAll('SELECT verteilt_durch, mkdate, abgabe_datum, Seminar_id
            FROM `eportfolio_group_templates`
            WHERE `Seminar_id` IN (?) AND `group_id` = ?',
            [$portfolioIds, $groupId]
        );

        $portfoliosData = [];
        foreach ($data as $portfolioData) {
            $portfolio['distributionDate'] = $portfolioData['mkdate'];
            $portfolio['deadline']         = $portfolioData['abgabe_datum'];
            $portfolio['seminarId']        = $portfolioData['Seminar_id'];
            $portfolio['portfolio']        = $portfolios[array_search($portfolio['seminarId'], array_column($portfolios, 'id'))];

            if (!$portfolioData['verteilt_durch']) {
                $portfolio['creatorName'] = "Unknown";
            } else {
                $portfolio['creatorName'] = User::find($portfolioData['verteilt_durch'])->getFullName();
            }
            array_push($portfoliosData, $portfolio);
        }

        return $portfoliosData;
    }

    public function getUserChapterInfos($group_id, $cid)
    {
        $vars = DBManager::get()->fetchAll(
            "SELECT eportfolio_group_templates.Seminar_id, mooc_blocks.title, eportfolio_block_infos.block_id as id
            FROM eportfolio_group_templates
            JOIN mooc_blocks ON mooc_blocks.seminar_id = eportfolio_group_templates.Seminar_id
            JOIN eportfolio_block_infos ON eportfolio_block_infos.vorlagen_block_id = mooc_blocks.id
            WHERE eportfolio_group_templates.group_id = :group_id
            AND mooc_blocks.parent_id != 0 AND mooc_blocks.type = 'Chapter'
            AND eportfolio_block_infos.Seminar_id = :cid",
            [":group_id" => $group_id, ":cid" => $cid]
        );

        $templates = array();

        foreach ($vars as $var) {
            if(!$templates[$var['Seminar_id']]) {
                $templates[$var['Seminar_id']] = [];
            }
            array_push($templates[$var['Seminar_id']], $var);
        }

        return $templates;
    }
}
