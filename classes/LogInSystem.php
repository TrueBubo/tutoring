<?php
declare(strict_types=1);

class LogInSystem {
    private string $email;
    private string $plainTextPassword;
    private mysqli $db;

    public function __construct(string $email, string $plainTextPassword) {
        $this->email = $email;
        $this->plainTextPassword = $plainTextPassword;
        $this->db = connectDb();
    }

    // Logs user in
    public function login(): bool {
        if (!$this->verifyLogin()) {
            return false;
        }
        $query = "SELECT UID, isTutor, isAdmin FROM `Users` WHERE email=?";
        $statement = $this->db->prepare($query);
        $statement->bind_param("s", $this->email);
        $statement->execute();
        $result = $statement->get_result()->fetch_assoc();
        $_SESSION["user"] = $result["UID"];
        $_SESSION["isTutor"] = $result["isTutor"];
        $_SESSION["isAdmin"] = $result["isAdmin"];

        return true;
    }


    // Verifies whether password matches
    public function verifyLogin(): bool {
        $query = "SELECT password FROM `Users` WHERE email=?";
        $statement = $this->db->prepare($query);
        $statement->bind_param("s", $this->email);
        $statement->execute();
        $result = $statement->get_result();
        $hashedPassword = $result->fetch_assoc()["password"];
        if ($result->num_rows != 1) {
            return false;
        }
        if (password_verify($this->plainTextPassword, $hashedPassword)) {
            return true;
        } else {
            return false;
        }
    }


    // Puts account info into the database
    public function createAccount(string $name): bool {
        $langPack = getLanguagePack($_COOKIE["lang"]);
        if (!isValidEmail($this->email)) {
            ?>
            <p class="errorMessage"><?= $langPack["Invalid mail"] ?></p>
            <?php
            return false;
        }
        $hashedPassword = password_hash($this->plainTextPassword,
            PASSWORD_DEFAULT);
        $query = "INSERT INTO `Users` (`name`, `email`, `password`, `howManyAdditionHoursFree`, `isTutor`, `isAdmin`) VALUES(
        ?, ?, ?, null, '1', '0')";
        $statement = $this->db->prepare($query);
        $statement->bind_param("sss", $name, $this->email, $hashedPassword);
        if (!$statement->execute()) {
            return false;
        } else {
            return true;
        }
    }

    public function changePassword(string $newPassword, string $email): void { // Updates the password to a new one
        $query = "UPDATE `Users` SET `password` = ? WHERE `Users`.`email` = ?";
        $statement = $this->db->prepare($query);
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $statement->bind_param("ss", $hashedPassword, $email);
        $statement->execute();
    }


    public function isInDatabase(string $varName, string|int $varValue): bool {
        $query = "SELECT * FROM `Users` WHERE {$varName}=?";
        $statement = $this->db->prepare($query);
        $statement->bind_param("s", $varValue);
        $statement->execute();
        $result = $statement->get_result();
        if ($result->num_rows == 0) {
            return false;
        } else {
            return true;
        }
    }

    public function createRecovery(): void {
        $token = $this->generateToken();
        $this->sendRecoveryMail($this->email, $token);
        $this->insertTokenIntoDb($token);
    }

    // Generates token, which is used to reset passwords

    public function sendRecoveryMail(string $to, string $token): void {
        $langPack = getLanguagePack($_COOKIE["lang"]);
        $recoveryLink = generateRecoveryLink($to, $token);
        $message = "{$langPack["Reset message"]}

{$recoveryLink}";
        $subject = $langPack["Subject password reset"];
        mail($to, $subject, $message);
    }

    // Verifies whether we can use given info to reset the password
    public function isValidToken(string $email, string $token): bool {
        // Only select recent (in the last 30 minutes) tokens, older ones are invalid
        $query
            = "SELECT * FROM RecoveryTokens WHERE ((UNIX_TIMESTAMP() - `expiration`) < 0) AND email=? AND token=?";
        $statement = $this->db->prepare($query);
        $hashedToken = hash("sha256", $token);
        $statement->bind_param("ss", $email, $hashedToken);
        $statement->execute();
        $result = $statement->get_result();
        if ($result->num_rows == 1) {
            return true;
        }

        return false;

    }

    private function generateToken(): string { // One-time password for when user forgets their password
        $randomBytes = random_bytes(64);

        return bin2hex($randomBytes);
    }



    private function insertTokenIntoDb(string $token): void { // Sets a token for the user which expires in 30 minutes
        $hashedToken = hash("sha256", $token);
        $expiration = time() + 1800;
        $query
            = "INSERT INTO `RecoveryTokens` (token, email, expiration) VALUES ('{$hashedToken}', '{$this->email}', {$expiration});";
        $statement = $this->db->prepare($query);
        $statement->execute();
    }
}