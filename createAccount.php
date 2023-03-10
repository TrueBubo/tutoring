<?php
require_once("header.php");
require_once("helperFunctions.php");
require_once("classes/LogInSystem.php");

// After the button is pressed, register the user
if (isset($_POST["submit"])) {
    $langPack = getLanguagePack($_COOKIE["lang"]);
    $login = new LogInSystem($_POST["email"], $_POST["password"]);
    try {
    if ($login->createAccount($_POST["name"])) {
        $login->login();
        header("Location: index.php");
    } else { ?>
        <p class="errorMessage"><?=$langPack["Failed"]?></p>
    <?php }
    } catch (Exception $e) {
        ?><p class="errorMessage"><?=$langPack["Failed"]?></p>
    <?php }
}

$langPack = getLanguagePack($_COOKIE["lang"]);
?>

<div class='centerScreen centerText'>
    <h1><?= $langPack['Create an account'] ?></h1>
    <form method='post'>

        <label for='name'><?= $langPack["Name"] ?>:</label>
        <input type='text' name='name' required><br>

        <label for='email'>e-mail: </label>
        <input type='text' name='email' pattern='^[a-z0-9]([\.a-z0-9])+@[a-z0-9]+(\.[a-z]{2,})+$' required><br>

        <label for='password'><?= $langPack["Password"] ?>: </label>
        <input type='password' name='password' required><br>

        <input class='btn' type='submit' name='submit'
               value='<?= $langPack["Sign up"] ?>'><br>
    </form>
</div>