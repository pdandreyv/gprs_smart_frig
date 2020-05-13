<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);


//===============
// INCLUDE TWIG
//===============
$rootDir = __DIR__.'/../';
require_once $rootDir.'vendor/twig/twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem($rootDir.'src/gprs/ClientBundle/Resources/views/Email/');
$twig = new Twig_Environment($loader, array(
    'cache' => false, //$rootDir.'app/cache/carlsberg/',
));

// echo $twig->render('limit.html.twig', array());

//===============
// DB CONNECT
//===============

$host = 'localhost';
$db = 'carlsberg';
$user = 'root';
$pwd = 'Innovation2cthdth';

// open connect from db
$connect = mysqli_connect($host, $user, $pwd) or die("Не могу соединиться с MySQL.");
mysqli_select_db($connect, $db) or die("Не могу подключиться к базе: ".$db);

$emails = getOtherEmails();

$alarmTemplates = array(
	'temperatura' => array(
		'1' => array(
			'title' => 'High inner temperature',
			'file' => 'innertemp.html.twig',
		),
		'2' => array(
			'title' => 'High outer temperature',
			'file' => 'outertemp.html.twig',
		),
	),
	'weight' => array(
		'1' => array(
			'title' => 'Low quantity',
			'file' => 'limit.html.twig',
		),
	),
);
$alarmTypes = array_keys($alarmTemplates);

// get data
$sql = "SELECT *
		FROM alarm AS a
		WHERE a.updated_at > NOW() - INTERVAL 1 DAY AND status = 0";
$data = mysqli_query($connect, $sql);

while($row = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
	$iceboxId = $row['icebox_id'];
	$allEmails = addEmail(getTraderEmail($iceboxId));

	// filter columns
	foreach($alarmTypes as $type) {
		if(!array_key_exists($type, $row) || !$row[$type])
			continue;

		$template = $alarmTemplates[$type][$row[$type]];

		foreach($allEmails as $email) {
            sendMail($email, $template['title'], $twig->render($template['file']));
        }
	}
}

//===============
// LIB
//===============

function addEmail($newEmail) {
	global $emails;

	$clonEmails = $emails;

	if($newEmail)
		$clonEmails[] = $newEmail;

	return $clonEmails;
}

function getOtherEmails() {
	global $connect;

	// get other emails
	$sql = "SELECT emails FROM settings WHERE id=1";
	$settings = mysqli_fetch_row(mysqli_query($connect, $sql));
	$emails = $settings[0] ? explode(',', $settings[0]) : $settings[0];
	if(!is_array($emails))
		$emails = array();

	return $emails;
}

function getTraderEmail($iceboxId) {
	global $connect;

	// get trager email
	$sql = "SELECT t.email FROM icebox AS i
			INNER JOIN trader AS t ON i.trader_id=t.id
			WHERE i.id={$iceboxId}";
	$trager = mysqli_fetch_row(mysqli_query($connect, $sql));

	return $trager[0] ? $trager[0] : NULL;
}

function sendMail($to, $subject, $message, $headers = NULL){
	if(NULL === $headers) {
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
	}

	mail($to, $subject, $message, $headers);
}

// clone connect
mysqli_close($connect);