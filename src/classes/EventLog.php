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
        $sql = "INSERT INTO EventLog (EventTime, EventMessage) 
        VALUES (CURRENT_TIMESTAMP(), :EventMessage);";
        $this->pdo->runSQL($sql, ['EventMessage' => $message]);
    }

}