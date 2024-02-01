<?php
declare(strict_types=1);

namespace TennisApp;

use TennisApp\Fixtures;
use TennisApp\Users;

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
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
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
        FROM Users, FixtureSeries WHERE Seriesid=$seriesId AND Users.Userid=FixtureSeries.SeriesOwner;";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
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
        FROM FixtureSeries WHERE Seriesid=$seriesId;";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        return $row;
    }

    public function addSeries($owner, $day, $time)
    {
        $sql = "INSERT INTO FixtureSeries (SeriesOwner, SeriesWeekday, SeriesTime) 
        VALUES ($owner, $day, '$time');";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $seriesId = $this->pdo->lastInsertId();
        return $seriesId;
    }

    public function updateBasicSeriesData($seriesId, $owner, $day, $time) : array
    {
        $row = $this->getBasicSeriesData($seriesId);
        if ($owner != $row['SeriesOwner'] or $day != $row['SeriesWeekday'] or $time != substr($row['SeriesTime'],0,5)) {
            $sql = "UPDATE FixtureSeries SET SeriesOwner='$owner', SeriesWeekday='$day', SeriesTime='$time'
            WHERE Seriesid=$seriesId;";
            $statement = $this->pdo->prepare($sql);
            $statement->execute();
        }
        return $row;
    }

    public function deleteSeries($seriesId)
    {
        $series = $this->getSeries($seriesId);
        // delete any fixtures
        $sql = "SELECT Fixtureid FROM Fixtures WHERE Seriesid=$seriesId;";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $fixtureIds = $statement->fetchall(\PDO::FETCH_ASSOC);
        $f = new Fixtures($this->pdo);
        foreach ($fixtureIds as $fixtureId) {
            $f->deleteFixture($fixtureId);
        }
        // Delete any candidates
        $sql = "DELETE FROM SeriesCandidates WHERE Seriesid=$seriesId;";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $sql = "DELETE FROM FixtureSeries WHERE Seriesid=$seriesId;";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        return $series;
    }

    public function getSeriesUsers($seriesId)
    {
        // Return list of users for this series 
        $sql = "SELECT Users.Userid, FirstName, LastName FROM Users, SeriesCandidates
        WHERE Seriesid=$seriesId AND Users.Userid=SeriesCandidates.Userid
        ORDER BY FirstName, LastName;";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $users = $statement->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }
    
    public function deleteSeriesUsers($seriesId, $userIds) : array
    {
        // Delete specified users from this series 
        $u = new Users($this->pdo);
        $users = $u->getUsers($userIds);
        foreach ($userIds as $userId) {
            $sql = "DELETE FROM SeriesCandidates WHERE Seriesid=$seriesId AND Userid=$userId;";
            $statement = $this->pdo->prepare($sql);
            $statement->execute();
            }
        return $users;
    }
    
    public function getSeriesCandidates($seriesId) : array
    {
        // Return list of possible candidate participants to add to the series, 
        // which excludes existing participants
        $sql = "SELECT Userid, FirstName, LastName FROM Users
        WHERE Users.Userid NOT IN (SELECT Userid FROM SeriesCandidates WHERE Seriesid=$seriesId)
        ORDER BY FirstName, LastName;";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $users = $statement->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function addUsers($seriesId, $userIds) : array
    {
        // Add users to the series
        foreach ($userIds as $userId) {
            $sql = "INSERT INTO SeriesCandidates (Seriesid, Userid) VALUES ($seriesId, $userId);";
            $statement = $this->pdo->prepare($sql);
            $statement->execute();
            }
        $u = new Users($this->pdo);
        $users = $u->getUsers($userIds);
        return $users;
    }
}
