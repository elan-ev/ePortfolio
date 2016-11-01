<?php

class EportfoliopluginController extends StudipController {

  public function __construct($dispatcher)
  {
      parent::__construct($dispatcher);
      $this->plugin = $dispatcher->plugin;

      $sidebar = Sidebar::Get();
      Sidebar::Get()->setTitle('Uebersicht');
      $widget = new SearchWidget();
      Sidebar::Get()->addWidget($widget);

  }

  public function before_filter(&$action, &$args)
  {
      parent::before_filter($action, $args);

      $this->set_layout($GLOBALS['template_factory']->open('layouts/base.php'));
      PageLayout::setTitle('Create');

  }


  public function index_action()
  {

    Navigation::activateItem("course/eportfolioplugin");

    $cid = $_GET["cid"];
    $i = 0;

    $db = DBManager::get();
    $templateStatus = $db->query("SELECT templateStatus FROM eportfolio WHERE Seminar_id = '$cid' ")->fetchAll();
    $getCourseware = $db->query("SELECT id FROM mooc_blocks WHERE type = 'Courseware' AND seminar_id = '$cid'")->fetchAll();

    $t = $templateStatus[0][templateStatus];
    $getC = $getCourseware[0][id];

    if ($t == 0) {

      $template = array('Reflektionsimpuls 1', 'Reflektionsimpuls 2', 'Reflektionsimpuls 3', 'Reflektionsimpuls 4','Reflektionsimpuls 5', 'Reflektionsimpuls 6');

      foreach ($template as $value) {
        $db->query("INSERT INTO mooc_blocks (type, parent_id, seminar_id, title, position) VALUES ('Chapter', '$getC', '$cid', '$value', '$i')");
        $db->query("UPDATE eportfolio SET templateStatus = '1' WHERE seminar_id = '$cid'");
        $i++;
      }

    }

  }


  // customized #url_for for plugins
  function url_for($to)
  {
      $args = func_get_args();

      # find params
      $params = array();
      if (is_array(end($args))) {
          $params = array_pop($args);
      }

      # urlencode all but the first argument
      $args = array_map('urlencode', $args);
      $args[0] = $to;

      return PluginEngine::getURL($this->dispatcher->plugin, $params, join('/', $args));

  }
}
