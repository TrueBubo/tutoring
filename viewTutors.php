<?php

require_once("header.php");
require_once("helperFunctions.php");
require_once("classes/TutoringSession.php");

$langPack = getLanguagePack($_COOKIE["lang"]);

if (!(isset($_SESSION["isAdmin"]) and $_SESSION["isAdmin"])) {
    ?><strong
            class="hugeText"><?= $langPack['Access denied!'] ?></strong><?php
    exit();
}

$db = connectDb();

// Tutors with special requests
$query = "SELECT * FROM `SpecialRequests` WHERE type='tutor'";
$statement = $db->prepare($query);
$statement->execute();
$tutors = $statement->get_result();
?> <h1 class="centerText"><?= $langPack["Tutors"] ?></h1><?php
while ($row = $tutors->fetch_array()) {
    $RequestID = $row["RequestID"];
    ?>
    <div class="infoCard roundedCorners centeredHorizontalDiv">
        <b>ID:</b> S<?= $RequestID ?><br>
        <b><?= $langPack["Name"] ?>:</b> <?= $row["name"] ?><br>
        <b>Email:</b> <?= $row["email"] ?><br>
        <?php $subject = (isset($langPack[$row["subject"]]))
            ? $langPack[$row["subject"]] : $row["subject"]; ?>
        <b><?= $langPack["Subject"] ?>:</b> <?= $subject ?>
        <?php
        if ($row["specialNote"] != "") {
            echo "<br><b>{$langPack["Special request"]}:</b> {$row["specialNote"]}";
        }
        ?>
    </div>
<?php }

// Ordinary tutors
$query = "SELECT * FROM `TutorsAvailable`";
$statement = $db->prepare($query);
$statement->execute();
$tutors = $statement->get_result();
while ($row = $tutors->fetch_array()) {
    $SessionID = $row["TutorSessionID"];
    $TutorID = $row["TutorID"];
    $name = TutoringSession::getTutorNameByID($TutorID);
    $email = TutoringSession::getTutorEmailByID($TutorID);
    ?>
    <div class="infoCard roundedCorners centeredHorizontalDiv">
        <b>ID:</b> <?= $SessionID ?><br>
        <b><?= $langPack["Name"] ?>:</b> <?= $name ?><br>
        <b>Email:</b> <?= $email ?><br>
        <?php $subject = (isset($langPack[$row["subject"]]))
            ? $langPack[$row["subject"]] : $row["subject"]; ?>
        <b><?= $langPack["Subject"] ?>:</b> <?= $subject ?>
    </div>
<?php }
?>

