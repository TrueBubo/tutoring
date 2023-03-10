<head>
    <title>Tutoring</title>
    <link href="styles/styles.css" rel="stylesheet" type="text/css">
</head>

<?php
require_once("header.php");
require_once("helperFunctions.php");

// loads jsons with data
$langPack = getLanguagePack($_COOKIE["lang"]);
$contactInfo = getContactInfo();

$coordinator = $contactInfo["Coordinator"][0];
$helpers = $contactInfo["Helpers"]
?>
<div id="contactPage">

    <div>
        <h1>
            <?= $langPack["Contact"] ?>
        </h1>

        <!-- prints coordinator-->
        <h2><?= $langPack["Tutoring Coordinator"] ?>:</h2>
        <h3><?= $coordinator["name"] ?></h3>
        <a href='mailto:<?= $coordinator["email"] ?>'><?= $coordinator["email"] ?></a>

        <!-- prints helpers-->
        <h2><?= $langPack["Helpers"] ?>:</h2>
        <?php foreach ($helpers as $helper) { ?>
            <h3><?= $helper["name"] ?></h3>
            <a href='mailto:<?= $helper["email"] ?>'><?= $helper["email"] ?></a>
            <br>
        <?php } ?>

        <!-- Button for changing contact info-->
        <?php if (isset($_SESSION["isAdmin"]) and $_SESSION["isAdmin"]) { ?>
            <br><a class='buttonLink roundedCorners'
                   href='changeContacts.php'><?= $langPack["Change contact info"] ?></a>
        <?php } ?>
    </div>


</div>