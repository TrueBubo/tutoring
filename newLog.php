<?php
require_once("classes/TutoringSession.php");
require_once("header.php");
require_once("helperFunctions.php");

$db = connectDb();
$query = "SELECT TutorID, TuteeName FROM `TutoringSessions` WHERE SessionID=?";
$statement = $db->prepare($query);
$statement->bind_param("s", $_GET["id"]);
$statement->execute();
$row = $statement->get_result()->fetch_assoc();

$langPack = getLanguagePack($_COOKIE["lang"]);

if (isset($_POST["submit"])) {
    $query
        = "INSERT INTO `Logs` (TutoringSessionID, date, duration, description) VALUES (?, ?, ?, ?)";
    $statement = $db->prepare($query);
    $statement->bind_param("ssss", $_GET["id"], $_POST["date"],
        $_POST["duration"], $_POST["description"]);
    $statement->execute();

    // Updates when the tutor last put a log
    $query = "INSERT INTO LastLog (TutorID)
VALUES (?) ON DUPLICATE KEY UPDATE lastLogTime = CURRENT_TIMESTAMP";
    $statement = $db->prepare($query);
    $statement->bind_param("s", $row["TutorID"]);
    $statement->execute();

    header("Location: index.php");
}

if ($row["TutorID"] != $_SESSION["user"]) {
    ?><strong
            class="hugeText"><?= $langPack['Access denied!'] ?></strong><?php
    exit();
}

?>

    <div class='centeredScreen centerText'>
        <h1><?= $row["TuteeName"] ?></h1>
        <form method='post'>
            <label for='date'><?= $langPack["Date"] ?>:
                <input type='date' name='date' required><br>
            </label><br>
            <label for='duration'><?= $langPack["Duration"] ?>:
                <input style="width: 10em" type='text' name='duration' required>
            </label><br>
            <label for="description"><?= $langPack["Description of the meeting"] ?>
                :</label><br>
            <textarea name="description" rows="4" cols="30" required></textarea>
            <br>

            <br>
            <input class='btn' type='submit' name='submit'
                   value='<?= $langPack["Send"] ?>'><br>
        </form>
    </div>

<?php

