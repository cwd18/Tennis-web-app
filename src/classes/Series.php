<?php
declare(strict_types=1);

namespace TennisApp;

use TennisApp\Fixtures;

class Series
{
    private readonly Database $pdo;
    private int $seriesId;
    protected $base;

    public function __construct(Database $pdo, int $seriesId)
    {
        $this->pdo = $pdo;
        $this->seriesId = $seriesId;
        $this->setBase();
    }

    private function setBase()
    {
        // Retrieve basic series data...
        $sql = "SELECT Seriesid, SeriesOwner, FirstName, LastName, SeriesWeekday, LEFT(SeriesTime,5) AS SeriesTime, 
        SeriesCourts, TargetCourts, AutoEmail
        FROM Users JOIN FixtureSeries ON Users.Userid = FixtureSeries.SeriesOwner
        WHERE Seriesid = :Seriesid;";
        $stmt = $this->pdo->runSQL($sql,['Seriesid' => $this->seriesId]);
        $this->base = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->base['dayname'] = date('l', strtotime("Monday +" . $this->base['SeriesWeekday'] . " days"));
        $this->base['description'] = $this->base['dayname'] . ' at ' . $this->base['SeriesTime'];
    }

    public function getSeriesData() : array
    {
        // Get series data for series view
        // Get upcoming two fixtures (there should only be two - or zero if the series has just been created)
        $seriesId = $this->seriesId;
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

        // Get past fixtures...
        $pastFixtures = $this->getPastFixtures(8);

        // return all series data
        $seriesData = ['seriesid' => $seriesId, 'base' => $this->base, 'participants' => $users, 
         'pastFixtures' => $pastFixtures, 'next2fixtures' => $next2Fixtures];
        return $seriesData;
    }

    public function getBasicSeriesData() : array
    {
        return $this->base;
    }

    public function getOwnerid() : int
    {
        return $this->base['owner']['Userid'];
    }
    
    public function getFixtureIndex($fixtureId) : int
    {
        // Get the index of the future fixture in the series
        $todayDate = date('Y-m-d');
        $sql = "SELECT FixtureId 
        FROM FixtureSeries JOIN Fixtures ON FixtureSeries.Seriesid = Fixtures.Seriesid
        WHERE Fixtures.FixtureDate >= :today AND FixtureSeries.Seriesid = :Seriesid
        ORDER BY FixtureDate;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('today', $todayDate, \PDO::PARAM_STR); 
        $stmt->bindParam('Seriesid', $this->seriesId, \PDO::PARAM_INT);
        $stmt->execute();
        for ($index = 0; $fixture = $stmt->fetchColumn(); $index++) {
            if ($fixture == $fixtureId) {
                return $index;
            }
        }
        throw new \Exception("Fixture $fixtureId not found in series $this->seriesId");
    }

    public function getNextFixtureDate() : string
    {
        // Get the next fixture date, including today
        $dayname = $this->base['dayname'];
        $nextFixtureDt = strtotime("next $dayname", strtotime("-1 days")); // start from yesterday to include today
        return date("y-m-d", $nextFixtureDt);
    }

    public function getDaysToNextFixture() : int
    {
        // Get the number of days to the next fixture, including today's fixture
        $nextFixtureDate = $this->getNextFixtureDate();
        $todayDate = date('Y-m-d');
        return (int)((strtotime($nextFixtureDate) - strtotime($todayDate)) / 86400);
    }

    public function countFutureFixtures() : int
    {
        $todayDate = date('Y-m-d');
        $sql = "SELECT COUNT(FixtureId) 
        FROM FixtureSeries JOIN Fixtures ON FixtureSeries.Seriesid = Fixtures.Seriesid
        WHERE Fixtures.FixtureDate >= :today AND FixtureSeries.Seriesid = :Seriesid;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('today', $todayDate, \PDO::PARAM_STR); 
        $stmt->bindParam('Seriesid', $this->seriesId, \PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function updateBasicSeriesData($owner, $day, $time, $courts, $targetCourts, $autoEmail)
    {
        $sql = "UPDATE FixtureSeries 
        SET SeriesOwner = :SeriesOwner, SeriesWeekday = :SeriesWeekday, SeriesTime = :SeriesTime, 
        SeriesCourts = :SeriesCourts, TargetCourts = :TargetCourts, AutoEmail = :AutoEmail
        WHERE Seriesid = :Seriesid;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('Seriesid', $this->seriesId, \PDO::PARAM_INT);
        $stmt->bindParam('SeriesOwner', $owner, \PDO::PARAM_INT);
        $stmt->bindParam('SeriesWeekday', $day, \PDO::PARAM_INT);
        $stmt->bindParam('SeriesTime', $time, \PDO::PARAM_STR); 
        $stmt->bindParam('SeriesCourts', $courts, \PDO::PARAM_STR);
        $stmt->bindParam('TargetCourts', $targetCourts, \PDO::PARAM_STR);
        $stmt->bindParam('AutoEmail', $autoEmail, \PDO::PARAM_INT);
        $stmt->execute();
        $this->setBase();
    }

    public function deleteSeries()
    {
        // Delete any fixtures
        $sql = "SELECT Fixtureid FROM Fixtures WHERE Seriesid = :Seriesid;";
        $stmt = $this->pdo->runSQL($sql,['Seriesid' => $this->seriesId]);
        while ($fixtureId = $stmt->fetchColumn()) {
            $f = new Fixture($this->pdo, $fixtureId);
            $f->deleteFixture();
        }
        // Delete any candidates
        $sql = "DELETE FROM SeriesCandidates WHERE Seriesid = :Seriesid;";
        $this->pdo->runSQL($sql,['Seriesid' => $this->seriesId]);
        // Delete the series
        $sql = "DELETE FROM FixtureSeries WHERE Seriesid = :Seriesid;";
        $this->pdo->runSQL($sql,['Seriesid' => $this->seriesId]);
    }

    public function getSeriesUsers() : array|bool
    {
        // Return list of users for this series 
        $sql = "SELECT Users.Userid, FirstName, LastName, ShortName, Booker
        FROM Users JOIN SeriesCandidates ON Users.Userid = SeriesCandidates.Userid
        WHERE Seriesid = :Seriesid 
        ORDER BY ShortName;";
        $statement = $this->pdo->runSQL($sql,['Seriesid' => $this->seriesId]);
        $users = $statement->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }
    
    public function deleteSeriesUsers($userIds)
    {
        // Delete specified users from this series 
        $sql = "DELETE FROM SeriesCandidates WHERE Seriesid = :Seriesid AND Userid = :Userid;";
        $stmt = $this->pdo->prepare($sql);
        foreach ($userIds as $userId) {
            $stmt->execute(['Seriesid' => $this->seriesId, 'Userid' => $userId]);
        }
    }

    public function getSeriesCandidates() : array
    {
        // Return list of possible candidate participants to add to the series, 
        // which excludes existing participants
        $sql = "SELECT Userid, FirstName, LastName FROM Users
        WHERE Users.Userid NOT IN (SELECT Userid FROM SeriesCandidates WHERE Seriesid = :Seriesid)
        ORDER BY FirstName, LastName;";
        $users = $this->pdo->runSQL($sql,['Seriesid' => $this->seriesId])->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function addUsers($userIds)
    {
        // Add users to the series
        $sql = "INSERT INTO SeriesCandidates (Seriesid, Userid) VALUES (:Seriesid, :Userid);";
        $stmt = $this->pdo->prepare($sql);
        foreach ($userIds as $userId) {
            $stmt->execute(['Seriesid' => $this->seriesId, 'Userid' => $userId]);
        }
    }

    public function ensure2FutureFixtures()
    {
        // Ensure that the next two future fixtures exist
        $nextFixtureDate = $this->getNextFixtureDate();
        $this->addFixture($nextFixtureDate); // does nothing if fixture already exists
        $nextFixtureDatePlus = date("y-m-d",strtotime($nextFixtureDate) + 7 * 86400);
        $this->addFixture($nextFixtureDatePlus); // does nothing if fixture already exists
    }

    public function nextFixture() : int
    {
        // return fixtureid of next fixture or zero if there isn't one
        $todayDate = date('Y-m-d');
        $sql = "SELECT Fixtureid FROM Fixtures 
        WHERE Seriesid = :Seriesid AND FixtureDate >= :today
        ORDER BY FixtureDate LIMIT 1;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('Seriesid', $this->seriesId, \PDO::PARAM_INT);
        $stmt->bindParam('today', $todayDate, \PDO::PARAM_STR); 
        $stmt->execute();
        $fixtureId = $stmt->fetchColumn();
        return $fixtureId = (int)$fixtureId;
    }

    public function latestFixture() : int
    {
        // return fixtureid of the latest fixture or zero if there isn't one
        $sql = "SELECT Fixtureid FROM Fixtures 
        WHERE Seriesid = :Seriesid ORDER BY FixtureDate DESC LIMIT 1;";
        $stmt = $this->pdo->runSQL($sql, ['Seriesid' => $this->seriesId]);
        return (int)$stmt->fetchColumn();
    }

    private function addFixture($fixtureDate) : int
    {
        // Add a fixture at specified date and return the fixtureid
        // If the fixture already exists, return the fixtureid of that fixture
        $seriesId = $this->seriesId;
        $seriesRow = $this->getBasicSeriesData($seriesId);
        $fixtureOwner = $seriesRow['SeriesOwner'];
        $fixtureTime = $seriesRow['SeriesTime'];
        $fixtureCourts = $seriesRow['SeriesCourts'];
        $targetCourts = $seriesRow['TargetCourts'];
        $fixtureId = $this->checkFixtureExists($fixtureDate);
        if ($fixtureId != 0) {
            return $fixtureId;} // fixture already exists
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

        // Set CourtsBooked to 0 for non-bookers 
        // so they don't get a page asking them to record their court bookings
        $sql = "UPDATE FixtureParticipants
        JOIN Users ON FixtureParticipants.Userid = Users.Userid
        SET CourtsBooked = 0
        WHERE Fixtureid = :Fixtureid AND Booker = FALSE ;";
        $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);

        // Copy any court booking requests from any previous fixture
        $sql = "SELECT Fixtureid FROM Fixtures 
        WHERE Seriesid = :Seriesid AND FixtureDate < :FixtureDate
        ORDER BY FixtureDate DESC LIMIT 1;";
        $previousFixtureId = $this->pdo->runSQL($sql,
            ['Seriesid' => $seriesId, 'FixtureDate' => $fixtureDate])->fetchcolumn();
        if ($previousFixtureId == false) {
            return $fixtureId; // no previous fixture
        }
        $sql = "INSERT INTO CourtBookings (Fixtureid, BookingTime, CourtNumber, BookingType, Userid)
        SELECT $fixtureId AS FixtureId, BookingTime, CourtNumber, BookingType, Userid 
        FROM CourtBookings 
        WHERE Fixtureid = :previousFixtureid AND BookingType = 'Request'
        AND UserId IN (SELECT UserId FROM FixtureParticipants WHERE FixtureId = $fixtureId);";
        $this->pdo->runSQL($sql,['previousFixtureid' => $previousFixtureId]);
        return $fixtureId;
    }
  
    private function checkFixtureExists($fixtureDate) : int
    {
        // returns Fixtureid or zero if fixture does not exist
        $sql = "SELECT Fixtureid FROM Fixtures 
        WHERE Seriesid = :Seriesid AND FixtureDate = :FixtureDate;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('Seriesid', $this->seriesId, \PDO::PARAM_INT);
        $stmt->bindParam('FixtureDate', $fixtureDate, \PDO::PARAM_STR); 
        $stmt->execute();
        return (int)$stmt->fetchColumn(); 
    }
    
    private function getPastFixtures($count) : array
    {
        $seriesId = $this->seriesId;
        $todayDate = date('Y-m-d');
        $sql = "SELECT Fixtureid FROM Fixtures 
        WHERE Seriesid = :Seriesid AND FixtureDate < :today
        ORDER BY FixtureDate DESC LIMIT :Count;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('Seriesid', $seriesId, \PDO::PARAM_INT);
        $stmt->bindParam('today', $todayDate, \PDO::PARAM_STR); 
        $stmt->bindParam('Count', $count, \PDO::PARAM_INT);
        $stmt->execute();
        $pastFixtures = [];
        while ($fixtureId = $stmt->fetchColumn()) {
            $f = new Fixture($this->pdo, $fixtureId);
            $pastFixtures[] = $f->getBasicFixtureData();
        }
        return $pastFixtures;
    }

}
