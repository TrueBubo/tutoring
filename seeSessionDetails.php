<?php
require_once("classes/TutoringSession.php");
require_once("header.php");
require_once("helperFunctions.php");

$langPack = getLanguagePack($_COOKIE["lang"]);

$db = connectDb();
$query = "SELECT * FROM `TutoringSessions` WHERE SessionID=?";
$statement = $db->prepare($query);
$statement->bind_param("s", $_GET["id"]);
$statement->execute();
$row = $statement->get_result()->fetch_assoc();

if ($row["TutorID"] != $_SESSION["user"]) {
    ?><strong
            class="hugeText"><?= $langPack['Access denied!'] ?></strong><?php
    exit();
} ?>

<div>
    <h1> <?= $langPack["Details"] ?> </h1>

    <h2> <?= $langPack["Tutee name"] ?> </h2>
    <p> <?= $row["TuteeName"] ?> </p>

    <h2> <?= $langPack["Tutee email"] ?> </h2>
    <a href='mailto:<?= $row["TuteeEmail"] ?>'><?= $row["TuteeEmail"] ?></a>


    <h2> <?= $langPack["Subjects"] ?> </h2>
    <?php
    $subjects = json_decode($row["subjects"]);
    foreach ($subjects as $subject) {
        $subject = (isset($langPack[$subject]))
            ? $langPack[$subject] : $subject;
        ?> <p><?= $subject ?></p> <?php
    } ?>
</div>