<?php
declare(strict_types=1);

namespace TennisApp;

use TennisApp\Fixtures;

class Series
{
    public $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
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
        $statement = $this->pdo->runSQL($sql);
        $rows = $statement->fetchall(\PDO::FETCH_ASSOC);
        $series = (array) null;
        foreach ($rows as $row) {
            $description = $this->seriesDescription($row['SeriesWeekday'], $row['SeriesTime']);
            $series[] = ['seriesid' => $row['Seriesid'], 'description' => $description];
        }
        return $series;
    }

    public function getSeries($seriesId) : array
    {
        // Retrieve basic series data...
        $sql = "SELECT FirstName, LastName, SeriesWeekday, SeriesTime 
        FROM Users, FixtureSeries WHERE Seriesid = :Seriesid AND Users.Userid = FixtureSeries.SeriesOwner;";
        $statement = $this->pdo->runSQL($sql,['Seriesid' => $seriesId]);
        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        $description = $this->seriesDescription($row['SeriesWeekday'], $row['SeriesTime']);
        $ownerName = $row['FirstName']." ".$row['LastName'];
        
        // Get default fixture attendees...
        $users = $this->getSeriesUsers($seriesId);
        $ParticipantList = NULL;
        foreach ($users as $user) {
            $ParticipantList[] = $user['FirstName']." ".$user['LastName'];
        }

        // Get recent fixtures...
        $f = new Fixtures($this->pdo);
        $fixtureList = $f->getRecentFixtures($seriesId);

        // Count future fixtures?
        $futureFixtures = $f->futureFixtures($seriesId);

        // return all series data
        $series = ['seriesid' => $seriesId, 'description' => $description, 'owner' => $ownerName, 
        'participants' => $ParticipantList, 'fixtures' => $fixtureList, 'futurefixtures' => $futureFixtures];
        return $series;
    }

    public function getBasicSeriesData($seriesId) : array
    {
        $sql = "SELECT Seriesid, SeriesOwner, SeriesWeekday, SeriesTime
        FROM FixtureSeries WHERE Seriesid = :Seriesid;";
        $statement = $this->pdo->runSQL($sql,['Seriesid' => $seriesId]);
        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        return $row;
    }

    public function addSeries($owner, $day, $time)
    {
        $sql = "INSERT INTO FixtureSeries (SeriesOwner, SeriesWeekday, SeriesTime) 
        VALUES (:SeriesOwner, :SeriesWeekday, :SeriesTime);";
        $this->pdo->runSQL($sql,['SeriesOwner' => $owner, 'SeriesWeekday' => $day, 'SeriesTime' => "'$time'"]);
        $seriesId = $this->pdo->lastInsertId();
        return $seriesId;
    }

    public function updateBasicSeriesData($seriesId, $owner, $day, $time) : array
    {
        $row = $this->getBasicSeriesData($seriesId);
        if ($owner != $row['SeriesOwner'] or $day != $row['SeriesWeekday'] or $time != substr($row['SeriesTime'],0,5)) {
            $sql = "UPDATE FixtureSeries 
            SET SeriesOwner = :SeriesOwner, SeriesWeekday = :SeriesWeekday, SeriesTime = :SeriesTime
            WHERE Seriesid = :Seriesid;";
            $this->pdo->runSQL($sql,['SeriesOwner' => $owner, 'SeriesWeekday' => $day, 'SeriesTime' => "'$time'"]);
        }
        return $row;
    }

    public function deleteSeries($seriesId)
    {
        // Delete any fixtures
        $sql = "SELECT Fixtureid FROM Fixtures WHERE Seriesid = :Seriesid;";
        $statement = $this->pdo->runSQL($sql,['Seriesid' => $seriesId]);
        $fixtureIds = $statement->fetchall(\PDO::FETCH_ASSOC);
        $f = new Fixtures($this->pdo);
        foreach ($fixtureIds as $fixtureId) {
            $f->deleteFixture($fixtureId);
        }
        // Delete any candidates
        $sql = "DELETE FROM SeriesCandidates WHERE Seriesid = :Seriesid;";
        $this->pdo->runSQL($sql,['Seriesid' => $seriesId]);
        // Delete the series
        $sql = "DELETE FROM FixtureSeries WHERE Seriesid = :Seriesid;";
        $this->pdo->runSQL($sql,['Seriesid' => $seriesId]);
    }

    public function getSeriesUsers($seriesId)
    {
        // Return list of users for this series 
        $sql = "SELECT Users.Userid, FirstName, LastName FROM Users, SeriesCandidates
        WHERE Seriesid = :Seriesid AND Users.Userid = SeriesCandidates.Userid
        ORDER BY FirstName, LastName;";
        $statement = $this->pdo->runSQL($sql,['Seriesid' => $seriesId]);
        $users = $statement->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }
    
    public function deleteSeriesUsers($seriesId, $userIds)
    {
        // Delete specified users from this series 
        foreach ($userIds as $userId) {
            $sql = "DELETE FROM SeriesCandidates WHERE Seriesid = :Seriesid AND Userid = :Userid;";
            $this->pdo->runSQL($sql,['Seriesid' => $seriesId, 'Userid' => $userId]);
        }
    }

    public function getSeriesCandidates($seriesId) : array
    {
        // Return list of possible candidate participants to add to the series, 
        // which excludes existing participants
        $sql = "SELECT Userid, FirstName, LastName FROM Users
        WHERE Users.Userid NOT IN (SELECT Userid FROM SeriesCandidates WHERE Seriesid = :Seriesid)
        ORDER BY FirstName, LastName;";
        $statement = $this->pdo->runSQL($sql,['Seriesid' => $seriesId]);
        $users = $statement->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function addUsers($seriesId, $userIds)
    {
        // Add users to the series
        foreach ($userIds as $userId) {
            $sql = "INSERT INTO SeriesCandidates (Seriesid, Userid) VALUES ($seriesId, $userId);";
            $this->pdo->runSQL($sql,['Seriesid' => $seriesId, 'Userid' => $userId]);
        }
    }
}
