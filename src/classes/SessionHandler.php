<?php

declare(strict_types=1);

namespace TennisApp;

class SessionHandler implements \SessionHandlerInterface
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($id): string
    {
        $nowDateTime = date('Y-m-d H:i:s');
        $sql = "SELECT SessionData FROM SessionData WHERE Sessionid = :id 
        AND SessionExpires > :nowDT;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('id', $id, \PDO::PARAM_INT);
        $stmt->bindParam('nowDT', $nowDateTime, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchColumn();
        return $result == false ? "" : $result;
    }

    public function write($id, $sessionData): bool
    {
        $sessionExpires = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30); // 4 days from now
        $sql = "REPLACE INTO SessionData 
        SET Sessionid = :id, SessionExpires = :Expires, SessionData = :SessionData;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('id', $id, \PDO::PARAM_INT);
        $stmt->bindParam('Expires', $sessionExpires, \PDO::PARAM_STR);
        $stmt->bindParam('SessionData', $sessionData, \PDO::PARAM_STR);
        $stmt->execute();
        return true;
    }

    public function destroy($id): bool
    {
        $sql = "DELETE FROM SessionData WHERE Sessionid = :id";
        $this->pdo->runSQL($sql, ['id' => $id]);
        return true;
    }

    public function gc($maxlifetime): int|false
    {
        // Cleanup old sessions, returning the number of deleted sessions on success, or false on failure
        $nowDateTime = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare("DELETE FROM SessionData WHERE SessionExpires < :nowDT;");
        $stmt->bindParam('nowDT', $nowDateTime, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
