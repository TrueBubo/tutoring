<?php
declare(strict_types=1);
use Ds\Set;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once 'vendor/autoload.php';

class TutoringSession {
    private mysqli $db;
    private string $type;
    private string $email;
    private int $grade;
    private string $name;
    private array $subjects;
    private string $preferredTimeScale;
    private int $howOftenTutor;
    private array $timesAvailable;
    private string $specialNote;
    private Logger $logger;

    public function __construct(array $data, string $type) {
        $this->type = $type;
        $this->grade = (int) $data["grade"];

        $this->db = connectDb();

        $this->logger = getLogger();
        set_error_handler('customErrorHandler');
        register_shutdown_function('customShutdownFunction');


        if (isset($_SESSION["user"])) {
            $query
                = "SELECT name, email FROM `Users` WHERE UID={$_SESSION['user']}";
            $statement = $this->db->prepare($query);
            $statement->execute();
            $result = $statement->get_result()->fetch_assoc();
            $this->name = $result["name"];
            $this->email = $result["email"];
        } else {
            if ($type == "tutee") {
                $this->email = $data["email"];
                $this->name = $data["name"];
            }
        }
        if ($type == "tutor") {

            $this->howOftenTutor = (int) $data["howOftenTutor"];
        }
        if (isset($data["subjects"])) {
            $this->subjects = $data["subjects"];
        } else {
            $this->subjects = array();
        }
        $this->preferredTimeScale = $data["preferredTimeScale"];
        $this->timesAvailable = array();

        // Sets data a person is available
        $days = [
            "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday",
            "Sunday",
        ];
        foreach ($days as $day) {
            if ($data[lcfirst($day)."From"] != "" and $data[lcfirst($day)."To"]
                != ""
            ) {
                $this->timesAvailable[$day] = timeIntervalByDay($data,
                    lcfirst($day));
            }
        }

        $this->specialNote = $data["specialNote"];
        // All subjects need a special treatment (special note)
        if ($data["specialNote"] != "") {
            if ($data["otherSubjects"] != "") {
                array_push($this->subjects, $data["otherSubjects"]);
            }
            $this->createSpecialRequest($this->subjects);
        } else {
            if ($type == "tutor") {
                $this->createTutorOffer();
                $this->assignTutee();

            } elseif ($type == "tutee") {
                $this->createTuteeOffer();
                $this->assignTutor();
            }
        }
        /*
        Only for other subjects, so the system can assign normal subjects
        regularly if they do not have a special note
        */
        if ($data["specialNote"] == "" and $data["otherSubjects"] != "") {
            $this->createSpecialRequest([$data["otherSubjects"]]);
        }
    }

    // Sends mail when a partner is found
    public static function informAboutSessionByEmail(
        string $thisEmail,
        string $thisName,
        string $foundPersonEmail,
        string $foundPersonName,
        Ds\Set $subjects,
        string $type,
        array $timesInCommon = null,
        bool $reverseSend = false
    ): void {
        $langPack = getLanguagePack($_COOKIE["lang"]);
        if ($type == "tutee") {
            $subjectEmail = $langPack["FoundTutorEmailSubject"];
            $message = $langPack["FoundTutorEmailMessage"];
        } elseif ($type == "tutor") {
            $subjectEmail = $langPack["FoundTuteeEmailSubject"];
            $message = $langPack["FoundTuteeEmailMessage"];
        }

        $message .= " {$foundPersonName} {$foundPersonEmail}\n";
        $message .= "\n".$langPack["Subjects"].":\n";
        foreach ($subjects as $subject) {
            if (isset($langPack[$subject])) {
                $message .= $langPack[$subject]."\n";
            } else {
                $message .= $subject."\n";
            }
        }

        if ($timesInCommon != null) {
            $message .= "\n".$langPack["TimesInCommon"]."\n";
            foreach ($timesInCommon as $day => $values) {
                $message .= $langPack[$day].": "
                    ."{$values[0]} - {$values[1]}\n";
            }
        }

        if ($type == "tutor") {
            $message .= "\n".$langPack["LogRequest"];
        }

        if ($type == "tutor") {
            sendMail($thisEmail, $subjectEmail, $message);
            // Sends the email to the other person
            if (!$reverseSend) {
                TutoringSession::informAboutSessionByEmail($foundPersonEmail,
                    $foundPersonName, $thisEmail, $thisName, $subjects, "tutee",
                    $timesInCommon, true);
            }
        } elseif ($type == "tutee") {
            sendMail($thisEmail, $subjectEmail, $message);
            // Sends the email to the other person
            if (!$reverseSend) {
                TutoringSession::informAboutSessionByEmail($foundPersonEmail,
                    $foundPersonName, $thisEmail, $thisName, $subjects, "tutor",
                    $timesInCommon, true);
            }
        }
    }

    // Gets session tutor teaches
    public static function getTutorsSessions(int $TutorID): bool|mysqli_result {
        $db = connectDb();
        $query = "SELECT * FROM `TutoringSessions` WHERE TutorID=?";
        $statement = $db->prepare($query);
        $statement->bind_param("s", $TutorID);
        $statement->execute();
        $result = $statement->get_result();

        return $result;
    }

    public static function getTuteeNameByEmail(string $email): string {
        $db = connectDb();
        $query = "SELECT name FROM `TuteesAvailable` WHERE email=?";
        $statement = $db->prepare($query);
        $statement->bind_param("s", $email);
        $statement->execute();
        $result = $statement->get_result()->fetch_assoc();

        return $result["name"];
    }

    public static function getTutorIDbySessionID(int $ID) {
        $db = connectDb();
        $query = "SELECT TutorID FROM `TutoringSessions` WHERE SessionID=?";
        $statement = $db->prepare($query);
        $statement->bind_param("s", $ID);
        $statement->execute();
        $result = $statement->get_result()->fetch_assoc();

        return $result["TutorID"];
    }

    public static function getTutorNameByID(int $ID): string {
        $db = connectDb();
        $query = "SELECT name FROM `Users` WHERE UID=?";
        $statement = $db->prepare($query);
        $statement->bind_param("s", $ID);
        $statement->execute();
        $result = $statement->get_result()->fetch_assoc();

        return $result["name"];
    }

    public static function getTutorEmailByID(int $ID): string {
        $db = connectDb();
        $query = "SELECT email FROM `Users` WHERE UID=?";
        $statement = $db->prepare($query);
        $statement->bind_param("s", $ID);
        $statement->execute();
        $result = $statement->get_result()->fetch_assoc();

        return $result["email"];
    }

    public static function getTutorFreeHoursByID(int $ID): int {
        $db = connectDb();
        $query = "SELECT howManyAdditionHoursFree FROM `Users` WHERE UID=?";
        $statement = $db->prepare($query);
        $statement->bind_param("s", $ID);
        $statement->execute();
        $result = $statement->get_result()->fetch_assoc();

        return $result["howManyAdditionHoursFree"];
    }

    public static function createTutoringSessionEntryInDb(
        int $TutorID,
        string $TuteeEmail,
        string $TuteeName,
        Ds\Set $subjects
    ): void {
        $db = connectDb();
        $query
            = "INSERT INTO `TutoringSessions` (TutorID, TuteeEmail, TuteeName, subjects) VALUES (?, ?, ?, ?)";
        $statement = $db->prepare($query);
        $subjectsJSON = json_encode($subjects);
        $statement->bind_param("ssss", $TutorID, $TuteeEmail, $TuteeName,
            $subjectsJSON);
        $statement->execute();
    }

    // Tutor can have multiple session with one tutee, this combines them, so they are not all over the place
    public static function combineTutorsSessionByTuteeMail(int $TutorID): void {
        $db = connectDb();
        $tuteeEmailToSessionIDs = array(); // tuteeEmail => SessionID
        $duplicatePartner = new Set(); // duplicatePartner => min sessionID
        $tuteeEmailToSubjects = array(); // tuteeEmail => subjects
        $tutorsSession = self::getTutorsSessions($TutorID);

        while ($row = $tutorsSession->fetch_assoc()) {
            // Duplicate detected
            if (isset($tuteeEmailToSessionIDs[$row["TuteeEmail"]])) {
                $duplicatePartner->add($row["TuteeEmail"]);
                array_push($tuteeEmailToSessionIDs[$row["TuteeEmail"]],
                    $row["SessionID"]);
                array_push($tuteeEmailToSubjects[$row["TuteeEmail"]], ...
                    json_decode($row["subjects"]));
            } else { // Initializes variables
                $tuteeEmailToSessionIDs[$row["TuteeEmail"]]
                    = array($row["SessionID"]);
                $tuteeEmailToSubjects[$row["TuteeEmail"]]
                    = json_decode($row["subjects"]);
            }
        }

        // Delete unnecessary session, and put their subject to one row
        foreach ($duplicatePartner as $tuteeEmail) {
            $subjectsJSON = json_encode($tuteeEmailToSubjects[$tuteeEmail]);
            $tuteeSessionID = min($tuteeEmailToSessionIDs[$tuteeEmail]);

            // Update subject for the first session
            $query
                = "UPDATE `TutoringSessions` SET `subjects` = ? WHERE SessionID = ?";
            $statement = $db->prepare($query);
            $statement->bind_param("ss", $subjectsJSON, $tuteeSessionID);
            $statement->execute();

            // Delete remaining sessions
            $query
                = "DELETE FROM `TutoringSessions` WHERE TutorID=? AND TuteeEmail=? AND SessionID != ?";
            $statement = $db->prepare($query);
            $statement->bind_param("sss", $TutorID, $tuteeEmail,
                $tuteeSessionID);
            $statement->execute();
        }
    }

    public function createSpecialRequest(
        array $subjects
    ): void { // Sends special requests for admins to review
        $timeAvailableJson = json_encode($this->timesAvailable);
        // Puts the request to SpecialRequests table
        foreach ($subjects as $subject) {
            if ($this->type == "tutee") {
                $statement
                    = $this->db->prepare('INSERT INTO `SpecialRequests` (type, name, grade, subject, preferredTimeScale, timesAvailable, specialNote, email, tutorID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL)');
            } elseif ($this->type == "tutor") {
                $statement
                    = $this->db->prepare("INSERT INTO `SpecialRequests` (type, name, grade, subject, preferredTimeScale, timesAvailable, specialNote, email, tutorID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, {$_SESSION["user"]})");
            }
            $statement->bind_param('ssisssss', $this->type, $this->name,
                $this->grade, $subject,
                $this->preferredTimeScale, $timeAvailableJson,
                $this->specialNote, $this->email);
            $statement->execute();
        }
        $this->logger->info('Special request received');
    }

    public function createTutorOffer(
    ): void {  // Puts Tutor to database, and tries to find whether it is possible to match it with a tutee
        $timeAvailableJson = json_encode($this->timesAvailable);

        // Update time tutor wants to teach per week
        $query
            = "UPDATE `Users` SET `howManyAdditionHoursFree` = ? WHERE `UID` = ?";
        $statement = $this->db->prepare($query);
        $statement->bind_param("ss", $this->howOftenTutor, $_SESSION["user"]);
        $statement->execute();

        // Inserts each subject as a separate row
        foreach ($this->subjects as $subject) {
            // tries to insert tutor's subjects into the database
            $query
                = "INSERT IGNORE INTO `TutorsAvailable` (TutorID, subject, grade, preferredTimeScale, timesAvailable) VALUES (?, ?, ?, ?, ?)";
            $statement = $this->db->prepare($query);
            $statement->bind_param("sssss", $_SESSION["user"], $subject,
                $this->grade, $this->preferredTimeScale,
                $timeAvailableJson);
            $statement->execute();
        }
        $statement->reset();

    }

    public function createTuteeOffer(
    ): void { // Puts Tutee to database, and tries to find whether it is possible to match it with a tutor
        $timeAvailableJson = json_encode($this->timesAvailable);


        foreach ($this->subjects as $subject) {
            // tries to insert tutee's subjects into the database
            $query
                = "INSERT IGNORE INTO `TuteesAvailable` (name, email, subject, grade, preferredTimeScale, timesAvailable) VALUES (?, ?, ?, ?, ?, ?)";
            $statement = $this->db->prepare($query);
            $statement->bind_param("ssssss", $this->name, $this->email,
                $subject, $this->grade,
                $this->preferredTimeScale,
                $timeAvailableJson);
            $statement->execute();
        }
    }

    public function assignTutee(
    ): void { // Finds Tutees which a person filling the form can tutor

        // Selects tutees which have a subject in common
        $query
            = "SELECT * FROM `TuteesAvailable` WHERE preferredTimeScale=? and grade <= ? and subject in ('"
            .implode("','", array_map(array($this->db, 'real_escape_string'),
                $this->subjects))
            ."')";
        $statement = $this->db->prepare($query);
        $statement->bind_param("ss", $this->preferredTimeScale, $this->grade);
        $statement->execute();
        $result = $statement->get_result();

        // Finds tutors who have the same free time
        $potentialTutees = array();
        $potentialTuteesTimesInCommon = array();
        while ($row = $result->fetch_array()) {
            // Associate each tutee with their subjects
            if (isset($potentialTutees[$row["email"]])) {
                $potentialTutees[$row["email"]]->add($row["subject"]);
            } else {
                if ($this->canMeetRegularly($this->timesAvailable,
                    json_decode($row["timesAvailable"],
                        true))
                ) {
                    $potentialTutees[$row["email"]]
                        = new Set([$row["subject"]]);
                    $potentialTuteesTimesInCommon[$row["email"]]
                        = $this->whenCanMeet($this->timesAvailable,
                        json_decode($row["timesAvailable"],
                            true));
                }
            }
        }
        $potentialTutees = $this->findTutoringPartners($potentialTutees);

        // Selects tutees so tutor won't teach above their capacities
        $freeHoursTutor = (int) $this->howOftenTutor;
        $selectedTutees = array();
        foreach ($potentialTutees as $id => $subjects) {
            if (count($subjects) > $freeHoursTutor) {
                $selectedTutees[$id] = $subjects->slice(0, $freeHoursTutor);
                $freeHoursTutor = 0;
                break;
            } else {
                $selectedTutees[$id] = $subjects;
                $freeHoursTutor -= count($subjects);
            }
        }

        // Puts every session in the database
        foreach ($selectedTutees as $email => $subjects) {
            $tuteeName = self::getTuteeNameByEmail($email);
            $this->markSessionInDb($_SESSION["user"], $email, $tuteeName,
                $subjects);
            // Sends email
            self::informAboutSessionByEmail($this->email, $this->name, $email,
                $tuteeName, $subjects, "tutor",
                $potentialTuteesTimesInCommon[$email]);
        }
    }

    public function assignTutor(
    ): void { // Finds Tutors which a person filling the form can be a tutee of
        // Selects tutors which have a subject in common
        $query
            = "SELECT * FROM `TutorsAvailable` WHERE preferredTimeScale=? and grade >= ? and subject in ('"
            .implode("','", array_map(array($this->db, 'real_escape_string'),
                $this->subjects))
            ."')";
        $statement = $this->db->prepare($query);
        $statement->bind_param("ss", $this->preferredTimeScale, $this->grade);
        $statement->execute();
        $result = $statement->get_result();

        // Finds potential tutors
        $potentialTutors = array();
        $potentialTutorsTimesInCommon = array();
        while ($row = $result->fetch_array()) {
            // Associate each tutee with their subjects
            if (isset($potentialTutors[$row["TutorID"]])) {
                $potentialTutors[$row["TutorID"]]->add($row["subject"]);
            } else {
                if ($this->canMeetRegularly($this->timesAvailable,
                    json_decode($row["timesAvailable"],
                        true))
                ) {
                    $potentialTutors[$row["TutorID"]]
                        = new Set([$row["subject"]]);
                    $potentialTutorsTimesInCommon[$row["TutorID"]]
                        = $this->whenCanMeet($this->timesAvailable,
                        json_decode($row["timesAvailable"],
                            true));
                }
            }
        }
        $potentialTutors = $this->findTutoringPartners($potentialTutors);

        $selectedTutors = array();
        foreach ($potentialTutors as $id => $subjects) {
            $selectedTutors[$id] = $subjects;
        }


        // Puts every session in the database
        foreach ($selectedTutors as $id => $subjects) {
            $this->markSessionInDb($id, $this->email, $this->name, $subjects);
            // Sends email
            $tutorName = self::getTutorNameByID($id);
            $tutorMail = self::getTutorEmailByID($id);
            self::informAboutSessionByEmail($this->email, $this->name,
                $tutorMail, $tutorName, $subjects, "tutee",
                $potentialTutorsTimesInCommon[$id]);
        }


    }

    public function canMeetRegularly(
        array $freeTimeA,
        array $freeTimeB
    ): bool { // Determines whether two people can meet regularly for at least an hour
        $days = [
            "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday",
            "Sunday",
        ];
        foreach ($days as $day) {
            if (isset($freeTimeA[$day]) and isset($freeTimeB[$day])) {
                if (numOverlappingMinutes($freeTimeA[$day], $freeTimeB[$day])
                    >= 60
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    public function whenCanMeet(
        array $freeTimeA,
        array $freeTimeB
    ): array { // Outputs array with all times two people can meet for at least an hour
        $days = [
            "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday",
            "Sunday",
        ];
        $timesInCommon = array();
        foreach ($days as $day) {
            if (isset($freeTimeA[$day]) and isset($freeTimeB[$day])) {
                if (numOverlappingMinutes($freeTimeA[$day], $freeTimeB[$day])
                    >= 60
                ) {
                    $timesInCommon[$day] = overlappingTime($freeTimeA[$day],
                        $freeTimeB[$day]);
                }
            }
        }

        return $timesInCommon;
    }

    public function findTutoringPartners(array $potentialPartners
    ): array { // Return IDs (UID for tutor, email for tutees) of partners
        /* Finding partners is application of the set coverage problem as subjects
         represent element, and subjects we want, represent the universe. This
         method works on principle of greedy algorithm outlined here:
         https://www.geeksforgeeks.org/greedy-approximate-algorithm-for-set-cover-problem/
         */
        $remainingSubjectsToFind = new Set($this->subjects);
        $partnersFound = array();
        while (!$remainingSubjectsToFind->isEmpty()) {
            $mostNewSubjectsID = ""; // ID of a partner
            $maxNumNewSubject = 0; // How many new subjects does a person bring
            $newSubjectsCovered
                = new Set(); // Subjects new person selected covers
            foreach ($potentialPartners as $id => $subjects) {
                /* Checks whether this person can offer to be with the other person more often,
                   ensures person is with few people most of a time rather than with many people for one lesson */
                if (count($remainingSubjectsToFind->intersect($subjects))
                    > $maxNumNewSubject
                ) {
                    $mostNewSubjectsID = $id;
                    $maxNumNewSubject
                        = count($remainingSubjectsToFind->intersect($subjects))
                        > $maxNumNewSubject;
                    $newSubjectsCovered
                        = $remainingSubjectsToFind->intersect($subjects);
                }
            }
            if ($maxNumNewSubject == 0) {
                break;
            } // Could not find anyone to be a partner for remaining subjects
            $remainingSubjectsToFind
                = $remainingSubjectsToFind->diff($newSubjectsCovered);
            $partnersFound[$mostNewSubjectsID] = $newSubjectsCovered;
        }

        return $partnersFound;

    }

    public function setTutorFreeHoursByID(int $ID, int $value): void {
        $query = "UPDATE `Users` SET howManyAdditionHoursFree=? WHERE UID=?";
        $statement = $this->db->prepare($query);
        $statement->bind_param("ss", $value, $ID);
        $statement->execute();
    }

    public function deleteTutorsOffersByID(int $ID): void {
        $query = "DELETE FROM `TutorsAvailable` WHERE TutorID=?";
        $statement = $this->db->prepare($query);
        $statement->bind_param("s", $ID);
        $statement->execute();
    }

    public function deleteSelectedTuteesSubjects( // Used to delete subjects, when the tutee no longer seeks them, they found them
        string $TuteeEmail,
        Ds\Set $subjects
    ): void {
        $query
            = "DELETE FROM `TuteesAvailable` WHERE email=? and subject in ('"
            .implode("','", array_map(array($this->db, 'real_escape_string'),
                $subjects->toArray()))
            ."')";
        $statement = $this->db->prepare($query);
        $statement->bind_param("s", $TuteeEmail);
        $statement->execute();
    }

    public function markSessionInDb(
        int $TutorID,
        string $TuteeEmail,
        string $TuteeName,
        Ds\Set $subjects
    ): void {
        // Tutee no longer requires these subjects
        $this->deleteSelectedTuteesSubjects($TuteeEmail, $subjects);

        $newFreeHoursTutor = max([
            self::getTutorFreeHoursByID($TutorID)
            - count($subjects), 0,
        ]);
        // No free time, they won't offer to tutor someone
        if ($newFreeHoursTutor == 0) {
            $this->deleteTutorsOffersByID($TutorID);
        }
        $this->setTutorFreeHoursByID($TutorID, $newFreeHoursTutor);

        self::createTutoringSessionEntryInDb($TutorID, $TuteeEmail, $TuteeName,
            $subjects);

        $this->logger->info("Tutoring Session Created");
    }
}
