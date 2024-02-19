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
        $sql = "SELECT Users.Userid, FirstName, LastName, SeriesWeekday, SeriesTime, SeriesCourts
        FROM Users, FixtureSeries WHERE Seriesid = :Seriesid AND Users.Userid = FixtureSeries.SeriesOwner;";
        $statement = $this->pdo->runSQL($sql,['Seriesid' => $seriesId]);
        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        $description = $this->seriesDescription($row['SeriesWeekday'], $row['SeriesTime']);
        $owner['Userid'] =  $row['Userid'];
        $owner['FirstName'] = $row['FirstName'];
        $owner['LastName'] = $row['LastName'];
        $seriesCourts = $row['SeriesCourts'];
        
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
        $series = ['seriesid' => $seriesId, 'description' => $description, 'owner' => $owner, 'courts' => $seriesCourts,
        'participants' => $ParticipantList, 'fixtures' => $fixtureList, 'futurefixtures' => $futureFixtures];
        return $series;
    }

    public function getBasicSeriesData($seriesId) : array
    {
        $sql = "SELECT Seriesid, SeriesOwner, SeriesWeekday, SeriesTime, SeriesCourts
        FROM FixtureSeries WHERE Seriesid = :Seriesid;";
        $statement = $this->pdo->runSQL($sql,['Seriesid' => $seriesId]);
        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        return $row;
    }

    public function getOwner($seriesId) : int
    {
        $sql = "SELECT SeriesOwner FROM FixtureSeries WHERE Seriesid = :Seriesid;";
        return $this->pdo->runSQL($sql,['Seriesid' => $seriesId])->fetchColumn();
    }

    public function addSeries($owner, $day, $time, $courts)
    {
        $sql = "INSERT INTO FixtureSeries (SeriesOwner, SeriesWeekday, SeriesTime, SeriesCourts) 
        VALUES (:SeriesOwner, :SeriesWeekday, :SeriesTime, :SeriesCourts);";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('SeriesOwner', $owner, \PDO::PARAM_INT);
        $stmt->bindParam('SeriesWeekday', $day, \PDO::PARAM_INT);
        $stmt->bindParam('SeriesTime', $time, \PDO::PARAM_STR); 
        $stmt->bindParam('SeriesCourts', $courts, \PDO::PARAM_STR); 
        $stmt->execute();
        $seriesId = $this->pdo->lastInsertId();
        return $seriesId;
    }

    public function updateBasicSeriesData($seriesId, $owner, $day, $time, $courts)
    {
        $sql = "UPDATE FixtureSeries 
        SET SeriesOwner = :SeriesOwner, SeriesWeekday = :SeriesWeekday, 
        SeriesTime = :SeriesTime, SeriesCourts = :SeriesCourts
        WHERE Seriesid = :Seriesid;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('Seriesid', $seriesId, \PDO::PARAM_INT);
        $stmt->bindParam('SeriesOwner', $owner, \PDO::PARAM_INT);
        $stmt->bindParam('SeriesWeekday', $day, \PDO::PARAM_INT);
        $stmt->bindParam('SeriesTime', $time, \PDO::PARAM_STR); 
        $stmt->bindParam('SeriesCourts', $courts, \PDO::PARAM_STR); 
        $stmt->execute();
    }

    public function deleteSeries($seriesId)
    {
        // Delete any fixtures
        $sql = "SELECT Fixtureid FROM Fixtures WHERE Seriesid = :Seriesid;";
        $statement = $this->pdo->runSQL($sql,['Seriesid' => $seriesId]);
        $fixtures = $statement->fetchall(\PDO::FETCH_ASSOC);
        $f = new Fixtures($this->pdo);
        foreach ($fixtures as $fixture) {
            $fixtureId = $fixture['Fixtureid'];
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
        $sql = "DELETE FROM SeriesCandidates WHERE Seriesid = :Seriesid AND Userid = :Userid;";
        $stmt = $this->pdo->prepare($sql);
        foreach ($userIds as $userId) {
            $stmt->execute(['Seriesid' => $seriesId, 'Userid' => $userId]);
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
        $sql = "INSERT INTO SeriesCandidates (Seriesid, Userid) VALUES (:Seriesid, :Userid);";
        $stmt = $this->pdo->prepare($sql);
        foreach ($userIds as $userId) {
            $stmt->execute(['Seriesid' => $seriesId, 'Userid' => $userId]);
        }
    }
}
