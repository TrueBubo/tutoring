<?php
// Ensures typechecking is respected

declare(strict_types=1);
require("helperFunctions.php");

// Initiates a session
session_set_cookie_params(7200, "/aktivity-na-skole/tutoring-a-dobrovolnictvo");
session_start();
?>
<head>
    <title>Tutoring</title>
    <link href="styles/styles.css" rel="stylesheet" type="text/css">
</head>
<?php


// Initiates a language
if (!isset($_COOKIE["lang"])) {
    $lang = substr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 0,
        2); // Gets user's preferred language
    $_COOKIE["lang"] = ($lang == "sk") ? "sk" : "en";
    setcookie("lang", $_COOKIE["lang"], time() + 365 * 24 * 60 * 60,
        "/"); // Saves the cookie for one year
}

// Changes language after user clicks the flag
if (isset($_POST["languageChooser"])) {
    if ($_COOKIE["lang"] == "sk") {
        $_COOKIE["lang"] = "en";
    } else {
        $_COOKIE["lang"] = "sk";
    }
    setcookie("lang", $_COOKIE["lang"], time() + 365 * 24 * 60 * 60,
        "/"); // Saves the cookie for one year
}

$langPack = getLanguagePack($_COOKIE["lang"]);
$otherLangSymbol = ($_COOKIE["lang"] == "sk") ? "🇬🇧" : "🇸🇰";

?>
<div>
    <header>
        <nav class="topnav">


            <form method='post'>
                <input type='submit' name='languageChooser' id='languageChooser'
                       value='<?= $otherLangSymbol ?>'/>
            </form>

            <a class='buttonLink'
               href='index.php'><?= $langPack["Home"] ?></a>

            <a class='buttonLink'
               href='contact.php'><?= $langPack["Contact"] ?></a>

            <?php if (isset($_SESSION["isTutor"])
                and $_SESSION["isTutor"]
            ) { ?>
                <a class='buttonLink'
                   href='welcome.php'><?= $langPack["Create a new session"] ?></a>
            <?php } ?>

            <?php if (isset($_SESSION["isAdmin"])
                and $_SESSION["isAdmin"]
            ) { ?>
                <a class='buttonLink'
                   href='specialRequests.php'><?= $langPack["Special requests"] ?></a>
                <a class='buttonLink'
                   href='logs.php'><?= $langPack["Logs"] ?></a>
            <?php } ?>

            <?php if (!isset($_SESSION["user"])) { ?>
                <a class='buttonLink'
                   href='login.php'><?= $langPack["Log in"] ?></a>
            <?php } else { ?>
                <a class='buttonLink'
                   href='logout.php'><?= $langPack["Log out"] ?></a>
            <?php } ?>

        </nav>
    </header>
</div>
