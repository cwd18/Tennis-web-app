<?php
declare(strict_types=1);

namespace TennisApp;

use TennisApp\Fixtures;

class Series
{
    public function __construct(protected $pdo)
    {
    }

    public function seriesDescription($weekday, $time)
    {
        $dayname = date('l', strtotime("Monday +$weekday days"));
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

    public function getSeries($seriesId) : array
    {
    // Retrieve basic series data...
    $sql = "SELECT FirstName, LastName, SeriesWeekday, SeriesTime 
    FROM Users, FixtureSeries WHERE Seriesid=$seriesId AND Users.Userid=FixtureSeries.SeriesOwner;";
    $statement = $this->pdo->prepare($sql);
    $statement->execute();
    $row = $statement->fetch(\PDO::FETCH_ASSOC);
    $description = $this->seriesDescription($row['SeriesWeekday'], $row['SeriesTime']);
    $ownerName = $row['FirstName']." ".$row['LastName'];
    // Get default fixture attendees...
    $sql = "SELECT FirstName, LastName FROM Users, SeriesCandidates
    WHERE Seriesid=$seriesId AND Users.Userid=SeriesCandidates.Userid
    ORDER BY FirstName, LastName;";
    $statement = $this->pdo->prepare($sql);
    $statement->execute();
    $rows = $statement->fetchall(\PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $ParticipantList[] = $row['FirstName']." ".$row['LastName'];
    }
    // Get recent fixtures...
    $fixtures = new Fixtures($this->pdo);
    $fixtureList = $fixtures->getRecentFixtures($seriesId);

    // return all series data
    $series = ['description' => $description, 'owner' => $ownerName, 
    'participants' => $ParticipantList, 'fixtures' => $fixtureList];
    return $series;
    }

    public function getBasicSeriesData($seriesId)
    {
    $sql = "SELECT Seriesid, SeriesOwner, SeriesWeekday, SeriesTime
    FROM FixtureSeries WHERE Seriesid=$seriesId;";
    $statement = $this->pdo->prepare($sql);
    $statement->execute();
    $row = $statement->fetch(\PDO::FETCH_ASSOC);
    return $row;
    }
}
