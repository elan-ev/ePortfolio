<?php
require 'bootstrap.php';
require 'models/Eportfoliomodel.class.php';


/**
 * EportfolioPlugin.class.php
 * @author  Marcel Kipp
 * @version 0.18
 */

class EportfolioPlugin extends StudIPPlugin implements StandardPlugin, SystemPlugin {

    public function __construct() {
        parent::__construct();

        $navigation = new Navigation(_('ePortfolio'));
        //$navigation->setImage(Icon::create('edit', 'clickable'));
        $navigation->setURL(PluginEngine::GetURL($this, array(), "show"));
        Navigation::addItem('/profile/eportfolioplugin', $navigation);
        //Navigation::activateItem("/eportfolioplugin");
        NotificationCenter::addObserver($this, "setup_navigation", "PageWillRender");
        NotificationCenter::addObserver($this, "store_activity","UserDidPostSupervisorNotiz");
        NotificationCenter::addObserver($this, "store_activity","SupervisorDidPostAnswer");
        NotificationCenter::addObserver($this, "store_activity","UserDidPostNotiz");

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
      PageLayout::addStylesheet($this->getPluginURL().'/assets/flexboxgrid.min.css');

    }

    public function getTabNavigation($course_id) {

      $tabs = array();
      global $perm, $user;
      $isDozent = $perm->have_studip_perm('dozent', $course_id);

      if ($isDozent && !$this->isPortfolio() && !$this->isVorlage()) {
          $navigation = new Navigation('Supervision', PluginEngine::getURL($this, compact('cid'), 'showsupervisor', true));
          $navigation->setImage(Icon::create('group4', 'info_alt'));
          $navigation->setActiveImage(Icon::create('group4', 'info'));
          
          $item = new Navigation(_('Supervisionsansicht'), PluginEngine::getURL($this, compact('cid'), 'showsupervisor', true));
          $navigation->addSubNavigation('supervision', $item);
          
          $item = new Navigation(_('Activity Feed'), PluginEngine::getURL($this, compact('cid'), 'showsupervisor/activityfeed', true));
          $navigation->addSubNavigation('portfoliofeed', $item);
          
      } else if ($this->isPortfolio() || $this->isVorlage() ){
          //uebersicht navigation point
          $navigation = new Navigation('Übersicht', PluginEngine::getURL($this, compact('cid'), 'eportfolioplugin', true));
          $navigation->setImage(Icon::create('group4', 'info_alt'));
          $navigation->setActiveImage(Icon::create('group4', 'info'));
       }


        $owner = Eportfoliomodel::isOwner($course_id, $user->id);
        if ($this->isPortfolio() && $owner) {
          $navigationSettings = new Navigation('Zugriffsrechte', PluginEngine::getURL($this, compact('cid'), 'settings', true));
          $navigationSettings->setImage(Icon::create('admin', 'info_alt'));
          $navigationSettings->setActiveImage(Icon::create('admin', 'info'));
          $tabs['settings'] = $navigationSettings;
        } else if ($isDozent == true && $this->isVorlage()) {
          $navigationSettings = new Navigation('Einstellungen', PluginEngine::getURL($this, compact('cid'), 'blocksettings', true));
          $navigationSettings->setImage(Icon::create('admin', 'info_alt'));
          $navigationSettings->setActiveImage(Icon::create('admin', 'info'));  
          $tabs['blocksettings'] = $navigationSettings;
        }
      

      $tabs['eportfolioplugin'] = $navigation;
      return $tabs;

    }

    public function getNotificationObjects($course_id, $since, $user_id) {
        return array();
    }

    public function getIconNavigation($course_id, $last_visit, $user_id) {
        $icon = new AutoNavigation(
            'Supervision',
            PluginEngine::getURL($this, array('cid' => $course_id, 'iconnav' => 'true'), 'eportfolioplugin/portfoliofeed', true)
        );
        
        $group = EportfolioGroup::find($course_id); 
        if($group){
            $new_ones = sizeof($group->getActivities());
       
            if ($new_ones) {
                $title = $new_ones > 1 ? sprintf(_('%s neue Ereignisse in Studierenden-Portfolios'), $new_ones) : _('1 neues Ereignisse in Studierenden-Portfolio');
                $icon->setImage(Icon::create('group3', 'attention', ['title' => $title]));
                $icon->setBadgeNumber($new_ones);
            } else {
                $icon->setImage(Icon::create('group3', 'inactive', ['title' => 'Supervision']));
            }
        }

        return $icon;
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
        $course = Course::findCurrent();
        if($course){
            $status = $course->status;
            if ($status == Config::get()->getValue('SEM_CLASS_PORTFOLIO')){
                return true;
            }
        } return false;
    }

    private function isVorlage()
    {
        $course = Course::findCurrent();
        if($course){
            $status = $course->status;
            if ($status == Config::get()->getValue('SEM_CLASS_PORTFOLIO_VORLAGE')){
                return true;
            }
        } return false;
    }

     private function isSupervisionsgruppe()
    {
        $course = Course::findCurrent();
        if($course){
            $status = $course->status;
            if ($status == Config::get()->getValue('SEM_CLASS_PORTFOLIO_Supervisionsgruppe')){
                return true;
            }
        } return false;
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
                Helpbar::get()->addPlainText(_('Tip zum Bearbeiten'), $bearbeiten, Icon::create('doctoral-cap', 'info_alt'));
            }
            if ($this->isVorlage()){
                $description  = _('Unter **Teilnehmende** können Sie festlegen, wer Zugriff auf diese Vorlage hat. ') . ' ';
                $description .= _('Ausserdem können Sie unter **Einstellungen** Inhalte der Vorlage für die spätere Bearbeitung durch Studierende sperren.') . '';
                $tip = _('Unter **ePortfolio** können Sie die Vorlage bearbeiten. ');
                $tip .= _('');
                $bearbeiten = _('Um Inhalte oder Kapitel hinzuzufügen, klicken Sie im Reiter **ePortfolio** oben rechts auf den Doktorandenhut');
                Helpbar::get()->addPlainText(_(''), $description, '');
                Helpbar::get()->addPlainText(_(''), $tip, '');
                Helpbar::get()->addPlainText(_('Tip zum Bearbeiten'), $bearbeiten, Icon::create('doctoral-cap', 'info_alt'));
            }
         }
    }
    
    public function store_activity($notification, $block_id, $course_id){
        EportfolioActivity::addActivity($course_id, $block_id, $notification);
    }
}
