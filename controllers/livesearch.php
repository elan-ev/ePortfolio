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
      $cid = $_POST["cid"];
      $db = DBManager::get();

      //set ajax vars
      $user_status = $_POST["status"];
      $val = $_POST["val"];

      //query
      if ($_POST["searchViewer"]){
        $search_query = $db->query("SELECT Vorname, Nachname, user_id FROM auth_user_md5 WHERE Vorname LIKE '%$val%' OR Nachname LIKE '%$val%'")->fetchAll();
      } elseif ($_POST["searchSupervisor"]) {
        $search_query = $db->query("SELECT Vorname, Nachname, user_id FROM auth_user_md5 WHERE Vorname LIKE '%$val%' OR Nachname LIKE '%$val%' AND perms = '$user_status'")->fetchAll();
      }

      foreach ($search_query as $key) {

        $user_id_viewer = $key[user_id];
        $checkUser = $db->query("SELECT * FROM seminar_user WHERE Seminar_id = '$cid' AND user_id = '$user_id_viewer'")->fetchAll();

        if (empty($checkUser)) {

          $arrayOne = array();
          $arrayOne["Vorname"] =  $key[Vorname];
          $arrayOne["Nachname"] = $key[Nachname];
          $arrayOne["userid"] = $key[user_id];

          array_push($return_arr, $arrayOne);

        }
      }

      echo json_encode($return_arr);
    }

}
