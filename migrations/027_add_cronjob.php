<?php

class AddCronjob extends Migration
{
    const FILENAMES = [
        'public/plugins_packages/virtUOS/EportfolioPlugin/cronjobs/eportfolio_activities.php'
    ];

    public function description()
    {
        return 'adds a cronjob for reuploading filed media uploads and fixes all cronjob registrations';
    }

    public function up()
    {
        foreach (self::FILENAMES as $filename) {

            if (!$task_id = CronjobTask::findByFilename($filename)[0]->task_id) {
                $task_id = CronjobScheduler::registerTask($filename, true);
            }

            // Schedule job to run at 6 o'clock in the morning
            if ($task_id) {
                CronjobScheduler::cancelByTask($task_id);
                CronjobScheduler::schedulePeriodic($task_id, 0, 6);
                CronjobSchedule::findByTask_id($task_id)[0]->activate();
            }
        }
    }

    function down() {}
}
