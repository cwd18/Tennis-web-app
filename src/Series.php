<?php
declare(strict_types=1);

namespace TennisApp;

class Series
{
    protected $dayName = ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"];

    public function __construct(protected $pdo)
    {
    }

    public function seriesDescription($weekday, $time)
    {
        $dayname = $this->dayName[$weekday];
        $hhmm = substr($time,0,5);
        $description = $dayname . ' at ' . $hhmm;
        return $description;
    }
    
    public function getAllSeries() : array
    {
        $sql = "SELECT Seriesid, SeriesWeekday, SeriesTime FROM FixtureSeries;";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $result = $statement->fetchall(\PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            $description = $this->seriesDescription($row['SeriesWeekday'], $row['SeriesTime']);
            $series[] = ['seriesid' => $row['Seriesid'], 'description' => $description];
        }
        return $series;
    }

    public function getSeries($seriesid) : array
    {
    // Retrieve basic series data...
    $sql = "SELECT FirstName, LastName, SeriesWeekday, SeriesTime 
    FROM Users, FixtureSeries WHERE Seriesid=$seriesid AND Users.Userid=FixtureSeries.SeriesOwner;";
    $statement = $this->pdo->prepare($sql);
    $statement->execute();
    $row = $statement->fetch(\PDO::FETCH_ASSOC);
    $description = $this->seriesDescription($row['SeriesWeekday'], $row['SeriesTime']);
    $ownerName = $row['FirstName']." ".$row['LastName'];
    $heading = ['description' => $description, 'owner' => $ownerName];
    return $heading;
    }
}
