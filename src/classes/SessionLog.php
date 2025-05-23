<?php

declare(strict_types=1);

namespace TennisApp;

class SessionLog
{
    private $pdo;
    private $enabled;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $sql = "SELECT FeatureEnabled FROM FeatureFlags WHERE FeatureName = 'SessionLog';";
        $stmt = $this->pdo->runSQL($sql);
        $this->enabled = $stmt->fetchColumn();
    }

    private function log($id, $logType, $msg): void
    {
        if (!$this->enabled) {
            return;
        }
        // Log a session operation
        $now = new \DateTime();
        $now->setTimezone(new \DateTimeZone('Europe/London'));
        $nowDateTime = $now->format("Y-m-d H:i:s.u");
        $sql = "INSERT INTO SessionLog (Sessionid, LogTime, LogType, LogMessage)
        VALUES (:id, :nowDT, :logType, :msg);";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('id', $id, \PDO::PARAM_INT);
        $stmt->bindParam('nowDT', $nowDateTime, \PDO::PARAM_STR);
        $stmt->bindParam('logType', $logType, \PDO::PARAM_STR);
        $stmt->bindParam('msg', $msg, \PDO::PARAM_STR);
        $stmt->execute();
    }
    public function logRead($id, $msg): void
    {
        // Log a read operation
        $this->log($id, 'Read', $msg);
    }
    public function logWrite($id, $msg): void
    {
        // Log a write operation
        $this->log($id, 'Write', $msg);
    }
    public function logDestroy($id, $msg): void
    {
        // Log a destroy operation
        $this->log($id, 'Destroy', $msg);
    }
}
