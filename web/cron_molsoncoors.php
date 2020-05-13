<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

$host='localhost'; // имя хоста (уточняется у провайдера)
$database='molsoncoors'; // имя базы данных, которую вы должны создать
$user='root'; // заданное вами имя пользователя, либо определенное провайдером
$pswd='Innovation2cthdth'; // заданный вами пароль

$dbh = mysql_connect($host, $user, $pswd) or die("Не могу соединиться с MySQL.");
mysql_select_db($database) or die("Не могу подключиться к базе.");

    function sql ($query) {
        $res = mysql_query($query);
        $result = array();
        $i=0;
        while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $result[] = $row;
            $i++;
        }
        return $result;
    }

       
    // Посылка алармов по отключению
    $res = sql("SELECT id FROM icebox WHERE status=2 AND id NOT IN (SELECT icebox_id FROM `data` WHERE created_at > NOW() - INTERVAL 5 MINUTE GROUP BY icebox_id)");
    if(count($res)){
        $query = "UPDATE `icebox` SET status=1 WHERE status=2 AND id NOT IN (SELECT icebox_id FROM `data` WHERE created_at > NOW() - INTERVAL 5 MINUTE GROUP BY icebox_id)";
        $res = mysql_query($query);
        $query = "INSERT INTO alarm (created_at,update_at,icebox_id,power,status) VALUES (NOW(),NOW(),1,1,0)";
        $res = mysql_query($query);
        $subj = "Alarm!";
        $mess = "Alarm! Power is off.";
        $traders = sql("SELECT email FROM trader WHERE alarm_power=1");
        foreach($traders as $trader){
            mail($trader['email'],$subj,$mess,"From: ubc@uaic.net");
        }
    }

    
mysql_close($dbh);
echo date('Y-m-d h:i:s')."\n";
?>