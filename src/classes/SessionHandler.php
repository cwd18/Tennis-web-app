<?php
// This provides a session handler that stores session data in a database
// It implements the \SessionHandlerInterface, which is a standard PHP interface
// for session handling
// It enables a serverless deployment (Google App Engine)
// So that server-side session data persists between server invocations

declare(strict_types=1);

namespace TennisApp;

class SessionHandler implements \SessionHandlerInterface
{
    private $pdo;
    private $log;

    public function __construct($pdo, $log)
    {
        $this->pdo = $pdo;
        $this->log = $log;
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
        $sql = "SELECT SessionData FROM SessionData WHERE Sessionid = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchColumn();
        if ($result === false) {
            $this->log->logRead($id, "Session not found");
            return "";
        } else {
            $this->log->logRead($id, $result);
        }
        return $result == false ? "" : $result;
    }

    public function write($id, $sessionData): bool
    {
        $dateTime = new \DateTime();
        $dateTime->setTimezone(new \DateTimeZone('Europe/London'));
        $dateTime->modify('+1 week');
        $sessionExpires = $dateTime->format("Y-m-d H:i:s");
        $sql = "REPLACE INTO SessionData 
        SET Sessionid = :id, SessionExpires = :Expires, SessionData = :SessionData;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('id', $id, \PDO::PARAM_INT);
        $stmt->bindParam('Expires', $sessionExpires, \PDO::PARAM_STR);
        $stmt->bindParam('SessionData', $sessionData, \PDO::PARAM_STR);
        $stmt->execute();
        $this->log->logWrite($id, $sessionData);
        return true;
    }

    public function destroy($id): bool
    {
        $sql = "DELETE FROM SessionData WHERE Sessionid = :id";
        $this->pdo->runSQL($sql, ['id' => $id]);
        $this->log->logDestroy($id, "Session destroyed");
        return true;
    }

    public function gc($maxlifetime): int|false
    {
        // Cleanup old sessions, returning the number of deleted sessions on success, or false on failure
        $dateTime = new \DateTime();
        $dateTime->setTimezone(new \DateTimeZone('Europe/London'));
        $nowDateTime = $dateTime->format("Y-m-d H:i:s");
        $stmt = $this->pdo->prepare("DELETE FROM SessionData WHERE SessionExpires < :nowDT;");
        $stmt->bindParam('nowDT', $nowDateTime, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
