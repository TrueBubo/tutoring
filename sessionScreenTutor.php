<?php
require_once("classes/TutoringSession.php");
require_once("header.php");
require_once("helperFunctions.php");

$langPack = getLanguagePack($_COOKIE["lang"]);

TutoringSession::combineTutorsSessionByTuteeMail($_SESSION["user"]); // Combines them, so we show all the subject a tutor teaches a tutee in one place
$tutorsSession = TutoringSession::getTutorsSessions($_SESSION["user"]);
if ($tutorsSession->num_rows == 0) {
    require_once("welcome.php");
}
while ($row = $tutorsSession->fetch_assoc()) {
    ?>
    <div class="infoCard roundedCorners centeredHorizontalDiv">
        <b><?= $row["TuteeName"] ?></b><br>
        <br>
        <form method='post'>
        <a class="buttonLink whiteHover border noDecoration"
           href="newLog.php?id=<?= $row['SessionID'] ?>"><?= $langPack["New log"] ?></a>
        <a class="buttonLink whiteHover border noDecoration"
           href="seeSessionDetails.php?id=<?= $row['SessionID'] ?>"><?= $langPack["See details"] ?></a>
            <button class="buttonLink whiteHover border noDecoration" type="submit" name="terminateSession"
                    value="<?= $row["SessionID"] ?>"
                    onclick="return confirm('<?= $langPack["Are you sure?"] ?>')"><?= $langPack["Terminate session"] ?></button>
        </form>
        </label>
    </div>
    <?php
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sessionTerminated = $_POST['terminateSession'];
    $db = connectDb();
    $query = "DELETE FROM `TutoringSessions` WHERE SessionID=?";
    $statement = $db->prepare($query);
    $statement->bind_param("s", $sessionTerminated);
    $statement->execute();
    header("Refresh:0");
}