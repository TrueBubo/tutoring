<?php
require_once("header.php");
require_once("helperFunctions.php");
require_once('vendor/autoload.php');
require_once("classes/TutoringSession.php");

$langPack = getLanguagePack($_COOKIE["lang"]);
if (!(isset($_SESSION["isAdmin"]) and $_SESSION["isAdmin"])) {
    ?><strong
            class="hugeText"><?= $langPack['Access denied!'] ?></strong><?php
    exit();
}

$id = $_GET["id"];
$db = connectDb();
$query
    = "SELECT Logs.date, Logs.duration, Logs.description FROM Logs JOIN TutoringSessions on Logs.TutoringSessionID = TutoringSessions.SessionID WHERE TutoringSessions.TutorID = ? ORDER BY `Logs`.`timestamp` DESC;";
$statement = $db->prepare($query);
$statement->bind_param("s", $id);
$statement->execute();
$results = $statement->get_result();
?>
<table class="width50Center logsTable">
    <tr>
        <th><b><?= $langPack["Date"] ?></b></th>
        <th><b><?= $langPack["Duration"] ?></b></th>
        <th><b><?= $langPack["Description of the meeting"] ?></b></th>
    </tr>
    <?php
    while ($row = $results->fetch_assoc()) {
        ?>
        <tr>
            <th><?= $row["date"] ?></th>
            <th><?= $row["duration"] ?></th>
            <th><?= $row["description"] ?></th>
        </tr>
        <?php
    }

    ?>
</table>
