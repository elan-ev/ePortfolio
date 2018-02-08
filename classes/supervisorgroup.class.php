<?

class Supervisorgroup{

  var $supervisorgroupId = null;
  var $supervisorgroupName = "DEFAULT_NAME";

  public function __construct($group_or_false = FALSE) {
    if ($group_or_false == FALSE) {
      $this->setId();
    } else {
      $this->supervisorgroupId = $group_or_false;
    }
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
    $query = DBManager::get()->query("SELECT name FROM supervisor_group WHERE id = '$this->supervisorgroupId'")->fetchAll();
    return $query[0][name];
  }

  public function getUsersOfGroup(){
    $query = DBManager::get()->query("SELECT * FROM supervisor_group_user WHERE supervisor_group_id = '$this->supervisorgroupId'")->fetchAll();
    return $query;
  }

  public function addUser($userId){
    DBManager::get()->query("INSERT INTO supervisor_group_user (supervisor_group_id, user_id) VALUES ('$this->supervisorgroupId', '$userId')");
  }

  public function deleteUser($userId){
    DBManager::get()->query("DELETE FROM supervisor_group_user WHERE supervisor_group_id = '$this->supervisorgroupId' AND user_id = '$userId'");
  }

  public function save(){
    DBManager::get()->query("INSERT INTO supervisor_group (id, name) VALUES ('$this->supervisorgroupId', '$this->supervisorgroupName')");
  }

}
