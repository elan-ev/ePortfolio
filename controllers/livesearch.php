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

      // empty input
      if ($val == "") {
        $val = array();
        exit(json_encode($val));
      }

      //query
      if ($_POST["searchViewer"]){
        $query = "SELECT Vorname, Nachname, user_id FROM auth_user_md5 WHERE Vorname LIKE '%:vorname%' OR Nachname LIKE '%:nachname%'";
        $statement = $db->prepare($query);
        $statement->execute(array(':vorname'=> $val[0], ':nachname'=> $val[0]));
        $search_query = $statement->fetchAll();
      } elseif ($_POST["searchSupervisor"]) {
        $query = "SELECT Vorname, Nachname, user_id FROM auth_user_md5 WHERE Vorname LIKE '%:string_1%' OR Nachname LIKE '%:string_1%' OR Vorname LIKE '%:string_2%' OR Nachname LIKE '%string_2%' AND perms = :user_status";
        $statement = $db->prepare($query);
        $statement->execute(array(':string_1'=> $val, ':string_2'=> $val[1], ':user_status' => $user_status));
        $search_query = $statement->fetchAll();
      }

      foreach ($search_query as $key) {

        $user_id_viewer = $key[user_id];
        $query = "SELECT * FROM seminar_user WHERE Seminar_id = :cid AND user_id = :user_id_viewer";
        $statement = $db->prepare($query);
        $statement->execute(array(':cid'=> $cid, ':user_id_viewer'=> $user_id_viewer));

        if (empty($statement->fetchAll())) {

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
