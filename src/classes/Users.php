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
        $sql = "SELECT Userid, FirstName, LastName FROM Users ORDER BY FirstName, LastName;";
        $statement = $this->pdo->runSQL($sql);
        $users = $statement->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function getUsername($userId) : string
    {
        $sql = "SELECT FirstName, LastName FROM Users WHERE Userid = :Userid;";
        $statement = $this->pdo->runSQL($sql,['Userid' => $userId]);
        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        return $row['FirstName'] . " " .$row['LastName'];
    }


    public function addUser($fname, $lname, $email)
    {
        $sql = "INSERT INTO Users (FirstName, LastName, EmailAddress)
        VALUES (:FirstName, :LastName, :EmailAddress);";
        $this->pdo->runSQL($sql, ['FirstName' => $fname, 'LastName' => $lname, 'EmailAddress' => $email]);
        return $this->pdo->lastInsertId();
    }

    public function getUser($userId) : array
    {
        $sql = "SELECT Userid, FirstName, LastName, EmailAddress FROM Users WHERE Userid = :Userid;";
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
    }

}
