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

    public function open($savePath, $sessionName) : bool
    {
        return true;
    }

    public function close() : bool
    {
        return true;
    }

    public function read($id) : string|false
    {
        $sql = "SELECT SessionData FROM SessionData WHERE Sessionid = :id 
        AND SessionExpires > CURRENT_DATE();";
        $result = $this->pdo->runSQL($sql,['id' => $id])->fetchColumn();
        return $result == false ? "" : $result;
    }

    public function write($id, $sessionData) : bool
    {
        $sql = "REPLACE INTO SessionData 
        SET Sessionid = :id, SessionExpires = DATE_ADD(CURRENT_DATE(), INTERVAL 7 DAY), 
        SessionData = :SessionData;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('id', $id, \PDO::PARAM_INT);
        $stmt->bindParam('SessionData', $sessionData, \PDO::PARAM_STR); 
        $stmt->execute();
        return true;
    }

    public function destroy($id) : bool
    {
        $sql = "DELETE FROM SessionData WHERE Sessionid = :id";
        $this->pdo->runSQL($sql,['id' => $id]); 
        return true;
    }

    public function gc($maxlifetime) : int|false
    {
        // Cleanup old sessions, returning the number of deleted sessions on success, or false on failure
        $stmt = $this->pdo->runSQL("DELETE FROM SessionData WHERE SessionExpires < CURRENT_DATE();");
        return $stmt->rowCount();
    }
}