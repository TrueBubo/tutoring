<?php
require("header.php");



// loads jsons with data
$langPack = getLanguagePack($_COOKIE["lang"]);
$contactInfo = getContactInfo();
$coordinator = $contactInfo["Coordinator"][0];
$helpers = $contactInfo["Helpers"];

// Changes JSON with new contact info
if (isset($_POST["submit"])) {
    // Changes coordinator info
    $contactInfo["Coordinator"][0]["name"] = $_POST["nameCoord"];
    $contactInfo["Coordinator"][0]["email"] = $_POST["emailCoord"];

    $db = connectDb();
    // Changes helper info
    for ($i = 0; $i < 5; $i++) {
        if (isset($_POST["nameHelper".$i])) {
            $contactInfo["Helpers"][$i]["name"] = $_POST["nameHelper".$i];
            $contactInfo["Helpers"][$i]["email"] = $_POST["emailHelper".$i];


            // Sets admin priviledges in the database
            $query = "UPDATE `Users` SET `isAdmin` = '1' WHERE email=?";
            $statement = $db->prepare($query);
            $statement->bind_param("s", $contactInfo["Helpers"][$i]["email"]);
            $statement->execute();

        }
    }

    // Overwrites contactInfo.json with new data
    file_put_contents("configFiles/contactInfo.json",
                      json_encode($contactInfo));
    header("Refresh:0");
}


// Shown only for people with admin privileges
if (isset($_SESSION["isAdmin"]) and $_SESSION["isAdmin"]) {
    ?>

    <div>
        <form method='post'>

            <!--Input fields for changing tutoring coordinator-->
            <h2><?= $langPack["Tutoring Coordinator"] ?>:</h2>
            <div class='contactInfoInput'>
                <label for='nameCoord'><?= $langPack['Name'] ?>: </label<br>
                <input type='text' name='nameCoord'
                       value='<?= $coordinator["name"] ?>'><br>

                <label for='emailCoord'>e-mail: </label<br>
                <input type='text' name='emailCoord'
                       value='<?= $coordinator["email"] ?>'><br>
            </div>


            <!--Input fields for changing helpers-->
            <h2><?= $langPack["Helpers"] ?>:</h2>
            <?php for ($i = 0; $i < 5; $i++) { ?>
                <div class='contactInfoInput'>
                    <label for='<?= "nameHelper{$i}" ?>'><?= $langPack['Name'] ?>
                        : </label><br>
                    <input type='text' name='<?= "nameHelper".$i ?>'
                           value='<?= $helpers[$i]['name'] ?>'><br>

                    <label for='<?= "emailHelper".$i ?>'>e-mail: </label><br>
                    <input type='text' name='<?= "emailHelper".$i ?>'
                           value='<?= $helpers[$i]['email'] ?>'
                           pattern='^[a-z0-9]([\.a-z0-9])+@[a-z0-9]+(\.[a-z]{2,})+$' ><br>
                </div>
                <br>
            <?php } ?>

            <input type='submit' name='submit' value='<?= $langPack["Done"] ?>'><br>
        </form>
    </div>
    <?php
} else { ?>
    <strong style='font-size: 3em'><?= $langPack['Access denied!'] ?></strong>
<?php }

?>