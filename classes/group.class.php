<?

class Group{

  var $groupId;

  public function __construct($id) {
    $groupid = $id;
  }

  public static function getTemplates($id){
    $q = DBManager::get()->query("SELECT templates FROM eportfolio_groups WHERE seminar_id = '$id'")->fetchAll();
    $q = json_decode($q[0][0], true);
    return $q;
  }

  public static function getGroupMember($id) {
    $q = DBManager::get()->query("SELECT user_id FROM eportfolio_groups_user WHERE Seminar_id = '$id'")->fetchAll();
    $array = array();
    foreach ($q as $key) {
      array_push($array, $key[0]);
    }
    return $array;
  }

  public static function create($owner, $title, $text){
    $course = new Seminar();
    $id = $course->getId();
    $course->store();
    $course->addMember($owner, 'dozent', true);

    $edit = new Course($id);
    $edit->visible = 0;
    $edit->store();

    DBManager::get()->query("UPDATE seminare SET Name = '$title', Beschreibung = '$text', status = 142 WHERE Seminar_id = '$id' ");
    DBManager::get()->query("INSERT INTO eportfolio_groups (seminar_id, owner_id) VALUES ('$id', '$owner')");

    echo $id;
  }

  public static function deleteUser($userId, $seminar_id){
    DBManager::get()->query("DELETE FROM eportfolio_groups_user WHERE user_id = '$userId' AND seminar_id = '$seminar_id'");
    return true;
  }

  public static function getOwner($id){
    $q = DBManager::get()->query("SELECT owner_id FROM eportfolio_groups WHERE seminar_id = '$id'")->fetchAll();
    return $q[0][0];
  }

  public function getGroupId(){
    return $groupid;
  }

}
