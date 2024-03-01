<?php
declare(strict_types=1);

namespace TennisApp;

class EventLog
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function write($message)
    {
        $now = date("Y-m-d H:i:s");
        $sql = "INSERT INTO EventLog (EventTime, EventMessage) 
        VALUES (:EventTime, :EventMessage);";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('EventTime', $now, \PDO::PARAM_STR); 
        $stmt->bindParam('EventMessage', $message, \PDO::PARAM_STR); 
        $stmt->execute();
    }
    
    public function list() : array
    {
        $sql = "SELECT * FROM EventLog ORDER BY EventTime DESC LIMIT 20;";
        $rows = $this->pdo->runSQL($sql)->fetchall(\PDO::FETCH_ASSOC);
        return $rows;
    }

}