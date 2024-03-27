<?php
declare(strict_types=1);

namespace TennisApp;

class Users
{
    private $pdo;

    public function __construct(Database $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllUsers() : array
    {
        $sql = "SELECT Userid, FirstName, LastName, EmailAddress, ShortName, Booker
        FROM Users ORDER BY ShortName;";
        $statement = $this->pdo->runSQL($sql);
        $users = $statement->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function addUser(string $fname, string $lname, string $email) : int
    {
        $sql = "INSERT INTO Users (FirstName, LastName, EmailAddress)
        VALUES (:FirstName, :LastName, :EmailAddress);";
        $this->pdo->runSQL($sql, ['FirstName' => $fname, 'LastName' => $lname, 'EmailAddress' => $email]);
        $userId = (int)$this->pdo->lastInsertId();
        $this->generateShortNames();
        return $userId;
    }

    public function getUserData(int $userId) : array
    {
        $sql = "SELECT Userid, FirstName, LastName, EmailAddress, ShortName, Booker 
        FROM Users WHERE Userid = :Userid;";
        $statement = $this->pdo->runSQL($sql,['Userid' => $userId]);
        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        return $row;
    }

    public function deleteUser(int $userId)
    {
        $sql = "DELETE FROM Users WHERE Userid = :Userid;";
        $this->pdo->runSQL($sql,['Userid' => $userId]);
    }

    public function updateUser(int $userId, string $fname, string $lname, string $email, bool $booker)
    {
        $sql = "UPDATE Users 
        SET FirstName = :FirstName, LastName = :LastName, EmailAddress = :EmailAddress, Booker = :Booker
        WHERE Userid = :Userid;";
        $this->pdo->runSQL($sql, ['Userid' => $userId, 
        'FirstName' => $fname, 'LastName' => $lname, 'EmailAddress' => $email, 'Booker' => $booker]);
        $this->generateShortNames();
    }

    private function generateShortNames() : bool
    {
        // Generate unique ShortName for each user, adding as few letter from LastName as necessary
        // Assumes this will be enough to create a unique ShortName
        // Returns TRUE if all ShortNames are unique, FALSE if not

        // Set ShortName to FirstName for each user
        $this->pdo->runSQL("UPDATE Users SET ShortName = FirstName;");

        // Add as many letters from LastName to eliminate duplicate ShortNames
        $i = 1; // start with one letter from LastName
        do {
            $sql = "UPDATE Users,
            (SELECT UserId, C FROM Users,
            (SELECT ShortName, COUNT(*) AS C FROM Users GROUP BY ShortName) AS T1
            WHERE Users.ShortName = T1.ShortName) AS T2
            SET ShortName = CONCAT(FirstName, ' ', LEFT(LastName, $i))
            WHERE Users.Userid = T2.Userid AND C > 1;";
            $stmt = $this->pdo->runSQL($sql);
            $rowsUpdated = $stmt->rowCount();
        } while ($rowsUpdated > 0 and $i++ <= 3); // add up to 3 letters from LastName
        return $rowsUpdated == 0;
    }

}
