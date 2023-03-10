<?php
require_once("header.php");
require_once("helperFunctions.php");
require_once("classes/LogInSystem.php");


// After the button is pressed, tries to log in the user
if (isset($_POST["submit"])) {
    $langPack = getLanguagePack($_COOKIE["lang"]);
    $login = new LogInSystem($_POST["email"], $_POST["password"]);
    if (!$login->login()) {
        ?> <p class="errorMessage"><?=$langPack["Wrong credentials"]?></p> <?php
    } else {
        if (isset($_GET["redirect"])) {
            if ($_GET["redirect"] == "form.php") {
                header("Location: form.php?type=tutor");
            }
        } else {
            header("Location: index.php");
        }
    }
}


$langPack = getLanguagePack($_COOKIE["lang"]);

// Log in screen
?>
<div class='centeredScreen centerText'>
    <h1><?= $langPack['Log in'] ?></h1>
    <form method='post'>
        <label for='email'>e-mail: </label>
        <input type='text' name='email' pattern='^[a-z0-9]([\.a-z0-9])+@[a-z0-9]+(\.[a-z]{2,})+$' required><br>
        <label for='password'><?= $langPack["Password"] ?>: </label>
        <input type='password' name='password' required><br>
        <a href='resetPassword.php'><?= $langPack["Forgot your password"] ?><br><br></a>
        <input class='btn' type='submit' name='submit'
               value='<?= $langPack["Log in"] ?>'><br>
    </form>
    <a href='createAccount.php'><?= $langPack["Create an account"] ?></a>
</div>
