<?php

require_once "init.php";

if(!isset($_SESSION['username']) || $_SESSION['username']==false){
    $_SESSION['userurl'] = $_SERVER['REQUEST_URI'];
    echo "<meta http-equiv=\"Refresh\" content=\"0;url=login.php\">";
    exit();
}

$smarty->display('sign_home.tpl');
?>
