<?php

class blocksettingsController extends PluginController
{
    public function index_action($cid = null)
    {
        // set vars
        $this->course  = Course::findCurrent();
        $userid        = $GLOBALS['user']->id;
        $this->cid     = Request::option('cid');
        $this->vorlage = EportfolioModel::findBySeminarId($this->cid);


        # Aktuelle Seite
        PageLayout::setTitle('ePortfolio-Vorlage - Einstellungen: ' . $this->course->getFullname());

        //autonavigation
        Navigation::activateItem("course/blocksettings");

        $sidebar = Sidebar::Get();
        $sidebar->setTitle('Navigation');

        $views = new ViewsWidget();
        $views->setTitle('Rechte');
        $views->addLink(_('Rechteverwaltung'), '#')->setActive(true);
        Sidebar::get()->addWidget($views);

        //get list chapters
        $chapters = EportfolioModel::getChapters($this->course->id);

        //get viewer information
        $viewers = $this->course->getMembersWithStatus('autor');
        //push to template
        $this->userid        = $userid;
        $this->title         = $this->course->getFullname();
        $this->chapterList   = $chapters; //$arrayList;
        $this->viewerList    = $viewers; //$return_arr;
        $this->numberChapter = count($chapters);
    }

    public function lockBlock_action($seminar_id, $block_id, $lock)
    {
        LockedBlock::lockBlock($seminar_id, $block_id, $lock);
        $this->render_nothing();
    }
}
