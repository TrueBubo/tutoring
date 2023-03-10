<head>
    <title>Tutoring</title>
    <link href="styles/styles.css" rel="stylesheet" type="text/css">
</head>
<?php
require_once("header.php");
require_once("helperFunctions.php");

if (!isset($_SESSION["user"])) {
    require_once("welcome.php");
} else {
    require_once("sessionScreenTutor.php");
}

?>

