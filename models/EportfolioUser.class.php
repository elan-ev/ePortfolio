<?php

/**
 * @author  <asudau@uos.de>
 *
 * @property varchar $user_id
 * @property varchar $Seminar_id
 * @property varchar $eportfolio_id
 * @property string $status
 * @property int $owner
 */
class EportfolioUser extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'eportfolio_user';
        parent::configure($config);
    }

    public static function getPortfolioInformationInGroup($group_id, $portfolio_id, $current_user_id)
    {
        return DBManager::get()->fetchAll("SELECT mooc_blocks.title, freigaben.mkdate as shareDate,
                info.block_id as id, eportfolio_group_templates.abgabe_datum, info.template_id
            FROM mooc_blocks
            JOIN eportfolio_block_infos AS info ON info.block_id = mooc_blocks.id
            LEFT JOIN (
                SELECT block_id, mkdate
                FROM eportfolio_freigaben
                WHERE user_id IN (
                    SELECT supervisor_group_id
                    FROM supervisor_group_user
                     WHERE user_id = :current_user_id
                )
            )
            AS freigaben ON info.block_id = freigaben.block_id
            JOIN eportfolio_group_templates ON (
                info.template_id = eportfolio_group_templates.seminar_id
                OR info.template_id = 0
            )
            WHERE mooc_blocks.type = 'Chapter'
                AND mooc_blocks.parent_id != '0'
                AND info.seminar_id = :portfolio_id
                AND eportfolio_group_templates.group_id = :group_id
            ORDER BY info.block_id ASC",
            [':group_id' => $group_id, ':portfolio_id' => $portfolio_id, ':current_user_id' => $current_user_id]);
    }

    /**
     * Liefert den Status eines Nutzers innerhalb einer Vorlage
     * 2   = grau (kein Abgabetermin festgelegt)
     * 1   = grün
     * 0   = orange
     * -1  = rot
     **/
    public static function getStatusOfChapter($chapterInfo)
    {
        $deadline = $chapterInfo['abgabe_datum'];

        if ($deadline == 0) {
            return 2;
        }

        // status Icon changes to orange on deadline+2 days
        $timestampXTageVorher = strtotime('-4 day', $deadline);
        $now                  = time();

        if ($now < $timestampXTageVorher || $chapterInfo['shareDate'] != false) {
            return 1;
        } else {
            if ($now > $timestampXTageVorher && $now <= strtotime('+1 day', $deadline)) {
                return 0;
            } else {
                return -1;
            }
        }
    }

    /**
     * Liefert den Status des Users in einer Gruppe
     * Status wird erzeugt aus den verteilten templates
     * Kleinster Status wird zurückgegeben
     **/
    public static function getStatusOfUserInGroup($group_id, $portfolio_id, $current_user_id)
    {
        if(!$portfolio_id) {
            return -1;
        }

        $results   = [];
        $portfolioInfo = EportfolioUser::getPortfolioInformationInGroup($group_id, $portfolio_id, $current_user_id);

        foreach ($portfolioInfo as $chapterInfo) {
            $status = EportfolioUser::getStatusOfChapter($chapterInfo);

            if ($status < 2) {
                array_push($results, $status);
            }
        }
        return (!empty($results)) ? min($results) : '1';
    }

    /**
     * Gibt die Verhältnis freigeben/gesamt in Prozent wieder
     **/
    public static function getGesamtfortschrittInProzent($oben, $unten)
    {
        $progress  = $oben / $unten * 100;
        return round($progress, 1);
    }

    /**
     * Gibt die Anzahl der Notizen für den Supervisor eines users
     * innerhalb einer Gruppe wieder
     **/
    public static function getAnzahlNotizen($userPortfolioId)
    {
        return DBManager::get()->fetchColumn(
            "SELECT COUNT(`type`) FROM `mooc_blocks` WHERE `Seminar_id` IN(:seminar_id) AND `type` = 'PortfolioBlockSupervisor'",
            [':seminar_id' => $userPortfolioId]
        );
    }

    public static function getStatusOfUserInTemplate($deadline, $sharedChapterCnt, $chapterCnt)
    {
        if ($deadline == 0) {
            return 2;
        }

        $chapterInfo = array();
        $chapterInfo['abgabe_datum'] = $deadline;

        if($sharedChapterCnt == $chapterCnt) {
            $chapterInfo['shareDate'] = true;
        }

        return EportfolioUser::getStatusOfChapter($chapterInfo);
    }
}
