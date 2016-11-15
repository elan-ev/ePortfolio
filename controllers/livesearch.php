  <?php

class livesearchController extends StudipController {

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;

    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

    }


    public function index_action()
    {
      //set retun array
      $return_arr = array();

      // set vars
      $userid = $GLOBALS["user"]->id;
      $cid = $_GET["cid"];
      $db = DBManager::get();

      //set ajax vars
      $user_status = $_POST["status"];
      $val = $_POST["val"];

      //query
      $search_query = $db->query("SELECT Vorname, Nachname, user_id FROM auth_user_md5 WHERE Vorname LIKE '%$val%' OR Nachname LIKE '%$val%' AND perms = '$user_status'")->fetchAll();
      foreach ($search_query as $key) {
        $arrayOne = array();
        $arrayOne["Vorname"] =  $key[Vorname];
        $arrayOne["Nachname"] = $key[Nachname];
        $arrayOne["userid"] = $key[user_id];

        array_push($return_arr, $arrayOne);
      }

      echo json_encode($return_arr);

    }

}
