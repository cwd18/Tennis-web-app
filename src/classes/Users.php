<?php
declare(strict_types=1);

namespace TennisApp;

class Users
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllUsers() : array
    {
        $sql = "SELECT Userid, FirstName, LastName, EmailAddress, ShortName 
        FROM Users ORDER BY ShortName;";
        $statement = $this->pdo->runSQL($sql);
        $users = $statement->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function getUsername($userId) : string
    {
        $sql = "SELECT FirstName, LastName FROM Users WHERE Userid = :Userid;";
        $statement = $this->pdo->runSQL($sql,['Userid' => $userId]);
        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        if ($row == FALSE)
            return "Unknown";
        return $row['FirstName'] . " " .$row['LastName'];
    }


    public function addUser($fname, $lname, $email)
    {
        $sql = "INSERT INTO Users (FirstName, LastName, EmailAddress)
        VALUES (:FirstName, :LastName, :EmailAddress);";
        $this->pdo->runSQL($sql, ['FirstName' => $fname, 'LastName' => $lname, 'EmailAddress' => $email]);
        $userId = $this->pdo->lastInsertId();
        $this->generateShortNames();
        return $userId;
    }

    public function getUser(int $userId) : array
    {
        $sql = "SELECT Userid, FirstName, LastName, EmailAddress, ShortName 
        FROM Users WHERE Userid = :Userid;";
        $statement = $this->pdo->runSQL($sql,['Userid' => $userId]);
        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        return $row;
    }

    public function getUsers($userIds) : array
    {
        $sql = "SELECT Userid, FirstName, LastName, EmailAddress FROM Users WHERE Userid = :Userid;";
        $stmt = $this->pdo->prepare($sql);
        foreach ($userIds as $userId) {
            $stmt->execute(['Userid' => $userId]);
            $rows[] = $stmt->fetch(\PDO::FETCH_ASSOC);
            }
        return $rows;
    }


    public function deleteUser($userId)
    {
        $sql = "DELETE FROM Users WHERE Userid = :Userid;";
        $this->pdo->runSQL($sql,['Userid' => $userId]);
    }

    public function updateUser($userId, $fname, $lname, $email)
    {
        $sql = "UPDATE Users 
        SET FirstName = :FirstName, LastName = :LastName, EmailAddress = :EmailAddress
        WHERE Userid = :Userid;";
        $this->pdo->runSQL($sql, ['Userid' => $userId, 
        'FirstName' => $fname, 'LastName' => $lname, 'EmailAddress' => $email]);
        $this->generateShortNames();
    }

    private function generateShortNames() 
    {
        // Generate unique ShortName for each user, adding letter from LastName if necessary
        // Set ShortName to FirstName for each user
        // Assumes this will be enough to create a unique ShortName
        $this->pdo->runSQL("UPDATE Users SET ShortName = FirstName;");
        // Add first letter from LastName to any duplicate ShortNames
        $sql = "UPDATE Users,
        (SELECT UserId, C FROM Users,
        (SELECT ShortName, COUNT(*) AS C FROM Users GROUP BY ShortName) AS T1
        WHERE Users.ShortName = T1.ShortName) AS T2
        SET ShortName = CONCAT(ShortName, ' ', LEFT(LastName, 1))
        WHERE Users.Userid = T2.Userid AND C > 1;";
        $this->pdo->runSQL($sql);
    }

}
