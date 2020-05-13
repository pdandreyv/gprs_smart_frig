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

// Крон для нескольких сайтов
$sites = array(
    'carlsberg',
    'gprs',
);

foreach($sites as $site)
{
    $host = 'localhost';
    $db = $site;
    $user = 'root';
    $pwd = 'Innovation2cthdth';
    // open connect from db
    $connect = mysqli_connect($host, $user, $pwd) or die("Не могу соединиться с MySQL.");
    mysqli_select_db($connect, $db) or die("Не могу подключиться к базе: ".$db);

    //===============
    // CRON TASK
    //===============

    $emails = getOtherEmails();

    // get data
    $sql = "SELECT *, SUM(dooropen) AS sum_dooropen
                    FROM data AS d
                    WHERE d.created_at > NOW() - INTERVAL 1 DAY
                    GROUP BY d.icebox_id
                    ORDER BY d.icebox_id DESC";
    $countData = mysqli_fetch_row(mysqli_query($connect, "SELECT COUNT(*) AS count FROM ({$sql}) AS s"));

    $data = mysqli_query($connect, $sql);
    while($row = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
        $iceboxId = $row['icebox_id'];
        $allEmails = addEmail(getTraderEmail($iceboxId));

        // No door openings 24 hours
        if((int)$row['sum_dooropen'] === 0) {
            foreach($allEmails as $email) {
                sendMail($email, 'No door openings', $twig->render('dooropen.html.twig'));
            }
        }

        // no connection
        if((time() - strtotime($row['created_at'])) > 60*(60+30)) {
                // update status on disconnect
                $sql = "UPDATE `icebox` SET `status`=1 WHERE `id`={$iceboxId}";
                mysqli_query($connect, $sql);

                // send mess
        foreach($allEmails as $email) {
            sendMail($email, 'No connection', $twig->render('noconnect.html.twig'));
        }
        }

        sendOtherData($row, $allEmails);
    }

    if((int)$countData[0] === 0) {
        $sql = "SELECT *, SUM(dooropen) AS sum_dooropen
                FROM data AS d
                GROUP BY d.icebox_id
                ORDER BY d.icebox_id DESC";
        $data = mysqli_query($connect, $sql);
        while($row = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
                $iceboxId = $row['icebox_id'];

                $allEmails = addEmail(getTraderEmail($iceboxId));

                sendOtherData($row, $allEmails);
        }
    }

    // clone connect
    mysqli_close($connect);
}

function sendOtherData($row, $emails) {
    global $twig;

    // shelf data
    $shelf = array();
    for($i = 0; $i < 6; $i++) {
            $index = $i + 1;
            $p = 'p'.$index;
            $pc = 'p'.$index.'c';

            $shelf[$index] = array(
                    $p => $row[$p],
                    $pc => $row[$pc],
            );
    }

    $params = array(
            'dooropen' => $row['sum_dooropen'],
            'out_temperature' => $row['t_out'],
            'in_temperature' => $row['t_inside'],
            'weight' => $row['weight'],
            'weight_status' => $row['weight_status'],
            'shelf' => $shelf,
            'created_at' => $row['created_at'],
    );

        // other info
    foreach($emails as $email) {
        sendMail($email, 'Smart fridge daily status', $twig->render('other.html.twig', $params));
    }
}

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
            $headers .= "From: smart.cooler.ubc@beer-co.com\r\n";
    }

    mail($to, $subject, $message, $headers);
}