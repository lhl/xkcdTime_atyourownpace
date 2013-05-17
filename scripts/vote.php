<?php
/*
 * Handles votes for or against special frames.
 */
include('../config.php');

if(isset($_REQUEST['frame'])) {
    $frame = $_REQUEST['frame'];
    $vote = $_REQUEST['vote'];
    $votelimit = 5; //set low for testing

    try {
        $DBH = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_WRITE_USER, DB_WRITE_PASS);
        
        $ipaddress = $_SERVER["REMOTE_ADDR"];
        $STH = $DBH->query("SELECT `votes`, `timestamp` FROM `voters` WHERE `ip`=\"$ipaddress\"");
        $result = $STH->fetch();

        $datetime1 = new DateTime($result['timestamp']);
        $datetime2 = new DateTime("now");
        $interval = $datetime1->diff($datetime2);
        $diff = $interval->format('%R%a days');
        
        if(intval($diff) > 0) {
            $STH = $DBH->prepare("UPDATE voters SET votes=1 WHERE `ip`=\"$ipaddress\"");
            $STH->execute(array($frame));
            echo "Thanks for voting.";
            
        } else if($result['votes'] < $votelimit) {
            $STH = $DBH->prepare("UPDATE votes SET $vote=$vote+1 WHERE frame=?");
            $STH->execute(array($frame));
            
            $STH = $DBH->prepare("INSERT INTO voters (votes, ip) VALUES (1, ?) ON DUPLICATE KEY UPDATE votes=votes+1");
            $STH->execute(array($ipaddress));
            
            echo "Thanks for voting.";
        } else {
            echo "You reached the daily vote limit of " . $votelimit . ". The time and dedication you've contributed will go unnoticed.";
        }


        

    } catch(PDOException $e) {
        $dblog = "../data/dblog.txt"; //Stores database exceptions.
        echo $eventtime . "\t" . $e->getMessage() . "\n";
        file_put_contents($dblog, $eventtime . "\t" . $e->getMessage() . "\n", FILE_APPEND);
    }
} else {
    //display vote data
}