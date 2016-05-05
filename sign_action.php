<?php

require_once 'init.php';

$ag_ent = $_GET['agent'];
$agentip = $_SERVER['REMOTE_ADDR'];

if(!preg_match('/\w{12}/',$ag_ent)){
    exit();
}

if(!checkip($agentip)){
    echo 'You adddress are not allow!';
    exit();
}


$db = new db_sql_functions();
$username = $db->check_agentid($ag_ent);

if($username){
    $ntime = time();

    //Check the heartbeat
    $res = $db->check_heartbeat($username,$ntime-120);

    if($res){
        $uid = $res['uid'];
        $stime = $res['stime'];
        $db->update_heartbeat($uid,$stime);
        echo 'Agent signing successed';
    }else{
        $db->add_heartheat($username);
        echo 'Agent signing successed';
    }

}else{
    echo 'Agent not registered';
    exit();
}

function checkip($ip){
    global $ALLOW_HOST;

    $len = count($ALLOW_HOST);

    $ip_arr = explode('.',$ip);

    for($i=0;$i<$len;$i++){
        $flag = 0;
        $host_arr = explode('.',$ALLOW_HOST[$i]);
        for($j=0;$j<count($host_arr);$j++){
            if($host_arr[$j] == '*'){
                $flag += 1;
                continue;
            }
            if($ip_arr[$j] != $host_arr[$j]){
                break;
            }
            $flag += 1;
        }
        if($flag==4) return true;
    }

    return false;
}

?>
