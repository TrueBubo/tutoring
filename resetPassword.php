<?php
require_once("header.php");
require_once("helperFunctions.php");
require_once("classes/LogInSystem.php");

// Verifies whether to send a recovery email, and sends it
if (isset($_POST["submit"])) {
    $langPack = getLanguagePack($_COOKIE["lang"]);
    $login = new LogInSystem($_POST["email"], "");

    if ($login->isInDatabase("email", $_POST["email"])) {
        $login->createRecovery();
        echo "<h1 class='centeredScreen centerText goodMessage roundedCorners'>{$langPack['Check your inbox']}</h1>";

    } else {
        echo "<h1 class='centerScreen centerText errorMessage roundedCorners'>{$langPack['Email not found']}</h1>";
    }
}

if (isset($_POST["passwordChange"])) {
    $login = new LogInSystem($_GET["email"], "");
    $login->changePassword($_POST['password'], $_GET['email']);
    header("Location: login.php");
}

$langPack = getLanguagePack($_COOKIE["lang"]);

if (isset($_GET['token']) and isset($_GET['email'])) {
    $login = new LogInSystem($_GET["email"], "");
    if ($login->isValidToken($_GET["email"], $_GET["token"])) { ?>
        <div class='centeredScreen centerText'>
        <h1 class='centerText'><?=$langPack['Enter a new password']?></h1>
        <form method='post'>
        <label for='password'><?=$langPack["Password"]?>: </label>
        <input type='password' name='password'><br>
        <input class='btn' type='submit' name='passwordChange' value='<?=$langPack["Change password"]?>'><br>
        </form>
        </div>
        <?php
    } else {
        ?>
        <div class='centeredScreen centerText'>
        <h1 class='centerText'><?=$langPack['Invalid token']?>, <?=$langPack['Permission denied']?></h1>
        </div>
    <?php }
} else { ?>
    <div class='centeredScreen centerText'>
    <h1 class='centerText'><?=$langPack['Enter your email']?></h1>
    <form method='post'>
    <label for='email'>e-mail: </label>
    <input type='text' name='email'><br>
    <input class='btn' type='submit' name='submit' value='<?=$langPack["Send recovery email"]?>'><br>
    </form>
    </div>
<?php }