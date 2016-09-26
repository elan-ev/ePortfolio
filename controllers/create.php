  <?php

class CreateController extends StudipController {

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

        function generateRandomString($length = 32) {
          $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
          $charactersLength = strlen($characters);
          $randomString = '';
          for ($i = 0; $i < $length; $i++) {
              $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
          return $randomString;
        }

        $userid = $GLOBALS["user"]->id;
        $Seminar_id = generateRandomString();
        $Institut_id = "7a4f19a0a2c321ab2b8f7b798881af7c";
        $VeranstaltungsNummer = "25252525";
        $name = $_POST[name];
        $status = 124;
        $Beschreibung = $_POST[text];
        $Lesezugriff = 1;
        $Schreibzugriff = 1;
        $start_time = 1459461600;
        $duration_time = 0;

        $statususer = "dozent";
        $bind_calendar = 1;
        $visibleuser = "yes";
        $position = 0;
        $gruppe = 5;
        $notification = 0;
        $bind_calendar = 1;

        $db = DBManager::get();
        $result = $db->query("INSERT INTO seminare (Seminar_id, VeranstaltungsNummer, Institut_id, Name, status, Beschreibung, Lesezugriff, Schreibzugriff, start_time, duration_time, mkdate, chdate) VALUES ('$Seminar_id', '$VeranstaltungsNummer', '$Institut_id', '$name', '$status', '$Beschreibung', '$Lesezugriff', '$Schreibzugriff', '$start_time', '$duration_time', 'UNIX_TIMESTAMP()', 'UNIX_TIMESTAMP()'); ");
        $resultuser = $db->query("INSERT INTO seminar_user (Seminar_id, user_id, status, position, gruppe, notification, visible, bind_calendar, mkdate) VALUES ('$Seminar_id', '$userid', '$statususer', '$position', '$gruppe', '$notification', '$visibleuser', '$bind_calendar', 'UNIX_TIMESTAMP()');");

        // $deleteCoursewareStandard = $db->query("DELETE FROM mooc_blocks WHERE type != 'Courseware' AND seminar_id = '".$Seminar_id."';");
        $createCoursewareTemplate = $db->query("SELECT * FROM mooc_blocks WHERE type = 'Courseware' AND seminar_id = '".$Seminar_id."'; ")->fetchAll();
        foreach ($createCoursewareTemplate as $block) {
          $block_id = $block[id];

        }


        //Öffnet Show-Page
        echo "<meta http-equiv='refresh' content='0; URL=/studip/plugins.php/eportfolioplugin/show?seminarName=".$name."'>";

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
