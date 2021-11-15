<?php

class Notification {

    // TEST

    // your code here ...

    public static function get_user_notifications($data = []){
        //check request attribute
        if (isset($data["unread"])){
            $q = DB::query("SELECT * FROM user_notifications WHERE user_id='".Session::$user_id."' AND viewed=0");
        }
        else{
            $q = DB::query("SELECT * FROM user_notifications WHERE user_id='".Session::$user_id."'");
        }
        $data = DB::fetch_all($q);
        return $data;
    }

    public static function read_notifications(){
        //fetch
        $q = DB::query("UPDATE user_notifications SET viewed=1 WHERE user_id='".Session::$user_id."'");
        DB::fetch_row($q);
        return true;
    }

}
