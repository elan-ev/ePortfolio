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

        function checkPermission(){
          $userId = $GLOBALS["user"]->id;
          $perm = get_global_perm($userId);

          // $havePerm = array("root", "dozent", "admin");
          $havePerm = array();
          if (in_array($perm, $havePerm)){
            $GLOBALS["permission"] = 1;
          }

        }

        $GLOBALS["permission"] = 0;
        $renderView = "show";
        checkPermission();

        if ($GLOBALS["permission"] == 1){
          $renderView = "dozentview";
        }

        $navigation = new AutoNavigation(_('ePortfolio'));
        $navigation->setImage(Assets::image_path('admin'));
        $navigation->setURL(PluginEngine::GetURL($this, array(), $renderView));
        Navigation::addItem('/eportfolioplugin', $navigation);
        Navigation::activateItem("/eportfolioplugin");

    }

    public function initialize () {
      PageLayout::addStylesheet($this->getPluginURL().'/assets/style.css');
      PageLayout::addStylesheet('https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css');
      // script row-link
      PageLayout::addScript($this->getPluginURL().'/assets/js/jasny-bootstrap.min.js');
    }

    public function getTabNavigation($course_id) {

      $cid = $course_id;
      $tabs = array();

      $navigation = new Navigation('eportfolioPlugin', PluginEngine::getURL($this, compact('cid'), 'eportfolioplugin', true));
      $navigation->setImage('icons/16/white/group4.png');
      $navigation->setActiveImage('icons/16/black/group4.png');

      $tabs['eportfolioplugin'] = $navigation;
      return $tabs;

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

}
