<?php
declare(strict_types=1);

namespace TennisApp;

class Users
{
    public $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllUsers() : array
    {
        $list = [];
        $sql = "SELECT Userid, FirstName, LastName FROM Users ORDER BY LastName;";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $users = $statement->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function addUser($fname, $lname, $email)
    {
        $sql = "INSERT INTO Users (FirstName, LastName, EmailAddress)
        VALUES ('$fname', '$lname', '$email');";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        return $this->pdo->lastInsertId();
    }

    public function getUser($userId) : array
    {
        $sql = "SELECT Userid, FirstName, LastName, EmailAddress FROM Users WHERE Userid=$userId;";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        return $row;
    }

    public function getUsers($userIds) : array
    {
        foreach ($userIds as $userId) {
            $sql = "SELECT Userid, FirstName, LastName, EmailAddress FROM Users WHERE Userid=$userId;";
            $statement = $this->pdo->prepare($sql);
            $statement->execute();
            $rows[] = $statement->fetch(\PDO::FETCH_ASSOC);
            }
        return $rows;
    }


    public function deleteUser($userid) : array
    {
        $row = $this->getUser($userid);
        if ( ! empty($row )) {
            $sql = "DELETE FROM Users WHERE Userid = $userid;";
            $statement = $this->pdo->prepare($sql);
            $statement->execute();
        }
        return $row;
    }

    public function updateUser($userid, $fname, $lname, $email) : array
    {
        $row = $this->getUser($userid);
        if ($fname != $row['FirstName'] or $lname != $row['LastName'] or $email != $row['EmailAddress']){
            $sql = "UPDATE Users SET FirstName='$fname', LastName='$lname', EmailAddress='$email'
            WHERE Userid=$userid;";
            $statement = $this->pdo->prepare($sql);
            $statement->execute();
        }
        return $row;
    }

}
