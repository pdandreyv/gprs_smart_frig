<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

$host='localhost';
$databases = array('carlsberg','pepsi');
$user='root';
$pswd='Innovation2cthdth';
/*
$host='localhost';
$database='baltika';
$user='root';
$pswd='Innovation2cthdth';
/*
$host='localhost';
$database='gprs';
$user='root';
$pswd='';
*/
foreach($databases as $database) {
    $dbh = mysql_connect($host, $user, $pswd) or die("Не могу соединиться с MySQL.");
    mysql_select_db($database) or die("Не могу подключиться к базе: ".$database);

        $query = "UPDATE `icebox` SET status=1 WHERE status=2 AND id NOT IN (SELECT icebox_id FROM `data` WHERE created_at > NOW() - INTERVAL 15 MINUTE GROUP BY icebox_id)";
        $res = mysql_query($query);

    mysql_close($dbh);
}
?>