<?php

class User {

    // GENERAL

    public static function user_info($data) {
        // vars
        $user_id = isset($data['user_id']) && is_numeric($data['user_id']) ? $data['user_id'] : 0;
        $phone = isset($data['phone']) ? preg_replace('~[^\d]+~', '', $data['phone']) : 0;
        // where
        if ($user_id) $where = "user_id='".$user_id."'";
        else if ($phone) $where = "phone='".$phone."'";
        else return [];
        // info
        $q = DB::query("SELECT user_id, first_name, last_name, middle_name, phone, email, gender_id, count_notifications FROM users WHERE ".$where." LIMIT 1;") or die (DB::error());
        if ($row = DB::fetch_row($q)) {
            return [
                'id' => (int) $row['user_id'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'middle_name' => $row['middle_name'],
                'gender_id' => (int) $row['gender_id'],
                'email' => $row['email'],
                'phone' => (int) $row['phone'],
                'phone_str' => phone_formatting($row['phone']),
                'count_notifications' => (int) $row['count_notifications']
            ];
        } else {
            return [
                'id' => 0,
                'first_name' => '',
                'last_name' => '',
                'middle_name' => '',
                'gender_id' => 0,
                'email' => '',
                'phone' => '',
                'phone_str' => '',
                'count_notifications' => 0
            ];
        }
    }

    public static function user_get_or_create($phone) {
        // validate
        $user = User::user_info(['phone' => $phone]);
        $user_id = $user['id'];
        // create
        if (!$user_id) {
            DB::query("INSERT INTO users (status_access, phone, created) VALUES ('3', '".$phone."', '".Session::$ts."');") or die (DB::error());
            $user_id = DB::insert_id();
        }
        // output
        return $user_id;
    }

    // TEST

    public static function owner_info() {
        //fetch
        $q = DB::query("SELECT user_id FROM sessions WHERE token='".Session::$token."'");
        $user = User::user_info(['user_id'=>DB::fetch_row($q)["user_id"]]);
        return $user;
    }

    public static function owner_update($data = []) {
        //validate
        if (!isset($data["first_name"]) && !isset($data["last_name"]) && !isset($data["middle_name"]) && !isset($data["email"]) && !isset($data["phone"])){
            return error_response(1002,"Invalid request: No required parameters in request");
        }
        if (strlen($data["first_name"]) == 0) return error_response(1002,"Invalid request: field 'first_name' cannot be null or empty");
        if (strlen($data["last_name"]) == 0) return error_response(1002,"Invalid request: field 'last_name' cannot be null or empty");
        if (strlen($data["phone"]) == 0) return error_response(1002,"Invalid request: field 'phone' cannot be null or empty");
        $data["phone"] = preg_replace("/[^0-9]/", "", $data["phone"]);
        if (strlen($data["phone"]) != 11 && substr($data["phone"],0,1) != "7") return error_response(1002,"Invalid request: Phone number should be written in this pattern +7-900-000-00-00");
        //prepare request
        $prepared_data = array();
        foreach(array_keys($data) as $item){
            if (strlen($data[$item]) > 0){
                array_push($prepared_data,$item."='".$data[$item]."'");
            }
        }
        $prepared_query = implode(",",$prepared_data);
        //execute request to DB
        $q = DB::query("UPDATE users SET ".$prepared_query." WHERE user_id='".Session::$user_id."'");
        $q->fetch();
        $q = DB::query("INSERT INTO user_notifications(user_id,title,description,viewed,created) VALUES ('".Session::$user_id."','Information updated','User information was updated',0,".time().")");
        $q->fetch();
        return true;
    }

}
