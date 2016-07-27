<?php

require __DIR__.'/../vendor/autoload.php';

class AddSemClass extends Migration
{
    public function description () {
        return 'add SemClass and SemTypes whose courses have this plugin in their overview slot';
    }


    public function up () {
        $id = $this->insertSemClass();
        // $this->addSemTypes($id);
        // $this->addConfigOption($id);
        SimpleORMap::expireTableScheme();
    }


    public function down () {
        // $id = $this->getMoocSemClassID();
        // $this->removeSemClassAndTypes($id);
        // $this->removeConfigOption();
        // SimpleORMap::expireTableScheme();
    }

    /**********************************************************************/
    /* PRIVATE METHODS                                                    */
    /**********************************************************************/

    private function getMoocSemClassID()
    {
        return Config::get()->getValue(\Mooc\SEM_CLASS_CONFIG_ID);
    }

    private function insertSemClass()
    {
        $db = DBManager::get();
        $name = "ePortfolio";
        $id = -2;

        if($this->validateUniqueness($name)) {
			$statement = $db->prepare("INSERT INTO sem_classes SET name = ?, mkdate = UNIX_TIMESTAMP(), chdate = UNIX_TIMESTAMP()");
			$statement->execute(array($name));
			$id = $db->lastInsertId();
	    } else {
			// We already got a type with that name, should be a previous installation ...
            $statement = $db->prepare('SELECT id FROM sem_classes WHERE name = ?');
            $statement->execute(array($name));
            $id = $statement->fetchColumn();
		}

		if($id === -2) {
			$message = sprintf('Ung�ltige id (id=%d)', $id);
            throw new Exception($message);
		}


        $sem_class = SemClass::getDefaultSemClass();
        $sem_class->set('name', $name);
        $sem_class->set('id', $id);

        // Setting Mooc-courses default datafields: mooc should not to be disabled, courseware and mooc should be active
        $current_modules = $sem_class->getModules(); // get modules
        $current_modules['Mooc']['activated'] = '1'; // set values
        $current_modules['Mooc']['sticky'] = '1'; // sticky = 1 -> can't be chosen in "more"-field of course
        $current_modules['Courseware']['activated'] = '1';
        $current_modules['Courseware']['sticky'] = '0';
        $current_modules['VipsPlugin']['activated'] = '1';
        $current_modules['VipsPlugin']['sticky'] = '0';

        $sem_class->setModules($current_modules); // set modules

        $sem_class->store();


        // set Mooc-Plugin as not choosable in all other sem-classes
        // foreach ($GLOBALS['SEM_CLASS'] as $sc) {
        //     if ($sc['name'] == $name) continue;                                 // do not overwrite mooc-class
        //
        //     $modules = $sc->getModules();                                       // get modules
        //     $modules['Mooc']['activated'] = '1';                                // set values
        //     $modules['Mooc']['sticky'] = '1';                                   // sticky = 1 -> disable on plus-page in seminar
        //
        //     $sc->setModules($current_modules);                                  // set modules
        //     $sc->store();
        // }

        // $GLOBALS['SEM_CLASS'] = SemClass::refreshClasses();

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

    private function addSemTypes($sc_id)
    {
        $db = DBManager::get();

        foreach (words(\Mooc\SEM_TYPE_NAMES) as $name) {

            // Test wether that type already exists in db, if so, don't insert another one
            $alreadyExists = $db->prepare('SELECT id FROM sem_types WHERE name = ?');
            $alreadyExists->execute(array($name));
            if(!$alreadyExists->fetchColumn()) {
                $statement = $db->prepare(
                    "INSERT INTO sem_types SET name = ?, class = ?, mkdate = UNIX_TIMESTAMP(), chdate = UNIX_TIMESTAMP()");
                $statement->execute(array($name, $sc_id));
            }

        }
        $GLOBALS['SEM_TYPE'] = SemType::refreshTypes();
    }

    private function addConfigOption($sc_id)
    {
        Config::get()->create(\Mooc\SEM_CLASS_CONFIG_ID, array(
            'value'       => $sc_id,
            'is_default'  => 0,
            'type'        => 'integer',
            'range'       => 'global',
            'section'     => 'global',
            'description' => 'ID der Veranstaltungsklasse f�r (M)OOC-Veranstaltungen.'
            ));
    }

    private function removeSemClassAndTypes($id)
    {
        $sem_class = new SemClass(intval($id));
        $sem_class->delete();
        $GLOBALS['SEM_CLASS'] = SemClass::refreshClasses();
    }

    private function removeConfigOption()
    {
        return Config::get()->delete(\Mooc\SEM_CLASS_CONFIG_ID);
    }
}
