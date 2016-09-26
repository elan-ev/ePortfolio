<?php
require 'bootstrap.php';

/**
 * EportfolioPlugin.class.php
 * @author  Marcel Kipp
 * @version 0.1
 */

class EportfolioPlugin extends StudIPPlugin implements StandardPlugin, SystemPlugin {

    public function __construct() {
        parent::__construct();

        $navigation = new AutoNavigation(_('ePortfolio'));
        $navigation->setImage(Assets::image_path('admin'));
        $navigation->setURL(PluginEngine::GetURL($this, array(), 'show'));
        Navigation::addItem('/eportfolioplugin', $navigation);
        Navigation::activateItem("/eportfolioplugin");

    }

    public function initialize () {
      PageLayout::addStylesheet($this->getPluginURL().'/assets/style.css');
      PageLayout::addStylesheet('https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css');
      PageLayout::addScript($this->getPluginURL().'/assets/js/jasny-bootstrap.min.js');
    }

    public function getTabNavigation($course_id) {

    }

    public function getNotificationObjects($course_id, $since, $user_id) {
        return array();
    }

    public function getIconNavigation($course_id, $last_visit, $user_id) {
        // ...
    }

    public function getInfoTemplate($course_id) {
        // ...
    }

    public function perform($unconsumed_path)
    {
      $this->setupAutoload();
      $dispatcher = new Trails_Dispatcher(
          $this->getPluginPath(),
          rtrim(PluginEngine::getLink($this, array(), null), '/'),
          'show'
      );

      $dispatcher->plugin = $this;
      $dispatcher->dispatch($unconsumed_path);

      $nameSeminar = "ePortfolio";
      $tableName = ".portfolioOverview";
      $tableNamenotMine = ".viewportfolioOverview";
      $status = "124";
      $userid = $GLOBALS["user"]->id;
      $arrayPortfolio = array();
      $userStatus = "dozent";

      $db = DBManager::get();
      $getseminarid = $db->query("SELECT * FROM seminar_user WHERE user_id = '".$userid."' AND status = '".$userStatus."'")->fetchAll();
        foreach ($getseminarid as $seminar) {
          $Seminar_id = $seminar[Seminar_id];

          $result = $db->query("SELECT * FROM seminare WHERE status = '".$status."' AND Seminar_id = '".$Seminar_id."' ")->fetchAll();
          foreach ($result as $nutzer) {
            $arrayOne = array($nutzer[Name], $nutzer[Seminar_id], $nutzer[Beschreibung], $nutzer[Seminar_id]);

            $seminarid = $nutzer[Seminar_id];
            $link = 'href="/studip/dispatch.php/course/overview?cid='.$seminarid.'"';
            $icon = '<i class="fa fa-minus-circle" aria-hidden="true"></i>';
            $class = ' class="clickable-row"';

            echo "<script>jQuery('".$tableName."').append('<tr><td><a ".$link.">".$nutzer[Name]."</a></td><td> ".$nutzer[Beschreibung]." </td><td>".$icon."  Keine</td></tr>');</script>";

            $arrayPortfolio[] = $arrayOne;
          }

        }

      $notMine = $db->query("SELECT * FROM seminar_user WHERE user_id = '".$userid."' AND status != '".$userStatus."'")->fetchAll();
      foreach ($notMine as $seminar) {
        $Seminar_id = $seminar[Seminar_id];

        $result = $db->query("SELECT * FROM seminare WHERE status = '".$status."' AND Seminar_id = '".$Seminar_id."' ")->fetchAll();
        foreach ($result as $nutzer) {
          $arrayOne = array($nutzer[Name], $nutzer[Seminar_id], $nutzer[Beschreibung], $nutzer[Seminar_id]);

          $seminarid = $nutzer[Seminar_id];
          $link = 'href="/studip/dispatch.php/course/overview?cid='.$seminarid.'"';
          $icon = '<i class="fa fa-minus-circle" aria-hidden="true"></i>';

          echo "<script>jQuery('".$tableNamenotMine."').append('<tr><td><a ".$link."> ".$nutzer[Name]." </a></td><td> ".$nutzer[Beschreibung]." </td><td>".$icon."  Keine</td></tr>');</script>";

          $arrayPortfolio[] = $arrayOne;
        }

      }
    }

    private function setupAutoload()
    {
        if (class_exists('StudipAutoloader')) {
            StudipAutoloader::addAutoloadPath(__DIR__ . '/models');
        } else {
            spl_autoload_register(function ($class) {
                include_once __DIR__ . $class . '.php';
            });
        }
    }

    private function getSemClass()
    {
        global $SEM_CLASS, $SEM_TYPE, $SessSemName;
        return $SEM_CLASS[$SEM_TYPE[$SessSemName['art_num']]['class']];
    }

    private function isSlotModule()
    {
        if (!$this->getSemClass()) {
            return false;
        }

        return $this->getSemClass()->isSlotModule(get_class($this));
    }
}
