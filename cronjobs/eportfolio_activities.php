<?php
/**
 * refresh_series.php
 */


require_once __DIR__.'/../bootstrap.php';

class EportfolioActivities extends CronJob
{

    public static function getName()
    {
        return _('eKEP - Mail an alle Lehrenden über Aktivitäten in deren Veranstaltungen');
    }

    public static function getDescription()
    {
        return _('eKEP - Es wird eine Mail generiert, die alle Veranstaltungen mit der Anzahl an neuen Aktivitäten und Link zur VA generiert.');
    }

    public function execute($last_result, $parameters = array())
    {
        $stmt = DBManager::get()->prepare("SELECT * FROM eportfolio_activities
            LEFT JOIN eportfolio USING(group_id)
            WHERE group_id IS NOT NULL
                AND mk_date > ?
                AND (
                    type = 'freigabe'
                    OR type = 'freigabe-entfernt'
                    OR type = 'supervisor-notiz'
                )
        ");

        // $stmt->execute([strtotime('-1 day')]);
        $stmt->execute([0]);

        $seminars = [];
        $users    = [];

        while($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $seminars[$data['group_id']]++;
        }

        $subject = sprintf(_('[eKEP - %s] Neue Aktivitäten in den letzten 24 Stunden'), date('m.d.Y'));

        foreach ($seminars as $seminar_id => $count) {
            $body = '';

            $course = Course::find($seminar_id);
            $body = $course->getFullname()
                . "\n" . sprintf(_('%s neue Aktivitäten'), $count)
                . ' - '. URLHelper::getURL('/plugins.php/eportfolioplugin/showsupervisor?cid=' . $seminar_id) .''
                . "\n";

            // get users for this seminar
            foreach ($course->getMembersWithStatus('dozent') as $member) {
                $users[$member->user_id][] = $body;
            }
        }

        $messaging = new messaging();
        $messaging->send_as_email = true;

        foreach ($users as $user_id => $body) {
            $messaging->insert_message(
                implode("\n", $body),
                get_username($user_id),
                '____%system%____',
                '',
                '',
                '',
                null,
                $subject,
                "",
                'normal',
                null
            );
        }
    }

}
