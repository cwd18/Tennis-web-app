<?php
declare(strict_types=1);

namespace TennisApp;

class EventLog
{
    private $pdo;
    private $started = false;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->garbageCollect();
    }

    private function writeMessage($time, $message)
    {
        $now = date("Y-m-d H:i:s");
        $sql = "INSERT INTO EventLog (EventTime, EventMessage) 
        VALUES (:EventTime, :EventMessage);";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('EventTime', $now, \PDO::PARAM_STR); 
        $stmt->bindParam('EventMessage', $message, \PDO::PARAM_STR); 
        $stmt->execute();
    }
    
    private function garbageCollect()
    {
        $t = date("Y-m-d H:i:s", time() - 5 * 24 * 60 * 60);
        $sql = "DELETE FROM EventLog WHERE EventTime < :t;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('t', $t, \PDO::PARAM_STR); 
        $stmt->execute();
    }
    
    public function write($message)
    {
        $now = date("Y-m-d H:i:s");
        if ($this->started == false) {
            $this->started = true;
            $this->writeMessage($now, 'START LOG');
        }
        $this->writeMessage($now, $message);
    }
    
    public function list() : array
    {
        $sql = "SELECT * FROM (SELECT * FROM EventLog ORDER BY Seq DESC LIMIT 20) AS EventLog1 ORDER By seq;";
        $rows = $this->pdo->runSQL($sql)->fetchall(\PDO::FETCH_ASSOC);
        return $rows;
    }

}