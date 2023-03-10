<?php

use Ds\Set;

require_once("header.php");
require_once("helperFunctions.php");
require_once('vendor/autoload.php');
require_once("classes/TutoringSession.php");

$langPack = getLanguagePack($_COOKIE["lang"]);
if (!(isset($_SESSION["isAdmin"]) and $_SESSION["isAdmin"])) {
    ?><strong class="hugeText"><?= $langPack['Access denied!'] ?></strong><?php
    exit();
}


$db = connectDb();

?>
<form method="post">
    <h1 class="centerText"><?= $langPack["Tutors"] ?></h1>
    <h2 class="centerText"><?= $langPack["ViewTutees"] ?></h2>
<?php
// Loads data from database
$query = "SELECT * FROM `SpecialRequests` WHERE type='tutor'";
$statement = $db->prepare($query);
$statement->execute();
$tutors = $statement->get_result();
$tutorsSessionID = array();
$sessionIDTutorID = array(); // key => value sessionID => TutorID
$requestIDToSubject = array();
while ($row = $tutors->fetch_array()) {
    $tutorSessionID = $row["RequestID"];
    array_push($tutorsSessionID, $tutorSessionID);
    $sessionIDTutorID[$tutorSessionID] = $row["TutorID"];
    $requestIDToSubject[$tutorSessionID] = $row["subject"];
    ?>

    <div class="infoCard roundedCorners centeredHorizontalDiv">
        <b><?= $langPack["Name"] ?>:</b> <?= $row["name"] ?><br>
        <b>Email:</b> <?= $row["email"] ?><br>
        <b><?= $langPack["Subject"] ?>:</b> <?= $row["subject"] ?>
        <?php
        if ($row["specialNote"] != "") {
            echo "<br><b>{$langPack["Special request"]}:</b> {$row["specialNote"]}";
        }
        ?>
        <br>
        <br>
        <label for='tuteeFor<?= $tutorSessionID ?>'><b><?= $langPack["Associate with a tutee (enter ID)"] ?></b>
            <input type='text' name='tuteeFor<?= $tutorSessionID ?>'>
        </label>
    </div>

    <?php
}
?>
    <h1 class="centerText"><?= $langPack["Tutees"] ?></h1>
    <h2 class="centerText"><?= $langPack["ViewTutors"] ?></h2>

<?php
$query = "SELECT * FROM `SpecialRequests` WHERE type='tutee'";
$statement = $db->prepare($query);
$statement->execute();
$tutees = $statement->get_result();
$tuteesSessionID = array();
while ($row = $tutees->fetch_array()) {
    $tuteeSessionID = $row["RequestID"];
    array_push($tuteesSessionID, $tuteeSessionID);
    $requestIDToSubject[$tuteeSessionID] = $row["subject"];
    ?>
    <div class="infoCard roundedCorners centeredHorizontalDiv">
        <b><?= $langPack["Name"] ?>:</b> <?= $row["name"] ?><br>
        <b>Email:</b> <?= $row["email"] ?><br>
        <b><?= $langPack["Subject"] ?>:</b> <?= $row["subject"] ?>
        <?php
        if ($row["specialNote"] != "") {
            echo "<br><b>{$langPack["Special request"]}:</b> {$row["specialNote"]}";
        }
        ?>
        <br>
        <br>
        <label for='tutorFor<?= $tuteeSessionID ?>'><b><?= $langPack["Associate with a tutor (enter ID)"] ?></b>
            <input type='text' name='tutorFor<?= $tuteeSessionID ?>'>
        </label>
    </div>

    <?php
}
?>

    <button type="submit" name='submit'
            class="btn centeredWidth btnCenter"><?= $langPack["Done"] ?></button>
</form>
<?php
if (isset($_POST["submit"])) {
    $removeTheseTutors = new Set();
    // Assign Tutors we filled
    foreach ($tutorsSessionID as $tutorSessionID) {
        $partnerSessionID = $_POST["tuteeFor{$tutorSessionID}"];
        if ($partnerSessionID == "") continue;  // Is not filled

        $tutorID = $sessionIDTutorID[$tutorSessionID];
        $thisEmail
            = TutoringSession::getTutorEmailByID($tutorID);
        $thisName
            = TutoringSession::getTutorNameByID($tutorID);

        // Partner with special request
        if ($partnerSessionID[0] == 'S' or $partnerSessionID[0] == 's') {
            $query = "SELECT * FROM `SpecialRequests` WHERE RequestID=?";
            $statement = $db->prepare($query);
            $id = substr($partnerSessionID, 1);
            $statement->bind_param("s", $id);
            $statement->execute();
            $result = $statement->get_result()->fetch_assoc();
            $tuteeMail = $result["email"];
            $tuteeName = $result["name"];

            $query
                = "DELETE FROM `SpecialRequests` WHERE RequestID IN (?, ?)";
            $statement = $db->prepare($query);
            $statement->bind_param("ss", $id, $tutorSessionID);
            $statement->execute();


            $subject = new Set([$requestIDToSubject[$tutorSessionID]]);
            TutoringSession::informAboutSessionByEmail($thisEmail,
                $thisName,
                $tuteeMail,
                $tuteeName,
                $subject, "tutor");
            TutoringSession::createTutoringSessionEntryInDb($tutorID, $tuteeMail, $tuteeName, $subject);

        } else {
            $query = "SELECT * FROM `TuteesAvailable` WHERE TuteeSessionID=?";
            $statement = $db->prepare($query);
            $statement->bind_param("s", $partnerSessionID);
            $statement->execute();
            $result = $statement->get_result()->fetch_assoc();
            $tuteeMail = $result["email"];
            $tuteeName = $result["name"];
            $subject = $result["subject"];

            $query
                = "DELETE FROM `TuteesAvailable` WHERE email = ? AND subject = ?";
            $statement = $db->prepare($query);
            $statement->bind_param("ss", $tuteeMail, $subject);
            $statement->execute();

            $query = "DELETE FROM `SpecialRequests` WHERE RequestID = ?";
            $statement = $db->prepare($query);
            $statement->bind_param("s", $tutorSessionID);
            $statement->execute();

            $subject = new Set([$requestIDToSubject[$tutorSessionID]]);
            TutoringSession::informAboutSessionByEmail($thisEmail,
                $thisName,
                $tuteeMail,
                $tuteeName,
                $subject, "tutor");
            TutoringSession::createTutoringSessionEntryInDb($tutorID, $tuteeMail, $tuteeName, $subject);
        }




    }

    // Assigns Tutees we filled
    foreach ($tuteesSessionID as $tuteeSessionID) {
        $partnerSessionID = $_POST["tutorFor{$tuteeSessionID}"];
        if ($partnerSessionID == "") continue;  // Is not filled

        // Set tutee info
        $query = "SELECT * FROM `SpecialRequests` WHERE RequestID=?";
        $statement = $db->prepare($query);
        $id = $tuteeSessionID;
        $statement->bind_param("s", $id);
        $statement->execute();
        $result = $statement->get_result()->fetch_assoc();
        $tuteeMail = $result["email"];
        $tuteeName = $result["name"];

        // Partner with special request
        if ($partnerSessionID[0] == 'S' or $partnerSessionID[0] == 's') {
            // Set tutor info
            $query = "SELECT * FROM `SpecialRequests` WHERE RequestID=?";
            $statement = $db->prepare($query);
            $id = substr($partnerSessionID, 1);
            $statement->bind_param("s", $id);
            $statement->execute();
            $result = $statement->get_result()->fetch_assoc();
            $tutorEmail = $result["email"];
            $tutorName = $result["name"];
            $tutorID = $result["TutorID"];

            $query
                = "DELETE FROM `SpecialRequests` WHERE RequestID IN (?, ?)";
            $statement = $db->prepare($query);
            $statement->bind_param("ss", $id, $tuteeSessionID);
            $statement->execute();

            $subject = new Set([$requestIDToSubject[$tutorSessionID]]);
            TutoringSession::informAboutSessionByEmail($tuteeMail,
                $tuteeName,
                $tutorEmail,
                $tutorName,
                $subject, "tutee");
            TutoringSession::createTutoringSessionEntryInDb($tutorID, $tuteeMail, $tuteeName, $subject);
        } else {
            $query = "SELECT * FROM `TutorsAvailable` WHERE TutorSessionID=?";
            $statement = $db->prepare($query);
            $statement->bind_param("s", $partnerSessionID);
            $statement->execute();
            $result = $statement->get_result()->fetch_assoc();
            $tutorID = $result["TutorID"];

            $query = "SELECT * FROM `Users` WHERE UID=?";
            $statement = $db->prepare($query);
            $statement->bind_param("s", $tutorID);
            $statement->execute();
            $result = $statement->get_result()->fetch_assoc();
            $tutorName = $result["name"];
            $tutorEmail = $result["email"];

            $query = "DELETE FROM `SpecialRequests` WHERE RequestID = ?";
            $statement = $db->prepare($query);
            $statement->bind_param("s", $tuteeSessionID);
            $statement->execute();

            $subject = new Set([$requestIDToSubject[$tuteeSessionID]]);
            TutoringSession::informAboutSessionByEmail($tuteeMail,
                $tuteeName,
                $tutorEmail,
                $tutorName,
                $subject, "tutee");
            TutoringSession::createTutoringSessionEntryInDb($tutorID, $tuteeMail, $tuteeName, $subject);
        }

        $query = "UPDATE `Users` SET howManyAdditionHoursFree = howManyAdditionHoursFree - 1 WHERE UID = ?;";
        $statement = $db->prepare($query);
        $statement->bind_param("s", $tutorID);
        $statement->execute();

        // Remove all subjects tutor offers
        if (TutoringSession::getTutorFreeHoursByID($tutorID) <= 0) {
            $removeTheseTutors->add($tutorID);
        }
    }

    foreach ($removeTheseTutors as $tutorID) {
        $query = "DELETE FROM `SpecialRequests` WHERE TutorID = ?";
        $statement = $db->prepare($query);
        $statement->bind_param("s", $tutorID);
        $statement->execute();

        $query = "DELETE FROM `TutorsAvailable` WHERE TutorID = ?";
        $statement = $db->prepare($query);
        $statement->bind_param("s", $tutorID);
        $statement->execute();
    }
    header("Refresh:0");
}