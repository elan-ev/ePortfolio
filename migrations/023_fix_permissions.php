<?php

require __DIR__.'/../vendor/autoload.php';

class FixPermissions extends Migration
{
    public function description () {
        return 'Fix permissions settings for all portfolios';
    }

    /*

    BEGIN
    INSERT INTO mooc_blocks_changes
    (
      id,
      old_approval,
      new_approval
    )
    VALUES
    (
      OLD.id,
      OLD.approval,
      NEW.approval
    );
  END

     */

    public function up ()
    {
        $db = DBManager::get();

        $users_stmt = $db->prepare("SELECT * FROM seminar_user
            WHERE Seminar_id = ?");

        $status = Config::get()->getValue('SEM_CLASS_PORTFOLIO');

        // get all portfolio seminars
        $stmt = $db->prepare("SELECT eportfolio.* FROM eportfolio
            JOIN seminare USING (Seminar_id)
            WHERE seminare.status = ?");

        $stmt->execute([$status]);

        $count = 0;

        $stmt_count = $db->prepare("SELECT COUNT(*) FROM eportfolio
            JOIN seminare USING (Seminar_id)
            WHERE seminare.status = ?");

        $stmt_count->execute([$status]);
        $max = $stmt_count->fetchColumn();

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $count++;

            if ($count % 50 == 0 || $count == 1 || $count == $max) {
                echo "[$count / $max] Setze Rechte neu...\n";
            }

            $users_stmt->execute([$data['Seminar_id']]);
            $users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

            // check permissions for group and reset them
            if ($data['group_id']) {
                $templates  = EportfolioGroupTemplates::getUserChapterInfos(
                    $data['group_id'], $data['Seminar_id']
                );

                foreach ($templates as $chapters) {
                    foreach ($chapters as $chapter) {
                        if (EportfolioFreigabe::getAccess($data['group_id'], $chapter['id'])) {
                            EportfolioFreigabe::setAccess($data['group_id'], $chapter['id'], true);
                        }

                        foreach ($users as $user) {
                            if (EportfolioFreigabe::getAccess($user['user_id'], $chapter['id'])) {
                                EportfolioFreigabe::setAccess($data['user_id'], $chapter['id'], true);
                            }
                        }
                    }
                }
            }
        }

        die;
    }


    public function down ()
    {
    }
}
