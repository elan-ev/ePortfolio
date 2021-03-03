<?php

class EportfoliopluginController extends PluginController
{
    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);

        $this->cid             = $this->plugin->context->id;
        $this->eportfolio      = EportfolioModel::findBySeminarId($this->plugin->context->id);
        $this->group_id        = $this->eportfolio->group_id;
        $this->supervisorgroup = SupervisorGroup::findOneBySQL('Seminar_id = ?', [$this->group_id]);

        if ($this->group_id) {
            $action  = $GLOBALS['perm']->have_studip_perm('tutor', $this->group_id) ? 'showsupervisor' : 'showstudent';

            $actions = new ActionsWidget();
            $actions->setTitle(_('Aktionen'));
            $actions->addLink(
                _('In die zugehörige Veranstaltung wechseln'),
                URLHelper::getLink('plugins.php/eportfolioplugin/' . $action . '?cid=' . $this->group_id), null, null);
            Sidebar::get()->addWidget($actions);
        }
    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $owner   = $this->eportfolio->owner;
        # Aktuelle Seite
        PageLayout::setTitle('ePortfolio von ' . $owner['Vorname'] . ' ' . $owner['Nachname'] . ': ' . $this->plugin->context->getFullname());
        if (EportfolioModel::isVorlage($this->plugin->context->id)) {
            PageLayout::setTitle('ePortfolio-Vorlage - Übersicht: ' . $this->plugin->context->getFullname());
            $this->render_action('index_vorlage');
        }

        if(Navigation::hasItem('course/eportfolioplugin')) {
            Navigation::activateItem('course/eportfolioplugin');
        }
    }

    public function index_action()
    {
        $this->templates  = EportfolioGroupTemplates::getUserChapterInfos($this->group_id, $this->plugin->context->id);
    }
}