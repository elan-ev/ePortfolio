<?php
require 'bootstrap.php';
require 'classes/group.class.php';
require 'classes/eportfolio.class.php';
require 'classes/supervisorgroup.class.php';

/**
 * EportfolioPlugin.class.php
 * @author  Marcel Kipp
 * @version 0.18
 */

class EportfolioPlugin extends StudIPPlugin implements StandardPlugin, SystemPlugin {

    public function __construct() {
        parent::__construct();

        if($_POST["type"] == "freigeben"){
          $this->freigeben($_POST["selected"], $_POST["cid"]);
          exit;
        }

        if($_POST["action"] == "getsettingsColor"){
          $this->getsettingsColor($_GET['cid']);
          exit;
        }

        function checkPermission(){
          $userId = $GLOBALS["user"]->id;
          $perm = get_global_perm($userId);

          // $havePerm = array("root", "dozent", "admin");
          $havePerm = array();
          if (in_array($perm, $havePerm)){
            $GLOBALS["permission"] = 1;
          }

        }

        $eportfolio = new eportfolio($_GET['cid']);

        $GLOBALS["permission"] = 0;
        $renderView = "show";
        checkPermission();

        if ($GLOBALS["permission"] == 1){
          $renderView = "dozentview";
        }

        $navigation = new AutoNavigation(_('ePortfolio'));
        $navigation->setImage(Assets::image_path('lightblue/edit'));
        $navigation->setURL(PluginEngine::GetURL($this, array(), $renderView));
        Navigation::addItem('/eportfolioplugin', $navigation);
        //Navigation::activateItem("/eportfolioplugin");

        //set Menu Point for Supervisor
        $thisperm = get_global_perm($GLOBALS["user"]->id);
        if ($thisperm == "autor"){

        }

      $serverinfo = $_SERVER['PATH_INFO'];

      // if ($serverinfo == "/courseware/courseware" || $serverinfo == "/eportfolioplugin/eportfolioplugin" || $serverinfo == "/eportfolioplugin/settings"){
      //   include 'coursewareController/modifier.php';
      // }

      if ($serverinfo == "/courseware/courseware" || $serverinfo == "/eportfolioplugin/settings" || $serverinfo == "/eportfolioplugin/eportfolioplugin" || $serverinfo == "/course/management"){
        if($_GET["cid"]){
          $id = $_GET["cid"];

          if ($eportfolio->isEportfolio() == true) {
            include 'coursewareController/modifier.php';

            # modifier for the menubar
            if (!$id == NULL) {
              $seminar = new Seminar($id);
              $seminarMembers = $seminar->getMembers("dozent");
              foreach ($seminarMembers as $key => $value) {
                if ($userId != $key) {
                  include 'assets/modify/modifyMenu.php';
                }
              }
            }

          }
        }
      }

    }

    public function getCardInfos($cid){
      $db = DBManager::get();
      $return_arr = array();
      $getCardInfos = $db->query("SELECT id, title FROM mooc_blocks WHERE seminar_id = '$cid' AND type = 'Chapter' ORDER BY id ASC")->fetchAll();
      foreach ($getCardInfos as $value) {
        $arrayOne = array();
        $arrayOne['id'] = $value[id];
        $arrayOne['title'] = $value[title];

        // get sections of chapter
        $queryMenuPoints = $db->query("SELECT id, title FROM mooc_blocks WHERE parent_id = '$value[id]'")->fetchAll();
        $arrayOne['section'] = $queryMenuPoints;

        array_push($return_arr, $arrayOne);
      }

      return $return_arr;
    }

    public function initialize () {
      PageLayout::addStylesheet($this->getPluginURL().'/assets/style.css');
      PageLayout::addStylesheet('https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css');
      // script row-link
      PageLayout::addScript($this->getPluginURL().'/assets/js/jasny-bootstrap.min.js');
      PageLayout::addScript($this->getPluginURL().'/assets/js/mustache.min.js');
    }

    public function getTabNavigation($course_id) {

      $cid = $course_id;
      $tabs = array();

      //uebersicht navigation point
      $navigation = new Navigation('Übersicht', PluginEngine::getURL($this, compact('cid'), 'eportfolioplugin', true));
      $navigation->setImage('icons/16/white/group4.png');
      $navigation->setActiveImage('icons/16/black/group4.png');

      # settings navigation
      $id             = $_GET["cid"];

      if (!$id == NULL) {

        $seminar        = new Seminar($id);
        $seminarMembers = $seminar->getMembers("dozent");
        $isDozent       = false;

        foreach ($seminarMembers as $key => $value) {
          if ($GLOBALS["user"]->id == $key) {
            $isDozent = true;
          }
        }

        if ($isDozent == true) {
          $navigationSettings = new Navigation('Zugriffsrechte', PluginEngine::getURL($this, compact('cid'), 'settings', true));
          $navigationSettings->setImage('icons/16/white/admin.png');
          $navigationSettings->setActiveImage('icons/16/black/admin.png');
        }
      }


      //generate navigation
      $tabs['eportfolioplugin'] = $navigation;
      $tabs['settings'] = $navigationSettings;
      return $tabs;

    }

    public function getAccess($cid,$userId){
      $db = DBManager::get();
      $query = $db->query("SELECT eportfolio_access FROM eportfolio_user WHERE Seminar_id = '$cid' AND user_id = '$userId' ")->fetchAll();
      return $query[0][0];
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

    public function freigeben($selected, $cid){
      $db = DBManager::get();
      $query = $db->query("SELECT freigaben_kapitel FROM eportfolio WHERE Seminar_id = '$cid'")->fetchAll();
      # debug print_r($query[0][0]);
      if(empty($query[0][0])){
        $array = array($selected => '1');
        $array = json_encode($array);
        $db->query("UPDATE eportfolio SET freigaben_kapitel = '$array' WHERE Seminar_id = '$cid'");
        echo true;
      } else {
        $array = $query[0][0];
        $array = json_decode($array);
        if ($array->$selected == "1") {
          $array->$selected = "0";
          echo false;
        } else {
          $array->$selected = "1";
          echo true;
        }
        $array = json_encode($array);
        $db->query("UPDATE eportfolio SET freigaben_kapitel = '$array' WHERE Seminar_id = '$cid'");
      }
    }

    public function getsettingsColor($cid){
      $color = DBManager::get()->query("SELECT settings FROM eportfolio WHERE seminar_id = '$cid'")->fetchAll();
      $color = json_decode($color[0][0]);
      echo $color->color;
    }

}
