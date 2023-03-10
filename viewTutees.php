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

// Tutees with special requests
$query = "SELECT * FROM `SpecialRequests` WHERE type='tutee'";
$statement = $db->prepare($query);
$statement->execute();
$tutees = $statement->get_result();
?> <h1 class="centerText"><?= $langPack["Tutees"] ?></h1><?php
while ($row = $tutees->fetch_array()) {
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

// Ordinary tutees
$query = "SELECT * FROM `TuteesAvailable`";
$statement = $db->prepare($query);
$statement->execute();
$tutees = $statement->get_result();
while ($row = $tutees->fetch_array()) {
    $SessionID = $row["TuteeSessionID"];
    $email = $row["email"];
    $name = $row["name"];
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
