<?php

declare(strict_types=1);

namespace TennisApp;

class Tokens
{
    protected $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getOrCreateToken(int $userId, string $tokenClass, int $otherId): string
    {
        $token = $this->getToken($userId, $tokenClass, $otherId);
        if (is_string($token)) {
            return $token; // token for this user already exists
        }
        $token = bin2hex(random_bytes(16));
        $dateNow = date("Y-m-d");
        $sql = "INSERT INTO Tokens (Token, Userid, TokenClass, Otherid, Created)
            VALUES (:Token, :Userid, :TokenClass, :Otherid, :DateNow);";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('Token', $token, \PDO::PARAM_STR);
        $stmt->bindParam('Userid', $userId, \PDO::PARAM_INT);
        $stmt->bindParam('TokenClass', $tokenClass, \PDO::PARAM_STR);
        $stmt->bindParam('Otherid', $otherId, \PDO::PARAM_INT);
        $stmt->bindParam('DateNow', $dateNow, \PDO::PARAM_STR);
        $stmt->execute();
        return $token;
    }

    public function checkToken(string $token): bool | array
    // Check if token exists 
    // Returns false if it does not
    // Returns userId, tokenClass, otherId as an associative array if it does
    {
        $sql = "SELECT Userid, TokenClass, Otherid FROM Tokens WHERE Token = :Token;";
        $stmt = $this->pdo->runSQL($sql, ['Token' => $token]);
        $r = $stmt->fetch(\PDO::FETCH_ASSOC); // returns row as associative array or false
        if ($r === false) {
            return false; // token not found
        }
        return $r;
    }


    private function getToken(int $userId, string $tokenClass, int $otherId): mixed
    {
        $sql = "SELECT Token FROM Tokens WHERE Userid = :Userid 
            AND TokenClass = :TokenClass AND Otherid = :Otherid;";
        $stmt = $this->pdo->runSQL(
            $sql,
            ['Userid' => $userId, 'TokenClass' => $tokenClass, 'Otherid' => $otherId]
        );
        return $stmt->fetchColumn();
    }
}
