<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

$name = explode('.', $_SERVER['SERVER_NAME']);

$host='localhost'; // имя хоста (уточняется у провайдера)
$database=$name[0]; // имя базы данных, которую вы должны создать
$user='root'; // заданное вами имя пользователя, либо определенное провайдером
$pswd='Innovation2cthdth'; // заданный вами пароль
 
$dbh = mysql_connect($host, $user, $pswd) or die("Не могу соединиться с MySQL.");
mysql_select_db($database) or die("Не могу подключиться к базе.");

$icebox_id = (isset($_GET['id']))? $_GET['id'] : 1;

    $query = "SELECT id,created_at,pin1_count,adc,temp,par1,par2,weight FROM `data` WHERE icebox_id = ".$icebox_id." ORDER BY id DESC LIMIT 0,50";
    $res = mysql_query($query);
    $result = array();
    $i=0;
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
        $result[] = $row;
        $i++;
    }

$html = '';
if(count($result)){
    $html .= "<table border='1'><tr>";
    foreach($result[0] as $key=>$val){
	$html .= "<th>".$key."</th>";
    }
    $html .= "</tr>";
    foreach($result as $row){
	$html .= "<tr>";
	foreach($row as $key=>$val){
	    $html .= "<td>".$val."</td>";
	}
	$html .= "</tr>";
    }
    $html .= "</table>";
}
echo $html;
mysql_close($dbh);

?>