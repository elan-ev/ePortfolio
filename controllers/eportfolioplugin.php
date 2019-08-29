<?php

class EportfoliopluginController extends StudipController
{

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->current_plugin;

        if (Request::get('titleChanger')) {
            $this->changeTitle();
            exit();
        }

        if (Request::get('infobox')) {
            $this->infobox(Request::get('cid'), Request::get('userid'), Request::get('selected'));
            exit();
        }

        $cid        = Course::findCurrent()->id;
        $eportfolio = Eportfoliomodel::findBySeminarId($cid);

        if ($eportfolio->group_id) {
            $action = $GLOBALS['perm']->have_studip_perm('tutor', $eportfolio->group_id) ? 'showsupervisor' : 'showstudent';

            $actions = new ActionsWidget();
            $actions->setTitle(_('Aktionen'));
            $actions->addLink(
                _('In die zugehörige Veranstaltung wechseln'),
                URLHelper::getLink('plugins.php/eportfolioplugin/' . $action . '?cid=' . $eportfolio->group_id), null, null);
            Sidebar::get()->addWidget($actions);
        }


        $sem_type_id = Config::get()->SEM_CLASS_PORTFOLIO_VORLAGE;

        $seminar = new Seminar($cid);

        if ($seminar->status == $sem_type_id) {
            $this->canEdit = true;
        }
    }

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

    }


    public function index_action()
    {
        if(Navigation::hasItem('course/eportfolioplugin')) {
            Navigation::activateItem('course/eportfolioplugin');
        }

        $userid          = $GLOBALS["user"]->id;
        $cid             = Course::findCurrent()->id;
        $this->cid       = $cid;
        $this->userId    = $userid;
        $eportfolio      = Eportfoliomodel::findBySeminarId($cid);
        $isOwner         = Eportfoliomodel::isOwner($cid, $userid);
        $owner           = $eportfolio->owner;
        $this->isVorlage = Eportfoliomodel::isVorlage($cid);
        $seminar         = new Seminar($this->cid);

        # Aktuelle Seite
        PageLayout::setTitle('ePortfolio von ' . $owner['Vorname'] . ' ' . $owner['Nachname'] . ' - Übersicht: ' . $seminar->getName());
        if ($this->isVorlage) {
            PageLayout::setTitle('ePortfolio-Vorlage - Übersicht: ' . $seminar->getName());
            $this->render_action('index_vorlage');
        }

        //get list chapters
        $chapters = Eportfoliomodel::getChapters($cid);

        //push to template
        $this->cardInfo     = $chapters; //$return_arr;
        $this->seminarTitle = $seminar->getName();
        $this->isOwner      = $isOwner;
        $this->cid          = $cid;
        $this->userid       = $userid;
        $this->owner        = $owner;

        $this->group_id  = $eportfolio->group_id;
        $this->templates = array_map('Seminar::GetInstance', EportfolioGroupTemplates::getGroupTemplates($eportfolio->group_id));
    }

    public function getCardInfos($cid)
    {
        $db         = DBManager::get();
        $return_arr = [];
        $query      = "SELECT id, title FROM mooc_blocks WHERE seminar_id = :cid AND type = 'Chapter' ORDER BY position ASC";
        $statement  = $db->prepare($query);
        $statement->execute([':cid' => $cid]);
        foreach ($statement->fetchAll() as $value) {
            $arrayOne          = [];
            $arrayOne['id']    = $value[id];
            $arrayOne['title'] = $value[title];

            // get sections of chapter
            $query     = "SELECT id, title FROM mooc_blocks WHERE parent_id = :id ORDER BY position ASC";
            $statement = $db->prepare($query);
            $statement->execute([':id' => $value[id]]);
            $arrayOne['section'] = $statement->fetchAll();

            array_push($return_arr, $arrayOne);
        }

        return $return_arr;
    }

    public function checkIfTemplate($id)
    {
        return DBManager::get()->fetchColumn(
            "SELECT template_id FROM eportfolio WHERE seminar_id = ?",
            [$id]
        );
    }

    public function changeTitle()
    {
        $sem       = new Seminar(Request::get('cid'));
        $sem->name = strip_tags(Request::get('title'));
        $sem->store();
    }

    public function deletePortfolio_action($id)
    {
        $sem = Course::findCurrent();
        $sem->delete();
        PageLayout::postSuccess(_('Das Portfolio wurde erfolgreich gelöscht'));

        $this->redirect('show/index');
    }

    public function infobox($cid, $owner_id, $selected)
    {
        $infoboxArray = [];

        if (Eportfoliomodel::isOwner($cid, $owner_id) == true) {
            /**
             * Funktioniert dieser Block überhaupt?
             */
            $infoboxArray["owner"] = true;
            $infoboxArray["users"] = [];

            //get user list
            $query     = "SELECT * FROM eportfolio_user WHERE Seminar_id = :cid";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([':cid' => $cid]);
            foreach ($statement->fetchAll() as $key) {
                $newarray           = [];
                $newarray["userid"] = $key["user_id"];
                $newarray["access"] = $key["eportfolio_access"];

                $userinfo              = User::find($key["user_id"]);
                $newarray['firstname'] = $userinfo['Vorname'];
                $newarray['lastname']  = $userinfo['Nachname'];

                $access = unserialize($newarray["access"]);

                if ($selected == 0) {
                    $keys     = array_keys($access);
                    $selected = $keys[0];
                }

                if ($access[$selected] == 1) {
                    $infoboxArray["users"][] = $newarray;
                }

            }

        } else {
            $sql = "
            SELECT
                auth_user_md5.Vorname as firstname,
                auth_user_md5.Nachname as lastname
            FROM auth_user_md5
            JOIN eportfolio ON eportfolio.owner_id = auth_user_md5.user_id
            WHERE eportfolio.Seminar_id = ?";
            $infoboxArray = DBManager::get()->fetchOne($sql, [$cid]);
        }

        print_r(json_encode($infoboxArray));
    }
}
