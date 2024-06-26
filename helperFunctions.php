<?php
declare(strict_types=1);
require_once('vendor/autoload.php');
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

function getJSONData(string $filename): array {
    $json = file_get_contents($filename);
    $array = json_decode($json, true);

    return $array;
}

function getLanguagePack(string $languageCode): array {
    return getJSONData("lang.json")[$languageCode][0];
}

function getContactInfo(): array {
    return getJSONData("configFiles/contactInfo.json");
}

function getDatabaseLogin(): array {
    return getJSONData(getWebsiteInfo()["Tutoring"][0]["dbPasswordFile"])["login"][0]; // Change file location to match your filesystem
}

// Returns tutoring database
function connectDb(): mysqli {
    $databaseLogin = getDatabaseLogin();

    return connect($databaseLogin["servername"],
                   $databaseLogin["username"],
                   $databaseLogin["password"],
                   $databaseLogin["dbname"]);
}

function getWebsiteInfo(): array {
    return getJSONData("configFiles/websiteInfo.json");
}

function connect(string $host, string $usr, string $pass, string $db): mysqli {
    $connect = new mysqli($host, $usr, $pass, $db);
    if ($connect->error) {
        print("Connection error");
        die();
    }

    return $connect;
}

function isValidEmail(string $email): bool {
    return ((filter_var($email, FILTER_VALIDATE_EMAIL)) !== false);
}

function sendMail(string $to, string $subject, string $message): void {
    mail($to, $subject, $message, "From:tutoring@gjh.sk");
}

function generateRecoveryLink(string $email, string $token): string {
    $rootLink
        = getWebsiteInfo()["Tutoring"][0]["websiteWWW"]; // Where the index.php file is located on the web

    return "{$rootLink}resetPassword.php?email={$email}&token={$token}";
}

// Converts html time to SQL time
function timeToSQLTime(string $time): string {
    return $time.":00";
}

function timeIntervalByDay(array $data, string $day): array {
    return array(
        timeToSQLTime($data["{$day}From"]), timeToSQLTime($data["{$day}To"]),
    );
}

function  SQLTimeToMinutesSinceMidnight(string $SQLTime): int {
    $timeArray = explode(":", $SQLTime);
    return (int) $timeArray[0] * 60 + (int) $timeArray[1];
}

function numOverlappingMinutes(array $timeIntervalA, array $timeIntervalB): int { // Input arrays [startingSQLTime, endingSQLTime]
    $startTimeA = SQLTimeToMinutesSinceMidnight($timeIntervalA[0]);
    $endTimeA = SQLTimeToMinutesSinceMidnight($timeIntervalA[1]);
    $startTimeB = SQLTimeToMinutesSinceMidnight($timeIntervalB[0]);
    $endTimeB = SQLTimeToMinutesSinceMidnight($timeIntervalB[1]);
    if ($endTimeA < $startTimeA) {
        // Available at midnight and later
        $endTimeA += 1440;
    }
    if ($endTimeB < $startTimeB) {
        $endTimeB += 1440;
    }
    $intersectingTimeStart = max($startTimeA, $startTimeB);
    $intersectingTimeEnd = min($endTimeA, $endTimeB);
    return $intersectingTimeEnd - $intersectingTimeStart;
}

function minutesToTime($minutes): string {
    $hours = (int) ($minutes / 60);
    $minutes = $minutes % 60;
    if ($minutes < 10) $minutes = "0" . $minutes;
    return $hours . ":" . $minutes;
}

function overlappingTime(array $timeIntervalA, array $timeIntervalB): array { // Input arrays [startingSQLTime, endingSQLTime]
    $startTimeA = SQLTimeToMinutesSinceMidnight($timeIntervalA[0]);
    $endTimeA = SQLTimeToMinutesSinceMidnight($timeIntervalA[1]);
    $startTimeB = SQLTimeToMinutesSinceMidnight($timeIntervalB[0]);
    $endTimeB = SQLTimeToMinutesSinceMidnight($timeIntervalB[1]);
    if ($endTimeA < $startTimeA) {
        // Available at midnight and later
        $endTimeA += 1440;
    }
    if ($endTimeB < $startTimeB) {
        $endTimeB += 1440;
    }
    $intersectingTimeStart = max($startTimeA, $startTimeB);
    $intersectingTimeEnd = min($endTimeA, $endTimeB);
    return array(minutesToTime($intersectingTimeStart), minutesToTime($intersectingTimeEnd));
}

function customErrorHandler($errLevel, $errStr, $errFile, $errline): void { // Handles php messages when not defined how to treat them
    global $logger;
    switch ($errLevel) {
        case E_WARNING or E_USER_WARNING:
            $logger->warning("$errStr in $errFile:$errline");
            break;
        case E_NOTICE or E_USER_NOTICE:
            $logger->notice("$errStr in $errFile:$errline");
            break;
        case E_USER_ERROR:
            $logger->error("$errStr in $errFile:$errline");
            break;
        default:
            $logger->error("$errStr in $errFile:$errline");
            break;
    }
}

function customShutdownFunction(): void { // Logs fatal error
    global $logger;
    $error = error_get_last();
    if ($error !== null && $error['type'] === E_ERROR) {
        $logger->error("Fatal error: {$error['message']} in {$error['file']}:{$error['line']}");
    }
}

function getLogger(): Logger { // Return default logger
    $logger = new Logger('Logger');
    $handler = new StreamHandler('logFile.log', Logger::DEBUG);
    $logger->pushHandler($handler);
    return $logger;
}
?>