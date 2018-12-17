<?

/**
 * Class dozentviewController
 * @deprecated
 *
 * Ich glaube, der Code wird nirgends genutzt
 */
class dozentviewController extends StudipController
{
    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->current_plugin;
        
        $sidebar = Sidebar::Get();
        $sidebar->setTitle('e-Portfolio von ' . $GLOBALS['user']->username);
        $widget = new SearchWidget();
        $sidebar->addWidget($widget);
    }
    
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        PageLayout::setTitle(_('Dozentenansicht'));
    }
    
    public function index_action()
    {
        echo "viewDozent";
    }
    
    // customized #url_for for plugins
    function url_for($to = '')
    {
        $args = func_get_args();
        
        # find params
        $params = [];
        if (is_array(end($args))) {
            $params = array_pop($args);
        }
        
        # urlencode all but the first argument
        $args    = array_map('urlencode', $args);
        $args[0] = $to;
        
        return PluginEngine::getURL($this->dispatcher->current_plugin, $params, join('/', $args));
    }
}
