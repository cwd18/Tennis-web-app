<?php
declare(strict_types=1);

namespace TennisApp;

class Users
{
    public function __construct(protected $pdo)
    {
    }

    public function getAllUsers() : array
    {
        $list = [];
        $sql = "SELECT Userid, FirstName, LastName FROM Users ORDER BY LastName;";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $list = $statement->fetchall(\PDO::FETCH_ASSOC);
        return $list;
    }

    public function addUser($fname, $lname, $email) : array
    {
        $sql = "INSERT INTO Users (FirstName, LastName, EmailAddress)
        VALUES ('$fname', '$lname', '$email')
        RETURNING Userid, FirstName, LastName, EmailAddress;";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        return $row;
    }

    public function getUser($userid) : array
    {
        $sql = "SELECT Userid, FirstName, LastName, EmailAddress FROM Users WHERE Userid=$userid;";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        return $row;
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
        $sql = "SELECT Userid, FirstName, LastName, EmailAddress FROM Users WHERE Userid=$userid;";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        if ($fname != $row['FirstName'] or $lname != $row['LastName'] or $email != $row['EmailAddress']){
            $sql = "UPDATE Users SET FirstName='$fname', LastName='$lname', EmailAddress='$email'
            WHERE Userid=$userid;";
            $statement = $this->pdo->prepare($sql);
            $statement->execute();
        }
        return $row;
    }

}
