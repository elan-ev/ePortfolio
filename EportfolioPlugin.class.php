<?php
require 'bootstrap.php';
require 'classes/group.class.php';
require 'classes/eportfolio.class.php';


/**
 * EportfolioPlugin.class.php
 * @author  Marcel Kipp
 * @version 0.18
 */

class EportfolioPlugin extends StudIPPlugin implements StandardPlugin, SystemPlugin {

    public function __construct() {
        parent::__construct();

        $navigation = new AutoNavigation(_('ePortfolio'));
        $navigation->setImage(Assets::image_path('lightblue/edit'));
        $navigation->setURL(PluginEngine::GetURL($this, array(), "show"));
        Navigation::addItem('/eportfolioplugin', $navigation);
        //Navigation::activateItem("/eportfolioplugin");
        NotificationCenter::addObserver($this, "setup_navigation", "PageWillRender");

    }

    public function getCardInfos($cid){
      $db = DBManager::get();
      $return_arr = array();
      $query = "SELECT id, title FROM mooc_blocks WHERE seminar_id = :id AND type = 'Chapter' ORDER BY position ASC";
      $statement = $db->prepare($query);
      $statement->execute(array(':id'=> $cid));
      $getCardInfos = $statement->fetchAll();
      foreach ($getCardInfos as $value) {
        $arrayOne = array();
        $arrayOne['id'] = $value[id];
        $arrayOne['title'] = $value[title];

        // get sections of chapter
        $query = "SELECT id, title FROM mooc_blocks WHERE parent_id = :id";
        $statement = $db->prepare($query);
        $statement->execute(array(':id'=> $value[id]));
        $arrayOne['section'] = $statement->fetchAll();

        array_push($return_arr, $arrayOne);
      }

      return $return_arr;
    }

    public function initialize () {
      //PageLayout::addStylesheet($this->getPluginURL().'/assets/bootstrap.css');
      PageLayout::addStylesheet($this->getPluginURL().'/assets/style.css');
      
    }

    public function getTabNavigation($course_id) {

      $cid = $course_id;
      $tabs = array();

      if ($this->isSupervisionsgruppe()) {
          $navigation = new Navigation('Übersicht', PluginEngine::getURL($this, compact('cid'), 'showsupervisor', true));
          $navigation->setImage('icons/16/white/group4.png');
          $navigation->setActiveImage('icons/16/black/group4.png');
      } else {
          //uebersicht navigation point
          $navigation = new Navigation('Übersicht', PluginEngine::getURL($this, compact('cid'), 'eportfolioplugin', true));
          $navigation->setImage('icons/16/white/group4.png');
          $navigation->setActiveImage('icons/16/black/group4.png');
       }

      # settings navigation
      $id = $_GET["cid"];

      if (Course::findById($id)) {

        $seminar        = new Seminar($id);
        $seminarMembers = $seminar->getMembers("dozent");
        $isDozent       = false;

        foreach ($seminarMembers as $key => $value) {
          if ($GLOBALS["user"]->id == $key) {
            $isDozent = true;
          }
        }

        if ($isDozent == true && $this->isPortfolio()) {
          $navigationSettings = new Navigation('Zugriffsrechte', PluginEngine::getURL($this, compact('cid'), 'settings', true));
          $navigationSettings->setImage('icons/16/white/admin.png');
          $navigationSettings->setActiveImage('icons/16/black/admin.png');
          $tabs['settings'] = $navigationSettings;
        } else if ($isDozent == true && $this->isVorlage()) {
          $navigationSettings = new Navigation('Einstellungen', PluginEngine::getURL($this, compact('cid'), 'blocksettings', true));
          $navigationSettings->setImage('icons/16/white/admin.png');
          $navigationSettings->setActiveImage('icons/16/black/admin.png');  
          $tabs['settings'] = $navigationSettings;
        }
      } 

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
      
       global $perm;
        
        if($_POST["type"] == "freigeben"){
          $this->freigeben($_POST["selected"], $_POST["cid"]);
          exit;
        }

        if($_POST["action"] == "getsettingsColor"){
          $this->getsettingsColor($_GET['cid']);
          exit;
        }
        $eportfolio = new eportfolio($_GET['cid']);

      $serverinfo = $_SERVER['PATH_INFO'];

      parent::perform($unconsumed_path);

    }

    private function setupAutoload()
    {
        StudipAutoloader::addAutoloadPath(__DIR__ . '/models');
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

    public function getsettingsColor($cid){
      $query = "SELECT settings FROM eportfolio WHERE seminar_id = :cid";
      $statement = DBManager::get()->prepare($query);
      $statement->execute(array(':cid'=> $cid));
      $color = json_decode($statement->fetchAll()[0][0]);
      echo $color->color;
    }

    //aktuelle cid/seminarid
    static function getSeminarId()
    {
        if (!Request::option('cid')) {
            if ($GLOBALS['SessionSeminar']) {
                URLHelper::bindLinkParam('cid', $GLOBALS['SessionSeminar']);
                return $GLOBALS['SessionSeminar'];
            }
            return false;
        }
        return Request::option('cid');
    }

    private function isPortfolio()
    {
        if(Course::findById($this->getSeminarId())){
            $seminar = Seminar::getInstance($this->getSeminarId());
            $status = $seminar->getStatus();
            if ($status == Config::get()->getValue('SEM_CLASS_PORTFOLIO')){
                return true;
            }
            else return false;
        } else return false;
    }
    
    private function isVorlage()
    {
        if(Course::findById($this->getSeminarId())){
            $seminar = Seminar::getInstance($this->getSeminarId());
            $status = $seminar->getStatus();
            if ($status == Config::get()->getValue('SEM_CLASS_PORTFOLIO_VORLAGE')){
                return true;
            }
            else return false;
        }  
        else return false;
    }
    
     private function isSupervisionsgruppe()
    {
        if(Course::findById($this->getSeminarId())){
            $seminar = Seminar::getInstance($this->getSeminarId());
            $status = $seminar->getStatus();
            if ($status == Config::get()->getValue('SEM_CLASS_PORTFOLIO_Supervisionsgruppe')){
                return true;
            }
            else return false;
        }  
        else return false;
    }
    
    public function setup_navigation() {
        //var_dump(Navigation::getItem('/course/mooc_courseware'));
        if (($this->isPortfolio() || $this->isVorlage() ) && Navigation::hasItem('/course/mooc_courseware')){
            //$settings = Navigation::getItem('/course/settings');
            //Navigation::removeItem('/course/settings');
            //Navigation::insertItem('/course/settings', $settings, '/course/files');
            Navigation::getItem('/course/mooc_courseware')->setTitle(ePortfolio);
        
            //default Courseware-Hilfe ersetzen
            $widgets = Helpbar::get()->getWidgets();
            foreach($widgets as $index=>$widget){
                $elements = $widget->getElements();
                Helpbar::get()->removeWidget($index);
            }
            
            if ($this->isPortfolio()){
                $description  = _('Unter **Zugriffsrechte** können Sie einzelne Kapitel für Komilitonen oder Ihre Supervisoren freigeben.') . ' ';
                $description .= _('') . '';
                $tip = _('Unter **ePortfolio** können Sie Ihr Portfolio bearbeiten. ');
                $tip .= _('');
                $bearbeiten = _('Um Inhalte oder Kapitel hinzuzufügen, klicken Sie im Reiter **ePortfolio** oben rechts auf den Doktorandenhut');
                Helpbar::get()->addPlainText(_(''), $description, '');
                Helpbar::get()->addPlainText(_(''), $tip, '');
                Helpbar::get()->addPlainText(_('Tip zum Bearbeiten'), $bearbeiten, 'icons/white/doctoral_cap.svg');
            }
            if ($this->isVorlage()){
                $description  = _('Unter **Zugriffsrechte** können Sie festlegen, wer Zugriff auf diese Vorlage hat. ') . ' ';
                $description .= _('Ausserdem können Sie Inhalte der Vorlage für die spätere Bearbeitung durch Studierende sperren.') . '';
                $tip = _('Unter **ePortfolio** können Sie die Vorlage bearbeiten. ');
                $tip .= _('');
                $bearbeiten = _('Um Inhalte oder Kapitel hinzuzufügen, klicken Sie im Reiter **ePortfolio** oben rechts auf den Doktorandenhut');
                Helpbar::get()->addPlainText(_(''), $description, '');
                Helpbar::get()->addPlainText(_(''), $tip, '');
                Helpbar::get()->addPlainText(_('Tip zum Bearbeiten'), $bearbeiten, 'icons/white/doctoral_cap.svg');
            }
            
         }
    }
}
