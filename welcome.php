<?php
require_once("header.php");
require_once("helperFunctions.php");
$langPack = getLanguagePack($_COOKIE["lang"]);
?>

<div class='centeredScreen'>
    <p id='wannaBeText'><?= $langPack['I want to be a'] ?></p>
    <div id='tutorTypeButtons'>
        <a href='form.php?type=tutor'
           class='tutorType buttonLink'><?= $langPack['Tutor'] ?></a>
        <a href='form.php?type=tutee'
           class='tutorType buttonLink'><?= $langPack['Tutee'] ?></a
    </div>
</div>
