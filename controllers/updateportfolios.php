<?php

class updateportfoliosController extends StudipController {

  public function __construct($dispatcher)
  {
      parent::__construct($dispatcher);
      $this->plugin = $dispatcher->plugin;

  }

  public function before_filter(&$action, &$args)
  {
      parent::before_filter($action, $args);

      // $this->set_layout($GLOBALS['template_factory']->open('layouts/base.php'));
      // PageLayout::setTitle('Create');
  }


  public function index_action()
  {

    function getMyPortfolios() {

      $nameSeminar = "ePortfolio";
      $tableName = ".portfolioOverview";
      $tableNamenotMine = ".viewportfolioOverview";
      $status = "124";
      $userid = $GLOBALS["user"]->id;
      $arrayPortfolio = array();
      $userStatus = "dozent";
      $countPortfolios = 0;

      $return_arr = array();

      $db = DBManager::get();
      $getseminarid = $db->query("SELECT * FROM seminar_user WHERE user_id = '".$userid."' AND status = '".$userStatus."'")->fetchAll();
        foreach ($getseminarid as $seminar) {
          $Seminar_id = $seminar[Seminar_id];

          $result = $db->query("SELECT * FROM seminare WHERE status = '".$status."' AND Seminar_id = '".$Seminar_id."' ")->fetchAll();
          foreach ($result as $nutzer) {

            $countPortfolios++;

            $arrayOne = array();
            $arrayOne['name'] = $nutzer[Name];
            $arrayOne['beschreibung'] = $nutzer[Beschreibung];
            $arrayOne['seminar_id'] = $nutzer[Seminar_id];

            array_push($return_arr, $arrayOne);

          }
        }

      $return_arr['counter'] = $countPortfolios;
      echo json_encode($return_arr);
    }

    getMyPortfolios();

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
