  <?php
class ShowController extends StudipController {

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;
    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->set_layout($GLOBALS['template_factory']->open('layouts/base.php'));
//      PageLayout::setTitle('');
    }

    public function index_action()
    {
        $sidebar = Sidebar::Get();
        Sidebar::Get()->setTitle('e-Portfolio');

        $widget1 = new SearchWidget();
        Sidebar::Get()->addWidget($widget1, 'search1');

    }

<<<<<<< HEAD
    public function createPortfolio () {


      

    }

=======
>>>>>>> 5d0dd0069747f8a8fcdfd26acd14232ecc9425d0
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
