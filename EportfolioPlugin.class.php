<?php
require 'bootstrap.php';

/**
 * EportfolioPlugin.class.php
 * @author  Marcel Kipp
 * @version 0.1
 */

class EportfolioPlugin extends StudIPPlugin implements SystemPlugin {

    public function __construct() {
        parent::__construct();

        $navigation = new AutoNavigation(_('ePortfolio'));
        $navigation->setImage(Assets::image_path('admin'));
        $navigation->setURL(PluginEngine::GetURL($this, array(), 'show'));
        Navigation::addItem('/eportfolioplugin', $navigation);
        Navigation::activateItem("/eportfolioplugin");

    }

    public function initialize () {

      PageLayout::addScript($this->getPluginURL() . '/assets/js/addPortfolio.js');
      PageLayout::addStylesheet($this->getPluginURL().'/assets/style.css');
      PageLayout::addStylesheet('https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css');

    }

    public function perform($unconsumed_path)
    {
        $this->setupAutoload();
        $dispatcher = new Trails_Dispatcher(
            $this->getPluginPath(),
            rtrim(PluginEngine::getLink($this, array(), null), '/'),
            'show'
        );
        $dispatcher->plugin = $this;
        $dispatcher->dispatch($unconsumed_path);

        $nameSeminar = "ePortfolio";
        $tableName = ".portfolioOverview";
        $status = "124";
        $userid = $GLOBALS["user"]->id;
        $arrayPortfolio = array();

        $db = DBManager::get();
        $getseminarid = $db->query("SELECT * FROM seminar_user WHERE user_id = '".$userid."'")->fetchAll();
          foreach ($getseminarid as $seminar) {
            $Seminar_id = $seminar[Seminar_id];

            $result = $db->query("SELECT * FROM seminare WHERE status = '".$status."' AND Seminar_id = '".$Seminar_id."' ")->fetchAll();
            foreach ($result as $nutzer) {
              $arrayOne = array($nutzer[Name], $nutzer[Seminar_id], $nutzer[Beschreibung], $nutzer[Seminar_id]);

              $seminarid = $nutzer[Seminar_id];
              $link = 'href="/studip/dispatch.php/course/overview?cid='.$seminarid.'"';
              $icon = '<i class="fa fa-minus-circle" aria-hidden="true"></i>';

              echo "<script>jQuery('".$tableName."').append('<tr><td><a ".$link."> ".$nutzer[Name]." </a></td><td> ".$nutzer[Beschreibung]." </td><td>".$icon."  Keine</td></tr>');</script>";

              $arrayPortfolio[] = $arrayOne;
            }

          }


    }

    public function createPortfolio()
    {



    }

    private function setupAutoload()
    {
        if (class_exists('StudipAutoloader')) {
            StudipAutoloader::addAutoloadPath(__DIR__ . '/models');
        } else {
            spl_autoload_register(function ($class) {
                include_once __DIR__ . $class . '.php';
            });
        }
    }
}
