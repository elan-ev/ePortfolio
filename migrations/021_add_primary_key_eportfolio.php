<?php

class AddPrimaryKeyEportfolio extends Migration
{
    public function description () {
        return 'Change INDEX Seminar_id to PRIMARY KEY';
    }


    public function up ()
    {
        $db = DBManager::get();

        try {
            $db->exec('ALTER TABLE `eportfolio`
                ADD PRIMARY KEY `Seminar_id` (`Seminar_id`),
                DROP INDEX `Seminar_id`');
        } catch (PDOException $e) {
            echo $e->getMessage() ."\n";
        }
    }


    public function down ()
    {
    }
}
