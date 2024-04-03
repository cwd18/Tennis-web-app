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
        $this->garbageCollect(); // delete old log entries
    }

    private function writeMessage(string $time, string $message)
    {
        $sql = "INSERT INTO EventLog (EventTime, EventMessage) 
        VALUES (:EventTime, :EventMessage);";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('EventTime', $time, \PDO::PARAM_STR); 
        $stmt->bindParam('EventMessage', $message, \PDO::PARAM_STR); 
        $stmt->execute();
    }
    
    private function garbageCollect()
    {
        // Garbage collect old log entries
        // We are not worried about the time zone here - UTC is good enough
        $t = date('Y-m-d H:i:s', strtotime('-1 week'));
        $sql = "DELETE FROM EventLog WHERE EventTime < :t;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('t', $t, \PDO::PARAM_STR); 
        $stmt->execute();
    }
    
    public function write(string $message)
    {
        // Write a message to the log
        // Prepend a 'START LOG' message if this is the first message since the EventLog object was created
        // Use the current time in London, including BST when applicable
        $now = date("Y-m-d H:i:s");
        $now = new \DateTime();
        $now->setTimezone(new \DateTimeZone('Europe/London'));
        $nowStr = $now->format("Y-m-d H:i:s");
        if ($this->started == false) {
            $this->started = true;
            $this->writeMessage($nowStr, 'START LOG');
        }
        $this->writeMessage($nowStr, $message);
    }
    
    public function list() : array
    {
        // Get the most recent 20 log entries, ascending by sequence number
        $sql = "SELECT * FROM (SELECT * FROM EventLog ORDER BY Seq DESC LIMIT 20) AS EventLog1 ORDER By seq;";
        $rows = $this->pdo->runSQL($sql)->fetchall(\PDO::FETCH_ASSOC);
        return $rows;
    }

}