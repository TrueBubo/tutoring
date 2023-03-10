<?php
require_once("header.php");
require_once("helperFunctions.php");
require_once("classes/LogInSystem.php");
require_once("classes/TutoringSession.php");
$langPack = getLanguagePack($_COOKIE["lang"]);


if (isset($_POST["submit"])) {
    $tutoringSession = new TutoringSession($_POST, $_GET["type"]);
    header("Location: checkYourEmail.php");
}

$type = $_GET["type"];
if (!($type == "tutor" or $type == "tutee")) {
    exit();
}

if ($type == "tutor" and !$_SESSION["user"]) {
    header("Location: login.php?redirect=form.php");
}
?>


<div class='centeredHorizontalDiv'>
    <h1><?= $langPack['Tutoring'] ?></h1>
    <form method='post'>
        <!-- Signed people have said their name during registration-->
        <?php if (!isset($_SESSION["user"])) { ?>
            <div class="formField">
                <label for='email'>e-mail:<span class="required">*</span><br>
                    <input type='text' name='email'
                           pattern='^[a-z0-9]([\.a-z0-9])+@[a-z0-9]+(\.[a-z]{2,})+$'
                           required>
                </label>
            </div>
        <?php } ?>

        <!-- Dropdown for class-->
        <div class="formField">
            <label for='grade'><?= $langPack["Class"] ?>:<span class="required">*</span><br>
                <select name='grade'>
                    <option value='9'>I.A</option>
                    <option value='10'>I.B</option>
                    <option value='10'>I.C</option>
                    <option value='10'>Kvinta A</option>
                    <option value='10'>II.A</option>
                    <option value='11'>II.B</option>
                    <option value='11'>II.C</option>
                    <option value='11'>Sexta A</option>
                    <option value='11'>III.A</option>
                    <option value='12'>III.B</option>
                    <option value='12'>III.C</option>
                    <option value='12'>Septima A</option>
                    <option value='13'>IV.A</option>
                    <option value='13'>IV.B</option>
                    <option value='13'>IV.C</option>
                    <option value='13'>Oktáva A</option>
                    <option value='13'>V.A</option>
                    <option value='6'>Príma A</option>
                    <option value='7'>Sekunda A</option>
                    <option value='8'>Tercia A</option>
                    <option value='9'>Kvarta A</option>
                    <option value='1'>I.PYP SJ</option>
                    <option value='2'>II.PYP SJ</option>
                    <option value='3'>III.PYP SJ</option>
                    <option value='4'>IV.PYP SJ</option>
                    <option value='5'>V.PYP SJ</option>
                    <option value='6'>VI.A</option>
                    <option value='7'>VII.A</option>
                    <option value='8'>VIII.A</option>
                    <option value='9'>IX.A</option>
                    <option value='1'>I.PYP AJ</option>
                    <option value='2'>II.PYP AJ</option>
                    <option value='3'>III.PYP AJ</option>
                    <option value='4'>IV.PYP AJ</option>
                    <option value='5'>V.PYP AJ</option>
                    <option value='6'>P.MYP</option>
                    <option value='7'>I.MYP</option>
                    <option value='8'>II.MYP</option>
                    <option value='9'>III.MYP</option>
                    <option value='10'>IV.MYP</option>
                    <option value='11'>V.MYP</option>
                    <option value='12'>III.IBDA</option>
                    <option value='12'>III.IBDB</option>
                    <option value='13'>IV.IBDA</option>
                    <option value='13'>IV.IBDB</option>
                </select>
            </label><br>
        </div>

        <!-- Signed people have said their name during registration-->
        <?php if (!isset($_SESSION["user"])) { ?>
            <div class="formField">
                <label for='name'><?= $langPack["Name"] ?>:<span
                            class="required">*</span><br>
                    <input type='text' name='name' required><br>
                </label>
            </div>

        <?php } ?>

        <!--Subject selection-->
        <div class="formField">
            <label style='width: 15em'> <?= $langPack['Subjects wanted'] ?>:<span class="required">*</span>
            </label><br>
            <?php
            $subjects = [
                "Math", "Slovak", "English", "German", "Spanish", "Physics",
                "Biology", "Chemistry", "Computer Science",
            ];
            foreach ($subjects as $subject) {
                ?>
                <input type="checkbox" name="subjects[]"
                       value="<?= $subject ?>">
                <label> <?= $langPack[$subject] ?></label><br>
            <?php } ?>
            <label for='other'> <?= $langPack['Other'] ?>
                <input type='text' name="otherSubjects" value=''>
            </label>

        </div>

        <div class="formField">
            <label style='width: 15em'> <?php
                if ($type == "tutor") {
                    echo $langPack["I am looking to help"];
                } else {
                    if ($type == 'tutee') {
                        echo $langPack["I am looking for help"];
                    }
                }
                ?>:<span class="required">*</span>
            </label>

            <br>
            <input type="radio" name="preferredTimeScale" value="longTerm"
                   required>
            <label> <?= $langPack["Long term"] ?></label><br>
            <input type="radio" name="preferredTimeScale" value="shortTerm"
                   required>
            <label> <?= $langPack["Short term"] ?></label><br>
        </div>
        <?php if ($type == "tutor") { ?>
            <div class="formField">
                <label> <?= $langPack["HowOftenTutor"] ?> <span
                            class="required">*</span><br>
                    <input type="number" min="1" max="7" name="howOftenTutor"
                           required>
                </label>
            </div>
        <?php } ?>

        <!--Selector for free time-->
        <div class="formField">
            <label> <?= $langPack["TimesAvailable"] ?><span
                        class="required">*</span> <br><br>
                <?php
                    $days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
                    foreach ($days as $day) {
                ?>

                    <label> <?= $langPack[$day] ?> <br>
                        <div class="fullWidth">
                            <?= $langPack["From"] ?> <input type="time"
                                                            name="<?= lcfirst($day) . 'From' ?>">
                            <?= $langPack["To"] ?>   <input type="time" name="<?= lcfirst($day) . 'To' ?>">
                        </div>
                    <label>
                <?php } ?>


            </label>
            <div class="formField">
                <label for='specialNote'><?= $langPack["SpecialNote"] ?> <br>
                    <input type='text' name='specialNote'>
                </label>
            </div>

        </div>

        <input class='btn' type='submit' name='submit'
               value='<?= $langPack["Send"] ?>' style="margin-top: -20em;"><br>
    </form>
</div>
