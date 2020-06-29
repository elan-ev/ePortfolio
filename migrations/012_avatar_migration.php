<?php
require __DIR__.'/../models/EportfolioModel.class.php';
require __DIR__.'/../vendor/autoload.php';

class AvatarMigration extends Migration
{
    public function description () {
        return 'add course avatar and startsemester for portfolio seminare';
    }


    public function up () {
        $seminare = EportfolioModel::findBySQL('true');
        foreach ($seminare as $sem){
            $course = Course::find($sem->Seminar_id);
            if($course){
                $status = $course->status;
                $avatar = CourseAvatar::getAvatar($sem->Seminar_id);
                if ($status == Config::get()->getValue('SEM_CLASS_PORTFOLIO_VORLAGE')){
                    $filename = sprintf('%s/public/plugins_packages/uos/EportfolioPlugin/%s',$GLOBALS['STUDIP_BASE_PATH'],'assets/images/avatare/vorlage.png');
                    $avatar->createFrom($filename);
                } else if ($status == Config::get()->getValue('SEM_CLASS_PORTFOLIO')){
                    $filename = sprintf('%s/public/plugins_packages/uos/EportfolioPlugin/%s',$GLOBALS['STUDIP_BASE_PATH'],'assets/images/avatare/eportfolio.png');
                    $avatar->createFrom($filename);
                } else if ($status == Config::get()->getValue('SEM_CLASS_PORTFOLIO_Supervisionsgruppe')){
                    $filename = sprintf('%s/public/plugins_packages/uos/EportfolioPlugin/%s',$GLOBALS['STUDIP_BASE_PATH'],'assets/images/avatare/supervisorgruppe.png');
                    $avatar->createFrom($filename);
                }
                $current_semester = Semester::findCurrent();
                $seminar = new Seminar($sem->Seminar_id);
                $seminar->setEndSemester(-1);
                $seminar->setStartSemester($current_semester->beginn);
            } 
        }
    }


    public function down () {
    }


}
