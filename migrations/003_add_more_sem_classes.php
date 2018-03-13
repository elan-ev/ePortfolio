<?php

require __DIR__.'/../vendor/autoload.php';

class AddMoreSemClasses extends Migration
{
    public function description () {
        return 'add SemClass and SemTypes for Supervisionsgruppen and ePortfolio-Vorlagen';
    }


    public function up () {
        $this->insertPortfolioVorlageSemClass();
    }


    public function down () {
        // $id = $this->getMoocSemClassID();
        // $this->removeSemClassAndTypes($id);
        // $this->removeConfigOption();
        // SimpleORMap::expireTableScheme();

        $db = DBManager::get();

       
        //remove entry in sem_classes
        $name = "ePortfolio-Vorlage";
        $statement = $db->prepare("DELETE FROM sem_classes WHERE name = ?");
        $statement->execute(array($name));

        //remove entry in sem_types
        $nameType = "ePortfolio-Vorlage";
        $statement = $db->prepare("DELETE FROM sem_types WHERE name = ?");
        $statement->execute(array($nameType));
    }
  
    private function insertPortfolioVorlageSemClass()
    {
        $db = DBManager::get();
        $name = "ePortfolio-Vorlage";
        $nameType = "Portfolio-Vorlage";
        $id = -2;

        //FÃ¼gt Spalte an true or false ePortfolio
        // $nameType = "eportfolioStatus";
        // $statement = $db->prepare("ALTER TABLE seminare ADD ? BOOLEAN");
        // $statement->execute(array($nameType));

        if($this->validateUniqueness($name)) {
    			$statement = $db->prepare("INSERT INTO sem_classes SET name = ?, mkdate = UNIX_TIMESTAMP(), chdate = UNIX_TIMESTAMP()");
    			$statement->execute(array($name));
    			$id = $db->lastInsertId();

          //Insert sem_type
          $statementSemTypes = $db->prepare("INSERT INTO sem_types SET name = ?, class = $id, mkdate = UNIX_TIMESTAMP(), chdate = UNIX_TIMESTAMP()");
          $statementSemTypes->execute(array($nameType));

    	    } else {
    			// We already got a type with that name, should be a previous installation ...
                $statement = $db->prepare('SELECT id FROM sem_classes WHERE name = ?');
                $statement->execute(array($name));
                $id = $statement->fetchColumn();
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
        $current_modules['EportfolioPlugin']['activated'] = '1';
        $current_modules['EportfolioPlugin']['sticky'] = '1'; 
        $current_modules['Courseware']['activated'] = '1';   // set values
        $current_modules['Courseware']['sticky'] = '1'; // sticky = 1 -> can't be chosen in "more"-field of course
        $current_modules['CoreParticipants']['activated'] = '0';
        $current_modules['CoreParticipants']['sticky'] = '0'; 
        $current_modules['CoreDocuments']['activated'] = '0';
        $current_modules['CoreDocuments']['sticky'] = '0'; 
        $current_modules['CoreOverview']['activated'] = '0';
        $current_modules['CoreOverview']['sticky'] = '1'; 
        $current_modules['CoreAdmin']['activated'] = '0';
        $current_modules['CoreAdmin']['sticky'] = '1';

        $sem_class->set('overview', 'EportfolioPlugin');
        $sem_class->setModules($current_modules); // set modules

        $sem_class->store();

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
