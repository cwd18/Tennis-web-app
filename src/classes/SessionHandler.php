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
        $nowDt = date('Y-m-d H:i:s');
        $sql = "SELECT SessionData FROM SessionData WHERE Sessionid = :id AND SessionExpires > :nowDt;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('id', $id, \PDO::PARAM_INT);
        $stmt->bindParam('nowDt', $nowDt, \PDO::PARAM_STR); 
        $stmt->execute();
        $result = $stmt->fetchColumn();
        return $result == false ? "" : $result;
    }

    public function write($id, $sessionData) : bool
    {
        $nowDt = date('Y-m-d H:i:s');
        $expiresDt = date('Y-m-d H:i:s',strtotime($nowDt . ' + 8 hours'));
        $sql = "REPLACE INTO SessionData 
        SET Sessionid = :id, SessionExpires = :expiresDt, SessionData = :SessionData;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('id', $id, \PDO::PARAM_INT);
        $stmt->bindParam('expiresDt', $expiresDt, \PDO::PARAM_STR); 
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
        $this->pdo->runSQL("DELETE FROM SessionData WHERE UNIX_TIMESTAMP(SessionExpires) < UNIX_TIMESTAMP();");
        return $this->pdo->rowCount();
    }


}