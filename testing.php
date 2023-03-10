<?php
//    include "vendor/php-ds/php-ds/";

//namespace Ds;
//use LogInSystem;

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once('vendor/autoload.php');
include "helperFunctions.php";
include_once "classes/TutoringSession.php";
//$numbers = new Set([1, 2, 3]);
////
////
////echo "Hi";
////echo $numbers->sum();
////echo "<br>";
////
////$numbers->diff(new Set([2, 3, 4]))
////->union(new Set([3, 4, 5]));
////
////print_r($numbers);

//TutoringSession::combineTutorsSessionByTuteeMail(54);
//echo "Test";
//print_r(json_decode(file_get_contents("configFiles/websiteInfo.json"), true));
//TutoringSession::informAboutSessionByEmail("bubak.filip@protonmail.com", "proton", "bubak.f@gjh.sk", "GJH", new \Ds\Set(["Math"]), "tutor", array("Monday" => array("0:00", "15:00")));
//echo "Sent";
//$lang = substr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 0,
//    2); // Gets user's preferred language
//echo $lang;
//$var = ($lang == "sk") ? "sk" : "en";
//echo $var;
?>