<?php

require __DIR__.'/../bootstrap.php';
require __DIR__.'/../vendor/autoload.php';

class FixSeminarUserStatus extends Migration
{
    public function description () {
        return 'Fix permissions settings for all portfolios';
    }

    public function up ()
    {
        set_time_limit(0);

        $db = DBManager::get();

        $users_stmt = $db->prepare("UPDATE seminar_user
            SET status = 'autor'
            WHERE status = 'user' AND Seminar_id = ?
        ");

        $status = Config::get()->getValue('SEM_CLASS_PORTFOLIO');

        // get all portfolio seminars
        $stmt = $db->prepare("SELECT eportfolio.* FROM eportfolio
            JOIN seminare USING (Seminar_id)
            WHERE seminare.status = ?");

        $stmt->execute([$status]);

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users_stmt->execute([$data['Seminar_id']]);
        }
    }


    public function down ()
    {
    }
}
