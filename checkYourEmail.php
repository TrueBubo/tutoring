<?php
require_once("header.php");
$langPack = getLanguagePack($_COOKIE["lang"]);
?>
<h1 class="centeredScreen centerText hugeText">
    <?= $langPack["CheckYourEmail"] ?>
</h1>