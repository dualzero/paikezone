<?php
function is_login(){
    $user = session('user_auth');
    if (empty($user)) {
        return 0;
    } else {
        return true;
    }
}

function check_verify($code, $id = 1){
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}

function time_format($time = NULL,$format='Y-m-d H:i'){
    $time = $time === NULL ? NOW_TIME : intval($time);
    return date($format, $time);
}