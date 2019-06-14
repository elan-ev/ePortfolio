<?php

require __DIR__.'/../vendor/autoload.php';

class AddSupervisorgroupSemClass extends Migration
{
    public function description () {
        return 'add SemClass and SemType for Supervisionsgruppen';
    }


    public function up () {
        $this->insertSupervisionsgruppeSemClass();
    }


    public function down () {
        // $id = $this->getMoocSemClassID();
        // $this->removeSemClassAndTypes($id);
        // $this->removeConfigOption();
        // SimpleORMap::expireTableScheme();

        $db = DBManager::get();

        //remove entry in sem_classes
        $name = "Supervisionsgruppe";
        $statement = $db->prepare("DELETE FROM sem_classes WHERE name = ?");
        $statement->execute(array($name));
        //remove entry in sem_types
        $nameType = "Supervisionsgruppe";
        $statement = $db->prepare("DELETE FROM sem_types WHERE name = ?");
        $statement->execute(array($nameType));

        $db->exec("DELETE FROM config WHERE field = 'SEM_CLASS_PORTFOLIO_Supervisionsgruppe'");
    }

    private function insertSupervisionsgruppeSemClass()
    {
        $db = DBManager::get();
        $name = "Supervisionsgruppe";
        $nameType = "Supervisionsgruppe";
        $id = -2;

        //Füt Spalte an true or false ePortfolio
        // $nameType = "eportfolioStatus";
        // $statement = $db->prepare("ALTER TABLE seminare ADD ? BOOLEAN");
        // $statement->execute(array($nameType));
        if ($this->validateUniqueness($name)) {
			$statement = $db->prepare("INSERT INTO sem_classes SET name = ?, mkdate = UNIX_TIMESTAMP(), chdate = UNIX_TIMESTAMP()");
			$statement->execute(array($name));
			$id = $db->lastInsertId();

            //Insert sem_type
            $statementSemTypes = $db->prepare("INSERT INTO sem_types SET name = ?, class = $id, mkdate = UNIX_TIMESTAMP(), chdate = UNIX_TIMESTAMP()");
            $statementSemTypes->execute(array($nameType));
            $st_id = $db->lastInsertId();
    	} else {
			// We already got a type with that name, should be a previous installation ...
            $statement = $db->prepare('SELECT id FROM sem_classes WHERE name = ?');
            $statement->execute([$name]);
            $id = $statement->fetchColumn();

            $statement = $db->prepare('SELECT id FROM sem_types WHERE name = ? AND class = ?');
            $statement->execute([$name, $id]);
            $st_id = $statement->fetchColumn();
		}

		if($id === -2) {
			$message = sprintf('Ungültige id (id=%d)', $id);
            throw new Exception($message);
		}

        $sem_class = SemClass::getDefaultSemClass();
        $sem_class->set('name', $name);
        $sem_class->set('id', $id);

        // Setting Mooc-courses default datafields: mooc should not to be disabled, courseware and mooc should be active
         $current_modules = $sem_class->getModules(); // get modules
        $current_modules = $sem_class->getModules(); // get modules
        $current_modules['EportfolioPlugin']['activated'] = '1';
        $current_modules['EportfolioPlugin']['sticky'] = '1';
        $current_modules['Courseware']['activated'] = '0';   // set values
        $current_modules['Courseware']['sticky'] = '1'; // sticky = 1 -> can't be chosen in "more"-field of course
        $current_modules['CoreParticipants']['activated'] = '0';
        $current_modules['CoreParticipants']['sticky'] = '0';
        $current_modules['CoreDocuments']['activated'] = '1';
        $current_modules['CoreDocuments']['sticky'] = '1';
        $current_modules['CoreOverview']['activated'] = '0';
        $current_modules['CoreOverview']['sticky'] = '1';
        $current_modules['CoreAdmin']['activated'] = '0';
        $current_modules['CoreAdmin']['sticky'] = '1';


        $sem_class->set('overview', 'EportfolioPlugin');
        $sem_class->setModules($current_modules); // set modules
        $sem_class->store();

        Config::get()->create('SEM_CLASS_PORTFOLIO_Supervisionsgruppe', array(
            'value'       => $st_id,
            'is_default'  => 0,
            'type'        => 'integer',
            'range'       => 'global',
            'section'     => 'global',
            'description' => 'ID der Veranstaltungsklasse für Portfolio-Supervisionsgruppen'
            ));


        return $id;
    }


    private function validateUniqueness($name)
    {
        $statement = DBManager::get()->prepare('SELECT id FROM sem_classes WHERE name = ?');
        $statement->execute(array($name));
        if ($old = $statement->fetchColumn()) {
            // $message = sprintf('Es existiert bereits eine Veranstaltungskategorie mit dem Namen "%s" (id=%d)', htmlspecialchars($name), $old);
            // throw new Exception($message);
            return false;
        }
        return true;
    }

    private function removeSemClassAndTypes($id)
    {
        $sem_class = new SemClass(intval($id));
        $sem_class->delete();
        $GLOBALS['SEM_CLASS'] = SemClass::refreshClasses();
    }

}
