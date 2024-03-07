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
        $description = $dayname . ' at ' . $time;
        return $description;
    }

    public function fixtureDescription($datestr)
    {
        $date = strtotime($datestr);
        return date("l jS \of F Y", $date);
    }
  
    public function getAllSeries() : array
    {
        $todayDate = date('Y-m-d');
        $sql = "SELECT FixtureSeries.Seriesid, SeriesWeekday, 
        LEFT(SeriesTime, 5) AS SeriesTime, AutoEmail, COUNT(FixtureId) AS FutureFixtures
        FROM FixtureSeries LEFT JOIN Fixtures ON FixtureSeries.Seriesid = Fixtures.Seriesid
        WHERE Fixtures.FixtureDate >= :today OR Fixtures.FixtureDate IS NULL
        GROUP BY FixtureSeries.Seriesid;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('today', $todayDate, \PDO::PARAM_STR); 
        $stmt->execute();
        $rows = $stmt->fetchall(\PDO::FETCH_ASSOC);
        $series = (array) null;
        foreach ($rows as $row) {
            $description = $this->seriesDescription($row['SeriesWeekday'], $row['SeriesTime']);
            $series[] = ['seriesid' => $row['Seriesid'], 'description' => $description, 
            'autoEmail' => $row['AutoEmail'], 'futureFixtures' => $row['FutureFixtures']];
        }
        return $series;
    }

    public function getSeries($seriesId) : array
    {
        // Retrieve basic series data...
        $sql = "SELECT Users.Userid, FirstName, LastName, SeriesWeekday, LEFT(SeriesTime,5) AS SeriesTime, 
        SeriesCourts, TargetCourts, AutoEmail
        FROM Users JOIN FixtureSeries ON Users.Userid = FixtureSeries.SeriesOwner
        WHERE Seriesid = :Seriesid;";
        $statement = $this->pdo->runSQL($sql,['Seriesid' => $seriesId]);
        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        $description = $this->seriesDescription($row['SeriesWeekday'], $row['SeriesTime']);
        $owner['Userid'] =  $row['Userid'];
        $owner['FirstName'] = $row['FirstName'];
        $owner['LastName'] = $row['LastName'];
        $seriesCourts = $row['SeriesCourts'];
        $targetCourts = $row['TargetCourts'];
        $autoEmail = $row['AutoEmail'];

        // Get upcoming two fixtures (there should only be two - or zero if the series has just been created)
        $todayDate = date('Y-m-d');
        $sql = "SELECT Fixtureid, FixtureDate, LEFT(FixtureTime, 5) AS FixtureTime FROM Fixtures 
        WHERE Seriesid = :Seriesid AND FixtureDate >= :today 
        ORDER BY FixtureDate ASC LIMIT 2;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('Seriesid', $seriesId, \PDO::PARAM_INT);
        $stmt->bindParam('today', $todayDate, \PDO::PARAM_STR); 
        $stmt->execute();
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
        $series = ['seriesid' => $seriesId, 'description' => $description,
         'owner' => $owner, 'courts' => $seriesCourts, 'targetCourts' => $targetCourts, 
         'autoEmail' => $autoEmail, 'participants' => $ParticipantList, 
         'fixtures' => $fixtureList, 'next2fixtures' => $next2Fixtures];
        return $series;
    }

    public function getBasicSeriesData($seriesId) : array
    {
        $sql = "SELECT Seriesid, SeriesOwner, SeriesWeekday, SeriesTime, SeriesCourts, TargetCourts, AutoEmail
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
        // Get the next fixture date, including today
        $sql = "SELECT SeriesWeekday FROM FixtureSeries WHERE Seriesid = :Seriesid;";
        $weekDay = $this->pdo->runSQL($sql,['Seriesid' => $seriesId])->fetchColumn();
        // Calculate the date of the next fixture
        $dayname = date('l', strtotime("Monday +$weekDay days"));
        $nextFixtureDt = strtotime("next $dayname", strtotime("-1 days")); // start from yesterday to include today
        return date("y-m-d", $nextFixtureDt);
    }

    public function addSeries($owner, $day, $time, $courts, $targetCourts)
    {
        $sql = "INSERT INTO FixtureSeries 
        (SeriesOwner, SeriesWeekday, SeriesTime, SeriesCourts, TargetCourts) 
        VALUES (:SeriesOwner, :SeriesWeekday, :SeriesTime, :SeriesCourts, :TargetCourts);";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('SeriesOwner', $owner, \PDO::PARAM_INT);
        $stmt->bindParam('SeriesWeekday', $day, \PDO::PARAM_INT);
        $stmt->bindParam('SeriesTime', $time, \PDO::PARAM_STR); 
        $stmt->bindParam('SeriesCourts', $courts, \PDO::PARAM_STR); 
        $stmt->bindParam('TargetCourts', $targetCourts, \PDO::PARAM_STR); 
        $stmt->execute();
        $seriesId = $this->pdo->lastInsertId();
        $this->addUsers($seriesId, array((int)$owner));
        return $seriesId;
    }

    public function updateBasicSeriesData($seriesId, $owner, $day, $time, $courts, $targetCourts, $autoEmail)
    {
        $sql = "UPDATE FixtureSeries 
        SET SeriesOwner = :SeriesOwner, SeriesWeekday = :SeriesWeekday, 
        SeriesTime = :SeriesTime, SeriesCourts = :SeriesCourts, TargetCourts = :TargetCourts, AutoEmail = :AutoEmail
        WHERE Seriesid = :Seriesid;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('Seriesid', $seriesId, \PDO::PARAM_INT);
        $stmt->bindParam('SeriesOwner', $owner, \PDO::PARAM_INT);
        $stmt->bindParam('SeriesWeekday', $day, \PDO::PARAM_INT);
        $stmt->bindParam('SeriesTime', $time, \PDO::PARAM_STR); 
        $stmt->bindParam('SeriesCourts', $courts, \PDO::PARAM_STR);
        $stmt->bindParam('TargetCourts', $targetCourts, \PDO::PARAM_STR);
        $stmt->bindParam('AutoEmail', $autoEmail, \PDO::PARAM_INT);
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
        $sql = "SELECT Users.Userid, FirstName, LastName 
        FROM Users JOIN SeriesCandidates ON Users.Userid = SeriesCandidates.Userid
        WHERE Seriesid = :Seriesid 
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
        $users = $this->pdo->runSQL($sql,['Seriesid' => $seriesId])->fetchall(\PDO::FETCH_ASSOC);
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
        // return fixtureid of next fixture or zero if there isn't one
        $todayDate = date('Y-m-d');
        $sql = "SELECT Fixtureid FROM Fixtures 
        WHERE Seriesid = :Seriesid AND FixtureDate > :today
        ORDER BY FixtureDate LIMIT 1;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('Seriesid', $seriesId, \PDO::PARAM_INT);
        $stmt->bindParam('today', $todayDate, \PDO::PARAM_STR); 
        $stmt->execute();
        $fixtureId = $stmt->fetchColumn();
        return $fixtureId == false ? 0 : $fixtureId;
    }

    public function latestFixture($seriesId) : int
    {
        // return fixtureid of the latest fixture or zero if there isn't one
        $sql = "SELECT Fixtureid FROM Fixtures 
        WHERE Seriesid = :Seriesid ORDER BY FixtureDate DESC LIMIT 1;";
        $stmt = $this->pdo->runSQL($sql, ['Seriesid' => $seriesId]);
        return (int)$stmt->fetchColumn();
    }

    private function addFixture($seriesId, $fixtureDate) : int
    {
        // Add a fixture at specified date and return the fixtureid
        // If the fixture already exists, return the fixtureid of that fixture
        $seriesRow = $this->getBasicSeriesData($seriesId);
        $fixtureOwner = $seriesRow['SeriesOwner'];
        $fixtureTime = $seriesRow['SeriesTime'];
        $fixtureCourts = $seriesRow['SeriesCourts'];
        $targetCourts = $seriesRow['TargetCourts'];
        $fixtureId = $this->checkFixtureExists($seriesId, $fixtureDate);
        if ($fixtureId != false) {
            return $fixtureId;
        }
        $sql = "INSERT INTO Fixtures (Seriesid, FixtureOwner, FixtureDate, FixtureTime, FixtureCourts, TargetCourts)
        VALUES (:Seriesid, :FixtureOwner, :FixtureDate, :FixtureTime, :FixtureCourts, :TargetCourts);";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('Seriesid', $seriesId, \PDO::PARAM_INT);
        $stmt->bindParam('FixtureOwner', $fixtureOwner, \PDO::PARAM_INT);
        $stmt->bindParam('FixtureDate', $fixtureDate, \PDO::PARAM_STR); 
        $stmt->bindParam('FixtureTime', $fixtureTime, \PDO::PARAM_STR); 
        $stmt->bindParam('FixtureCourts', $fixtureCourts, \PDO::PARAM_STR); 
        $stmt->bindParam('TargetCourts', $targetCourts, \PDO::PARAM_STR); 
        $stmt->execute();
        $fixtureId = (int)$this->pdo->lastInsertId();

        // Add fixture participants
        $sql = "INSERT INTO FixtureParticipants (Fixtureid, Userid)
        SELECT '$fixtureId', Userid FROM SeriesCandidates WHERE Seriesid = :Seriesid;";
        $this->pdo->runSQL($sql,['Seriesid' => $seriesId]);

        // Copy any court booking requests from any previous fixture
        $sql = "SELECT Fixtureid FROM Fixtures 
        WHERE Seriesid = :Seriesid AND FixtureDate < :FixtureDate
        ORDER BY FixtureDate DESC LIMIT 1;";
        $previousFixtureId = $this->pdo->runSQL($sql,
            ['Seriesid' => $seriesId, 'FixtureDate' => $fixtureDate])->fetchcolumn();
        if ($previousFixtureId == false) {
            $numParticipants = $this->pdo->runSQL(
                "SELECT COUNT(*) FROM FixtureParticipants WHERE Fixtureid = $fixtureId;")->fetchColumn();
            $range = explode("-", $targetCourts);
            // todo: add code to generate booking requests
            return $fixtureId; // no previous fixture
        }
        $sql ="INSERT INTO CourtBookings (Fixtureid, BookingTime, CourtNumber, BookingType)
        SELECT '$fixtureId', BookingTime, CourtNumber, BookingType FROM CourtBookings
        WHERE Fixtureid = :Fixtureid AND BookingType = 'Request';";
        $this->pdo->runSQL($sql,['Fixtureid' => $previousFixtureId]);
        return $fixtureId;
    }

    private function checkFixtureExists($seriesId, $fixtureDate) : int
    {
        // returns Fixtureid or zero if fixture does not exist
        $sql = "SELECT Fixtureid FROM Fixtures 
        WHERE Seriesid = :Seriesid AND FixtureDate = :FixtureDate;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('Seriesid', $seriesId, \PDO::PARAM_INT);
        $stmt->bindParam('FixtureDate', $fixtureDate, \PDO::PARAM_STR); 
        $stmt->execute();
        return (int)$stmt->fetchColumn(); 
    }
    
    private function getPastFixtures($seriesId, $count) : array
    {
        $todayDate = date('Y-m-d');
        $sql = "SELECT Fixtureid, FixtureDate, FixtureTime FROM Fixtures 
        WHERE Seriesid = :Seriesid AND FixtureDate < :today
        ORDER BY FixtureDate DESC LIMIT :Count;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('Seriesid', $seriesId, \PDO::PARAM_INT);
        $stmt->bindParam('today', $todayDate, \PDO::PARAM_STR); 
        $stmt->bindParam('Count', $count, \PDO::PARAM_INT);
        $stmt->execute();
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
