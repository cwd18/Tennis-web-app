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

    public function fixtureDescription($datestr)
    {
        $date = strtotime($datestr);
        return date("l jS \of F Y",$date);
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

        // Get upcoming two fixtures (there should only be two)
        $sql = "SELECT Fixtureid, FixtureDate, LEFT(FixtureTime, 5) AS FixtureTime FROM Fixtures 
        WHERE Seriesid = :Seriesid AND FixtureDate >= CURRENT_DATE() 
        ORDER BY FixtureDate ASC LIMIT 2;";
        $stmt = $this->pdo->runSQL($sql,['Seriesid' => $seriesId]);
        $next2Fixtures = $stmt->fetchall(\PDO::FETCH_ASSOC);
        
        // Get default fixture attendees...
        $users = $this->getSeriesUsers($seriesId);
        $ParticipantList = NULL;
        foreach ($users as $user) {
            $ParticipantList[] = $user['FirstName']." ".$user['LastName'];
        }

        // Get past fixtures...
        $fixtureList = $this->getPastFixtures($seriesId, 5);

        // return all series data
        $series = ['seriesid' => $seriesId, 'description' => $description, 'owner' => $owner, 'courts' => $seriesCourts,
        'participants' => $ParticipantList, 'fixtures' => $fixtureList, 'next2fixtures' => $next2Fixtures];
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

    public function getNextFixtureDate($seriesId) : string
    {
        $sql = "SELECT SeriesWeekday FROM FixtureSeries WHERE Seriesid = :Seriesid;";
        $weekDay = $this->pdo->runSQL($sql,['Seriesid' => $seriesId])->fetchColumn();
        // Calculate the date of the next fixture
        $dayname = date('l', strtotime("Monday +$weekDay days"));
        $nextFixtureDt = strtotime("next $dayname");
        return date("y-m-d", $nextFixtureDt);
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

    public function ensure2FutureFixtures($seriesId)
    {
        // Ensure that the next two future fixtures exist
        $nextFixtureDate = $this->getNextFixtureDate($seriesId);
        $this->addFixture($seriesId, $nextFixtureDate); // does nothing if fixture already exists
        $nextFixtureDatePlus = date("y-m-d",strtotime($nextFixtureDate) + 7 * 86400);
        $this->addFixture($seriesId, $nextFixtureDatePlus); // does nothing if fixture already exists
    }

    public function nextFixture($seriesId) : int
    {
        $today = date("y-m-d");
        $sql = "SELECT Fixtureid FROM Fixtures 
        WHERE Seriesid = :Seriesid AND FixtureDate > :Today
        ORDER BY FixtureDate LIMIT 1;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('Seriesid', $seriesId, \PDO::PARAM_INT);
        $stmt->bindParam('Today', $today, \PDO::PARAM_STR); 
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row == false ? 0 : $row['Fixtureid'];
    }

    private function addFixture($seriesId, $fixtureDate)
    {
        $seriesRow = $this->getBasicSeriesData($seriesId);
        $fixtureOwner = $seriesRow['SeriesOwner'];
        $fixtureTime = $seriesRow['SeriesTime'];
        $fixtureCourts = $seriesRow['SeriesCourts'];
        $fixtureId = $this->checkFixtureExists($seriesId, $fixtureDate);
        if ($fixtureId != false) {
            return $fixtureId;
        }
        $sql = "INSERT INTO Fixtures (Seriesid, FixtureOwner, FixtureDate, FixtureTime, FixtureCourts)
        VALUES (:Seriesid, :FixtureOwner, :FixtureDate, :FixtureTime, :FixtureCourts);";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('Seriesid', $seriesId, \PDO::PARAM_INT);
        $stmt->bindParam('FixtureOwner', $fixtureOwner, \PDO::PARAM_INT);
        $stmt->bindParam('FixtureDate', $fixtureDate, \PDO::PARAM_STR); 
        $stmt->bindParam('FixtureTime', $fixtureTime, \PDO::PARAM_STR); 
        $stmt->bindParam('FixtureCourts', $fixtureCourts, \PDO::PARAM_STR); 
        $stmt->execute();
        $fixtureId = $this->pdo->lastInsertId();
        $sql = "INSERT INTO FixtureParticipants (Fixtureid, Userid)
        SELECT '$fixtureId', Userid FROM SeriesCandidates WHERE Seriesid = :Seriesid;";
        $this->pdo->runSQL($sql,['Seriesid' => $seriesId]);
        return $fixtureId;
    }

    private function checkFixtureExists($seriesId, $fixtureDate) : int|bool
    {
        // returns Fixtureid or zero if fixture does not exist
        $sql = "SELECT Fixtureid FROM Fixtures 
        WHERE Seriesid = :Seriesid AND FixtureDate = :FixtureDate;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('Seriesid', $seriesId, \PDO::PARAM_INT);
        $stmt->bindParam('FixtureDate', $fixtureDate, \PDO::PARAM_STR); 
        $stmt->execute();
        return $stmt->fetchColumn(); 
    }
    
    private function getPastFixtures($seriesId, $count) : array
    {
        $sql = "SELECT Fixtureid, FixtureDate, FixtureTime FROM Fixtures 
        WHERE Seriesid = :Seriesid AND FixtureDate < CURRENT_DATE()
        ORDER BY FixtureDate DESC LIMIT :Count;";
        $stmt = $this->pdo->runSQL($sql,['Seriesid' => $seriesId, 'Count' => $count]);
        $result = $stmt->fetchall(\PDO::FETCH_ASSOC);
        if (empty($result)) {
            return $result;
        }
        foreach ($result as $row) {
            $description = $this->fixtureDescription($row['FixtureDate']);
            $time = substr($row['FixtureTime'],0,5);
            $fixtures[] = ['fixtureid' => $row['Fixtureid'], 'description' => $description, 
            'date' => $row['FixtureDate'], 'time' => $time];
        }
        return $fixtures;
    }

}
