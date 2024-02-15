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
        // $expires   = date("Y-m-d H:i:s", strtotime('+4 hours'));
        $sql = "INSERT INTO Tokens (Token, Userid, TokenClass, Otherid, Expires)
            VALUES (:Token, :Userid, :TokenClass, :Otherid, :Expires);";
        $this->pdo->runSQL($sql,
        ['Userid' => $userId, 'Token' => $token, 
        'TokenClass' => $tokenClass, 'Otherid' => $otherId, 'Expires' => NULL]);
        return $token;
    }

    public function checkToken(string $token): mixed
    {
        $sql = "SELECT Userid, TokenClass, Otherid FROM Tokens WHERE Token = :Token
            AND (Expires > NOW() OR Expires IS NULL) ;";
        return $this->pdo->runSQL($sql, ['Token' => $token])->fetch(\PDO::FETCH_ASSOC);
    }

    public function getToken(int $userId, $tokenClass, $otherId): mixed
    {
        $sql = "SELECT Token FROM Tokens WHERE Userid = :Userid 
            AND TokenClass = :TokenClass AND Otherid = :Otherid
            AND (Expires > NOW() OR Expires IS NULL) ;";
        $stmt = $this->pdo->runSQL($sql, 
            ['Userid' => $userId, 'TokenClass' => $tokenClass, 'Otherid' => $otherId]);
        return $stmt->fetchColumn();
    }
}