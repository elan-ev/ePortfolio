<?php

class updateaccessController extends StudipController {

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

    function updateAccess() {
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

      $notMine = $db->query("SELECT * FROM seminar_user WHERE user_id = '".$userid."' AND status != '".$userStatus."'")->fetchAll();
      foreach ($notMine as $seminar) {
        $Seminar_id = $seminar[Seminar_id];

        $result = $db->query("SELECT * FROM seminare WHERE status = '".$status."' AND Seminar_id = '".$Seminar_id."' ")->fetchAll();
        foreach ($result as $nutzer) {
          $arrayOne = array($nutzer[Name], $nutzer[Seminar_id], $nutzer[Beschreibung], $nutzer[Seminar_id]);
          $seminarid = $nutzer[Seminar_id];
          $countPortfolios++;

          $getSeminarOwnerid = $db->query("SELECT user_id FROM seminar_user WHERE status = 'dozent' AND Seminar_id = '".$seminarid."'")->fetchAll();
          foreach ($getSeminarOwnerid as $owner) {
            $seminarOwnerid = $owner[user_id];

            $getSeminarOwner = $db->query("SELECT * FROM auth_user_md5 WHERE user_id = '".$seminarOwnerid."';")->fetchAll();
            foreach ( $getSeminarOwner as $seminarOwner) {
              $ownerName = $seminarOwner[Vorname]." ".$seminarOwner[Nachname];

              $arrayOne = array();
              $arrayOne['name'] = $nutzer[Name];
              $arrayOne['beschreibung'] = $nutzer[Beschreibung];
              $arrayOne['seminar_id'] = $nutzer[Seminar_id];
              $arrayOne['ownerName'] = $ownerName;

              array_push($return_arr, $arrayOne);
            }
          }
        }
      }
      $return_arr['counter'] = $countPortfolios;
      echo json_encode($return_arr);
    }

    updateAccess();

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
