<?

class Supervisorgroup{

  var $supervisorgroupId = null;
  var $supervisorgroupName = "DEFAULT_NAME";

  public function __construct() {
    $this->setId();
  }

  private function setId(){
    $sem = new Seminar();
    $this->supervisorgroupId = $sem->createId();
  }

  public function setName($name){
    $this->supervisorgroupName = $name;
  }

  public function getId(){
    return $this->supervisorgroupId;
  }

  public function getName(){
    return $this->supervisorgroupName;
  }

  public function save(){
    DBManager::get()->query("INSERT INTO supervisor_group (id, name) VALUES ('$this->supervisorgroupId', '$this->supervisorgroupName')");
  }

}
