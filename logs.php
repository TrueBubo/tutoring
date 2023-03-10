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
?>
    <div class="width50Center">
        <div class="rightAlign">
            <form method="post">
                <input type="text" name="search"
                       placeholder="<?= $langPack["Search"] ?>...">
                <button name="searchBtn" class="roundedCorners noBorder"
                        type="submit">🔎
                </button>
            </form>
        </div>
        <h1><?= $langPack["Logs"] ?></h1>
        <?php
        $db = connectDb();
        if (isset($_POST["searchBtn"])) {
            $query
                = "SELECT * FROM `LastLog` JOIN `Users` ON Users.UID = LastLog.TutorID WHERE UPPER(Users.name) LIKE '%{$_POST["search"]}%' ORDER BY lastLogTime DESC;";
            $statement = $db->prepare($query);
            $statement->execute();
            $result = $statement->get_result();
        } else {
            $query = "SELECT * FROM `LastLog` ORDER BY lastLogTime DESC";
            $statement = $db->prepare($query);
            $statement->execute();
            $result = $statement->get_result();
        }

        ?>
        <?php
        ?><div class="logsTable"><?php
        while ($row = $result->fetch_assoc()) {
            ?>
                <div>
                    <a href="seeTutorsLogs.php?id=<?= $row["TutorID"] ?>"
                       class="rightAlign buttonLink largeText noDecoration"><?= $langPack["See logs"] ?></a>
                    <h2 class="largeText"><?= TutoringSession::getTutorNameByID($row["TutorID"]) ?></h2>
            </div>
            <?php
        }
        ?> </div>

    </div>
<?php
