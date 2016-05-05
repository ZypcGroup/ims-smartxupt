<?php

class db_sql_functions
{
    private $dbconn;

    function __construct()
    {
        $this->dbconn = new \db();
        return $this;
    }

    /*
    * 检查username合法性
    * 参数：username
    * 返回值：username / false
    */
    public function check_username($username)
    {
        if(is_numeric($username)){
            $sql = "SELECT username FROM users WHERE username=?";
            $stmt = $this->dbconn->get_mysqli()->stmt_init();
            $stmt->prepare($sql);
            $stmt->bind_param('s',$username);
            $stmt->execute();
            $stmt->bind_result($user);
            
            $result = $stmt->fetch();
            if($result){
                return $user;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    
    /*
    * 检查user合法性
    * 参数：username,password
    * 返回值：username / false
    */
    public function check_user($username,$password)
    {
        if(is_numeric($username)){
            $sql = "SELECT username FROM users WHERE username=? AND password=?";
            $stmt = $this->dbconn->get_mysqli()->stmt_init();
            $stmt->prepare($sql);
            $stmt->bind_param('ss',$username,$password);
            $stmt->execute();
            $stmt->bind_result($usernm);

            $result = $stmt->fetch();
            if($result){
                return $usernm;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    
    /*
    * 添加周小结
    * 参数：username,last_sum,this_play,detial_play
    * 返回值：true / false
    */
    public function add_asseing($username,$last_sum,$this_play,$detial_play)
    {
        $pubdate = date('Y-m-d H:i:s');
        $sql = "INSERT INTO asseing(username,pubdate,last_sum,this_play,detial_play) VALUES(?,?,?,?,?)";
        $stmt = $this->dbconn->get_mysqli()->stmt_init();
        $stmt->prepare($sql);
        $stmt->bind_param('sssss',$username,$pubdate,$last_sum,$this_play,$detial_play);
        $result = $stmt->execute();
        
        if($result){
            return true;
        }else{
            return false;
        }
    }
    
    /*
    * 获取上周小结
    * 参数：username
    * 返回值：array(result) / false
    */
    public function get_last_play($username)
    {
        $sql = "SELECT detial_play,admin_rate,admin_rank,timelong FROM asseing WHERE username=? ORDER BY uid DESC LIMIT 1";

        $stmt = $this->dbconn->get_mysqli()->stmt_init();
        $stmt->prepare($sql);
        $stmt->bind_param('s',$username);
        $stmt->execute();
        $stmt->bind_result($detial_play,$admin_rate,$admin_rank,$timelong);

        $result = $stmt->fetch();
        if($result){
            $result = array(
                'detial_play'=>$detial_play,
                'admin_rate'=>$admin_rate,
                'admin_rank'=>$admin_rank,
                'timelong'=>$timelong);

            return $result;
        }else{
            return false;
        }
    }
    
    /*
    * 添加组长评价
    * 参数：uid,rate,rank,timelong
    * 返回值：true / false
    */
    public function add_admin_asse($uid,$rate,$rank,$timelong)
    {
        $sql = "UPDATE asseing SET admin_rate=?,admin_rank=?,timelong=?,admin_flag=1 WHERE uid=? ";
        $stmt = $this->dbconn->get_mysqli()->stmt_init();
        $stmt->prepare($sql);
        $stmt->bind_param('sssi',$rate,$rank,$timelong,$uid);
        $result = $stmt->execute();

        if($result){
            return true;
        }else{
            return false;
        }
    }

    /*
    * 检查agentid合法性
    * 参数：agentid
    * 返回值：agent_id / false
    */
    public function check_agentid($agentid)
    {
        if(preg_match('/\w{12}/',$agentid)){
            $sql = "SELECT username FROM users WHERE mac_addr1=? OR mac_addr2=?";
            $stmt = $this->dbconn->get_mysqli()->stmt_init();
            $stmt->prepare($sql);
            $stmt->bind_param('ss',$agentid,$agentid);
            $stmt->execute();
            $stmt->bind_result($username);
                
            $result = $stmt->fetch();
            if($result){
                return $username;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /*
    * 检查近期用户在线数
    * 参数：alive
    * 返回值：array(username) / false
    */
    public function check_online_users($timelive)
    {
        $ntime = time();
        $ltime = $ntime - $timelive;

        $sql = "SELECT username FROM signing WHERE etime>=?";
        $stmt = $this->dbconn->get_mysqli()->stmt_init();
        $stmt->prepare($sql);
        $stmt->bind_param('i',$ltime);
        $stmt->execute();
        $stmt->bind_result($usernm);

        $result = array();
        while($row = $stmt->fetch()){
            if ($row){
                array_push($result,$usernm);
            }
        }

        return $result;
    }

    /*
    * 检查心跳存活状态
    * 参数：username,alive
    * 返回值：array(uid,stime) / false
    */
    public function check_heartbeat($username,$alive)
    {
        $sql = "SELECT uid,stime FROM signing WHERE username=? AND etime>=?";
        $stmt = $this->dbconn->get_mysqli()->stmt_init();
        $stmt->prepare($sql);
        $stmt->bind_param('si',$username,$alive);
        $stmt->execute();
        $stmt->bind_result($uid,$stime);

        $result = $stmt->fetch();
        if ($result){
            $result = array('uid'=>$uid,'stime'=>$stime);
            return $result;
        }else{
            return false;
        }
    }

    /*
    * 更新签到记录
    * 参数：uid
    * 返回值: true / false
    */
    public function update_heartbeat($uid,$stime)
    {
        $ntime = time();
        $long = $ntime - $stime;

        $sql = "UPDATE signing SET  etime=? ,longs=? WHERE uid=?";
        $stmt = $this->dbconn->get_mysqli()->stmt_init();
        $rs = $stmt->prepare($sql);
        $stmt->bind_param('iii',$ntime,$long,$uid);
        $result = $stmt->execute();

        if ($result)
            return true;
        else
            return false;
    }

    /*
    * 添加签到记录
    * 参数： username
    * 返回值：true / false
    */
    public function add_heartheat($username)
    {
        $ntime = time();
        $ndate = date('m-d');
        $long = 0;

        $sql = "INSERT INTO signing (username,stime,etime,dates,longs) VALUES (?,?,?,?,?)";
        $stmt = $this->dbconn->get_mysqli()->stmt_init();
        $rs = $stmt->prepare($sql);
        $stmt->bind_param('siisi',$username,$ntime,$ntime,$ndate,$long);
        $result = $stmt->execute();

        if ($result)
            return true;
        else
            return false;
    }
}
