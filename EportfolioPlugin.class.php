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
