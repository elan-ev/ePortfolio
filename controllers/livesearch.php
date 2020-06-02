<?php

class livesearchController extends StudipController
{
    public function index_action()
    {
        //set retun array
        $return_arr = [];

        // set vars
        $cid = Request::get('cid');

        //set ajax vars
        $user_status = Request::get('status');
        $val         = Request::get('val');

        // empty input
        if ($val == "") {
            $val = [];
            exit(json_encode($val));
        }

        //query
        if (Request::get('searchViewer')) {
            $query     = "SELECT Vorname, Nachname, user_id FROM auth_user_md5 WHERE Vorname LIKE '%:vorname%' OR Nachname LIKE '%:nachname%'";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([':vorname' => $val[0], ':nachname' => $val[0]]);
            $search_query = $statement->fetchAll();
        } elseif (Request::get('searchSupervisor')) {
            $query     = "SELECT Vorname, Nachname, user_id FROM auth_user_md5 WHERE Vorname LIKE '%:string_1%' OR Nachname LIKE '%:string_1%' OR Vorname LIKE '%:string_2%' OR Nachname LIKE '%string_2%' AND perms = :user_status";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([':string_1' => $val, ':string_2' => $val[1], ':user_status' => $user_status]);
            $search_query = $statement->fetchAll();
        }

        $query     = "SELECT * FROM seminar_user WHERE Seminar_id = :cid AND user_id = :user_id_viewer";
        $statement = DBManager::get()->prepare($query);

        foreach ($search_query as $key) {

            $user_id_viewer = $key['user_id'];

            $statement->execute([':cid' => $cid, ':user_id_viewer' => $user_id_viewer]);

            if (empty($statement->fetchAll())) {
                $arrayOne             = [];
                $arrayOne["Vorname"]  = $key['Vorname'];
                $arrayOne["Nachname"] = $key['Nachname'];
                $arrayOne["userid"]   = $key['user_id'];

                array_push($return_arr, $arrayOne);
            }
        }
        $this->render_json($return_arr);
    }
}
