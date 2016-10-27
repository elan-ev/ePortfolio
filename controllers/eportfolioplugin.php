<?php

class EportfoliopluginController extends StudipController {

  public function __construct($dispatcher)
  {
      parent::__construct($dispatcher);
      $this->plugin = $dispatcher->plugin;



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
