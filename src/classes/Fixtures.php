<?php
declare(strict_types=1);

namespace TennisApp;

class Fixtures
{
    protected $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getSeriesid($fixtureId) : int|bool
    {
        $sql = "SELECT Seriesid FROM Fixtures WHERE Fixtureid = :Fixtureid;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        return $stmt->fetchColumn();
    }

    public function getBasicFixtureData($fixtureId) : array
    {
        $sql = "SELECT Fixtureid, FixtureOwner, FixtureDate, FixtureTime, FixtureCourts
        FROM Fixtures WHERE Fixtureid = :Fixtureid;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row;
    }

    public function inBookingWindow($fixtureId) : int
    {
        // Return 0 if the current time is inside the booking window for this fixture
        // Return -1 if the current time is earlier than the booking window
        // Return +1 if the current time is later than the booking window
        // The booking window is the week before 07:30 on the fixture data
        $sql = "SELECT FixtureDate FROM Fixtures WHERE Fixtureid = :Fixtureid;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $fixtureDate = $stmt->fetchColumn();
        $bookingTime = "07:30";
        $bookingDt2 = strtotime($fixtureDate . " " . $bookingTime);
        $bookingDt1 = $bookingDt2 - 7 * 26 * 60 * 60; // 7 days earlier
        $nowDt = time();
        if ($nowDt < $bookingDt1) {
            $r = -1;
        } else if ($nowDt > $bookingDt2) {
            $r = 1;
        } else {
            $r =0;
        }
        return $r;
    }

    public function updateBasicFixtureData($fixtureId, $date, $time, $courts)
    {
        $sql = "UPDATE Fixtures SET FixtureDate = :FixtureDate, 
        FixtureTime = :FixtureTime, FixtureCourts = :FixtureCourts
        WHERE Fixtureid = :Fixtureid;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('Fixtureid', $fixtureId, \PDO::PARAM_INT);
        $stmt->bindParam('FixtureDate', $date, \PDO::PARAM_STR); 
        $stmt->bindParam('FixtureTime', $time, \PDO::PARAM_STR); 
        $stmt->bindParam('FixtureCourts', $courts, \PDO::PARAM_STR); 
        $stmt->execute();
    }

    public function deleteFixture($fixtureId)
    {
        $sql = "DELETE FROM CourtBookings WHERE Fixtureid = :Fixtureid;";
        $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $sql = "DELETE FROM FixtureParticipants WHERE Fixtureid = :Fixtureid;";
        $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $sql = "DELETE FROM Fixtures WHERE Fixtureid = :Fixtureid;";
        $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
    }

    public function getFixtureCandidates($fixtureId) : array
    {
        // Return list of possible candidate participants to add to the fixture, 
        // which excludes existing participants
        $sql = "SELECT Userid, FirstName, LastName FROM Users
        WHERE Users.Userid NOT IN (SELECT Userid FROM FixtureParticipants WHERE Fixtureid = :Fixtureid)
        ORDER BY FirstName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $users = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function getFixtureParticipants($fixtureId) : array
    {
        // Return list of fixture participants
        $sql = "SELECT Users.Userid, FirstName, LastName 
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE  Fixtureid = :Fixtureid
        ORDER BY FirstName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $users = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function getWantToPlay($fixtureId) : array
    {
        // Return list of users who want to play and are currently not playing
        $sql = "SELECT Users.Userid, FirstName, LastName 
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE Fixtureid = :Fixtureid AND WantsToPlay = TRUE AND IsPlaying = FALSE
        ORDER BY FirstName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $users = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function getWantToPlayCandidates($fixtureId) : array
    {
        // Return list of users who have not yet declared they want to play
        $sql = "SELECT Users.Userid, FirstName, LastName 
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE Fixtureid = :Fixtureid AND WantsToPlay IS NULL
        ORDER BY FirstName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $users = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function getParticipantData($fixtureId, $userId) : array|bool
    {
        $sql = "SELECT Users.Userid, FirstName, LastName, WantsToPlay, IsPlaying, CourtsBooked 
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE Fixtureid = :Fixtureid AND FixtureParticipants.Userid = :Userid;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId, 'Userid' => $userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row;
    }

    public function getWantsToPlay($fixtureId, $userId) : ?int
    {
        $sql = "SELECT WantsToPlay FROM FixtureParticipants
        WHERE Fixtureid = :Fixtureid AND Userid = :Userid;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId, 'Userid' => $userId]);
        return $stmt->fetchColumn();
    }
    
    public function getCourtsBooked($fixtureId, $userId) : ?int
    {
        // Return the value of the CourtsBooked column, which is initially NULL but sent to FALSE
        // when user has indicated they did not book any court
        $sql = "SELECT CourtsBooked FROM FixtureParticipants
        WHERE Fixtureid = :Fixtureid AND Userid = :Userid;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId, 'Userid' => $userId]);
        return $stmt->fetchColumn();
    }
    
    public function setCourtsBooked(int $fixtureId, int $userId, bool $val)
    {
        $sql = "UPDATE FixtureParticipants SET CourtsBooked = :val 
        WHERE Fixtureid = :Fixtureid AND Userid = :Userid;";
        $this->pdo->runSQL($sql, ['val' => $val, 'Fixtureid' => $fixtureId, 'Userid' => $userId]);
    }
    
    public function setBookersPlaying($fixtureId)
    {
        // Set bookers to playing unless they have declared they don't want to play
        $sql = "UPDATE FixtureParticipants
        JOIN CourtBookings ON FixtureParticipants.Fixtureid = CourtBookings.Fixtureid
        AND FixtureParticipants.Userid = CourtBookings.UserId
        SET WantsToPlay =  TRUE, IsPlaying = TRUE
        WHERE BookingType = 'Booked' AND FixtureParticipants.Fixtureid = :Fixtureid        
        AND (WantsToPlay = TRUE OR WantsToPlay IS NULL) AND IsPlaying = FALSE;";
        $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
    }

    public function setPlaying($fixtureId, $userIds)
    {
        $sql = "UPDATE FixtureParticipants SET IsPlaying = TRUE
        WHERE Fixtureid = :Fixtureid AND Userid = :Userid;";
        $stmt = $this->pdo->prepare($sql);
        foreach ($userIds as $userId) {
            $stmt->execute(['Fixtureid' => $fixtureId, 'Userid' => $userId]);        
        }
    }

    public function setWantsToPlay($fixtureId, $userIds)
    {
        // Set participant(s) to want to play and update AcceptTime to current time if NULL
        $dateTimeNow = date("Y-m-d H:i:s");
        $sql = "UPDATE FixtureParticipants SET WantsToPlay = TRUE,
        AcceptTime = CASE
            WHEN AcceptTime IS NULL THEN :DTnow
            ELSE AcceptTime
            END
        WHERE Fixtureid = :Fixtureid AND Userid = :Userid;";
        $stmt = $this->pdo->prepare($sql);
        if (is_array($userIds)) {
            foreach ($userIds as $userId) {
                $stmt->execute(['Fixtureid' => $fixtureId, 'Userid' => $userId, 'DTnow' => $dateTimeNow]);        
            } 
        } else {
            $stmt->execute(['Fixtureid' => $fixtureId, 'Userid' => $userIds, 'DTnow' => $dateTimeNow]);        
        }
    }

    public function setWantsNotToPlay($fixtureId, $userId)
    {
        $sql = "UPDATE FixtureParticipants SET WantsToPlay = FALSE, IsPlaying = FALSE 
        WHERE Fixtureid = :Fixtureid AND Userid = :Userid;";
        $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId, 'Userid' => $userId]);
    }

    public function resetPlaying($fixtureId)
    {
        $sql = "UPDATE FixtureParticipants SET IsPlaying = FALSE
        WHERE Fixtureid = :Fixtureid;";
        $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
    }

    public function addUsers($fixtureId, $userIds)
    {
        // Add users to the fixture
        $sql = "INSERT INTO FixtureParticipants (Fixtureid, Userid) 
        VALUES (:Fixtureid, :Userid);";
        $stmt = $this->pdo->prepare($sql);
        foreach ($userIds as $userId) {
            $stmt->execute(['Fixtureid' => $fixtureId, 'Userid' => $userId]);
        }
    }

    public function getFixtureUsers($fixtureId) : array
    {
        // Return list of existing participants
        $sql = "SELECT Users.Userid, FirstName, LastName 
        FROM Users JOIN FixtureParticipants ON Users.Userid=FixtureParticipants.Userid
        WHERE Fixtureid = :Fixtureid
        ORDER BY FirstName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $users = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function getFixtureNonBookers($fixtureId, $type) : array
    {
        // Return list of existing participants that have not booked or requested
        $sql = "SELECT Users.Userid, FirstName, LastName 
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid 
        WHERE Fixtureid = :Fixtureid1
        AND Users.Userid NOT IN 
        (SELECT Userid FROM CourtBookings WHERE BookingType = :BookingType AND Fixtureid = :Fixtureid2)
        ORDER BY FirstName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid1' => $fixtureId, 'BookingType' => $type, 'Fixtureid2' => $fixtureId]);
        $users = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function deleteFixtureUsers($fixtureId, $userIds)
    {
        // Delete specified users from this fixture 
        $sql = "DELETE FROM FixtureParticipants WHERE Fixtureid = :Fixtureid AND Userid = :Userid;";
        $stmt = $this->pdo->prepare($sql);
        foreach ($userIds as $userId) {
            $stmt->execute(['Fixtureid' => $fixtureId, 'Userid' => $userId]);
            }
    }

    public function addCourtBooking($fixtureId, $bookerId, $time, $court, $type)
    {
        $sql="INSERT INTO CourtBookings (Fixtureid, Userid, BookingTime, CourtNumber, BookingType)
        VALUES (:Fixtureid, :Bookerid, :BookingTime, :CourtNumber, :BookingType);";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('Fixtureid', $fixtureId, \PDO::PARAM_INT);
        $stmt->bindParam('Bookerid', $bookerId, \PDO::PARAM_INT);
        $stmt->bindParam('BookingTime', $time, \PDO::PARAM_STR); 
        $stmt->bindParam('CourtNumber', $court, \PDO::PARAM_INT);
        $stmt->bindParam('BookingType', $type, \PDO::PARAM_STR); 
        $stmt->execute();
    }

    public function deleteCourtBooking($fixtureId, $userId, $time, $court, $type)
    {
        $sql = "DELETE FROM CourtBookings WHERE BookingType = :BookingType 
        AND Fixtureid = :Fixtureid AND Userid = :Userid
        AND BookingTime = :BookingTime AND CourtNumber = :CourtNumber;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('Fixtureid', $fixtureId, \PDO::PARAM_INT);
        $stmt->bindParam('Userid', $userId, \PDO::PARAM_INT);
        $stmt->bindParam('BookingTime', $time, \PDO::PARAM_STR); 
        $stmt->bindParam('CourtNumber', $court, \PDO::PARAM_INT);
        $stmt->bindParam('BookingType', $type, \PDO::PARAM_STR); 
        $stmt->execute();
    }

    private function getAvailableCourts($fixtureId, $time, $type) : array
    {
        // Return a list of available courts for the passed time
        $sql = "SELECT FixtureCourts FROM Fixtures WHERE Fixtureid = :Fixtureid;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $fixtureCourts = explode(",", str_replace(' ','',$row['FixtureCourts']));
        $sql = "SELECT CourtNumber FROM CourtBookings 
        WHERE BookingType = :BookingType AND Fixtureid = :Fixtureid AND BookingTime = :BookingTime
        ORDER BY CourtNumber;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('BookingType', $type, \PDO::PARAM_STR); 
        $stmt->bindParam('Fixtureid', $fixtureId, \PDO::PARAM_INT);
        $stmt->bindParam('BookingTime', $time, \PDO::PARAM_STR); 
        $stmt->execute();
        $rows = $stmt->fetchall(\PDO::FETCH_ASSOC);
        $excludedCourts[] = NULL;
        foreach ($rows as $row) {
        $excludedCourts[] = $row['CourtNumber'];
        }
        foreach ($fixtureCourts as $rangeStr) {
            $range = explode("-", $rangeStr);
            $n1 = (int)$range[0];
            $n2 = (int)$range[1];
            for ($n=$n1; $n<=$n2; $n++) {
                $ok = TRUE;
                foreach ($excludedCourts as $excludedCourt) {
                    if ($n == $excludedCourt) {
                        $ok = FALSE;
                        break;
                    }
                }
                if ($ok) { $courts[] = $n; }
            }
            $courts[] = 0;
        }
        return $courts;
    }

    public function getFixture(int $fixtureId) : array
    {
        // Get Fixture data
        $sql="SELECT Fixtures.Seriesid, FirstName, LastName, 
        FixtureDate, LEFT(FixtureTime, 5) AS FixtureTime, FixtureCourts, InvitationsSent
        FROM Fixtures JOIN Users ON Fixtures.FixtureOwner = Users.Userid 
        JOIN FixtureSeries ON Fixtures.Seriesid = FixtureSeries.Seriesid
        WHERE Fixtureid = :Fixtureid;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $seriesId = $row['Seriesid'];
        $owner['FirstName'] = $row['FirstName'];
        $owner['LastName'] = $row['LastName'];
        $description = date("l jS \of F Y", strtotime($row['FixtureDate']));
        $fixtureTime = $row['FixtureTime'];
        $fixtureCourts = $row['FixtureCourts'];
        $invitationsSent = $row['InvitationsSent'];

        // Calculate booking time slots
        $bookingBase = $fixtureTime;
        $bookingRange = 2;
        if ($bookingBase=='08:30') {
            $bookingBase = '07:30';
            $bookingRange =3 ;
        }
        for ($n=0; $n<$bookingRange; $n++) {
            $bookingTimes[$n] = date("H:i",strtotime($bookingBase)+$n*3600);
        }
        $bookingTime1 = $bookingTimes[0];
        $bookingTime2 = $bookingTimes[1];
        if ($bookingRange==3) {
            $bookingTime1 = $bookingTimes[1];
            $bookingTime2 = $bookingTimes[2];
        }
        
        // Get players...
        $sql="SELECT DISTINCT Users.Userid, FirstName, LastName, AcceptTime, CourtsBooked
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE FixtureParticipants.Fixtureid = :Fixtureid 
        AND IsPlaying = TRUE
        ORDER BY CourtsBooked DESC, AcceptTime, FirstName, LastName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $playerList = $stmt->fetchall(\PDO::FETCH_ASSOC);

        // Get people who have accepted but not marked to play...
        $sql="SELECT DISTINCT Users.Userid, FirstName, LastName, AcceptTime, CourtsBooked
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE FixtureParticipants.Fixtureid = :Fixtureid 
        AND IsPlaying = FALSE AND WantsToPlay = TRUE
        ORDER BY CourtsBooked DESC, AcceptTime, FirstName, LastName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $reserveList = $stmt->fetchall(\PDO::FETCH_ASSOC);

        // Get decliners...
        $sql="SELECT Users.Userid, FirstName, LastName
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE Fixtureid = :Fixtureid 
        AND WantsToPlay = FALSE
        ORDER BY FirstName, LastName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $declineList = $stmt->fetchall(\PDO::FETCH_ASSOC);
        
        // Get abstainers (people who haven't responded to invitation)...
        $sql="SELECT Users.Userid, FirstName, LastName
        FROM Users, FixtureParticipants
        WHERE Fixtureid = :Fixtureid AND Users.Userid = FixtureParticipants.Userid
        AND WantsToPlay IS NULL
        ORDER BY FirstName, LastName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $abstainList = $stmt->fetchall(\PDO::FETCH_ASSOC);

        // Are we in the booking window for this fixture
        $inBookingWindow = $this->inBookingWindow($fixtureId);

        // get requested bookings
        $requestedBookings = $this->getRequestedBookings($fixtureId);
        
        // Get court bookings into grid with columns (court, booking time, bookers)
        $bookingGrid[0][0] = "Court";
        $sql = "SELECT DISTINCT BookingTime FROM CourtBookings 
        WHERE BookingType = 'Booked' AND Fixtureid = :Fixtureid 
        ORDER BY BookingTime;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $rows = $stmt->fetchall(\PDO::FETCH_ASSOC);

        $c=1;
        foreach ($rows as $row) {
            $bookingGrid[0][$c] = substr($row['BookingTime'],0,5);
            $c++;
        }
        $bookersColumn=$c;
        $bookingGrid[0][$bookersColumn]="Bookers";

        $sql="SELECT DISTINCT CourtNumber FROM CourtBookings 
        WHERE BookingType = 'Booked' AND Fixtureid = :Fixtureid ORDER BY CourtNumber;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $rows = $stmt->fetchall(\PDO::FETCH_ASSOC);
        $r=1;
        foreach ($rows as $row) {
            $bookingGrid[$r][0] = $row['CourtNumber'];
            for ($c=1;$c<=$bookersColumn;$c++) {$bookingGrid[$r][$c] = "-";}
            $r++;
        }

        $sql = "SELECT FirstName, LastName, CourtNumber, BookingTime FROM Users
        JOIN CourtBookings ON Users.Userid = CourtBookings.Userid
        WHERE BookingType = 'Booked' AND Fixtureid = :Fixtureid 
        ORDER BY CourtNumber, BookingTime;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $rows = $stmt->fetchall(\PDO::FETCH_ASSOC);
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $name = $row['FirstName']." ".$row['LastName'];
                for ($r=1;$bookingGrid[$r][0]!=$row['CourtNumber'];$r++) {} // match grid row
                for ($c=1;$bookingGrid[0][$c]!=substr($row['BookingTime'],0,5);$c++) {} // match grid column
                $bookingGrid[$r][$c] = $row['CourtNumber'];
                if ($bookingGrid[$r][$bookersColumn]=="-") {
                    $bookingGrid[$r][$bookersColumn] = $name;
                } else {
                    $names = explode(", ", $bookingGrid[$r][$bookersColumn]);
                    if (strcmp($names[count($names) - 1],$name) != 0) {
                        $bookingGrid[$r][$bookersColumn] = $bookingGrid[$r][$bookersColumn].", ".$name;
                    } else {
                        $bookingGrid[$r][$bookersColumn] = $bookingGrid[$r][$bookersColumn]." (2)";
                    }
                }
            }
            // remove the first column from the grid as it isn't wanted for display
            for ($r=0; $r < count($bookingGrid); $r++) {
                for ($c = 1; $c <= $bookersColumn; $c++) {
                    $bookingViewGrid[$r][$c-1] = $bookingGrid[$r][$c];
                }
            }
        } else {
            $bookingViewGrid[0][0]="None";
        }

        // return all fixture data
        $fixture = ['seriesid' => $seriesId, 'fixtureid' => $fixtureId,
        'description' => $description, 'time' => $fixtureTime,
        'owner' => $owner, 'invitationsSent' => $invitationsSent,
        'players' => $playerList, 'reserves' => $reserveList, 
        'decliners' => $declineList,  'abstainers' => $abstainList,
        'inBookingWindow' => $inBookingWindow, 'requestedBookings' =>$requestedBookings,
        'bookingtimes' => $bookingTimes, 'time1' => $bookingTime1, 'time2' => $bookingTime2,
        'bookings' => $bookingViewGrid, 'courts' => $fixtureCourts];
        return $fixture;
    }

    public function getParticipantBookings(int $fixtureId, int $userId, $type) : array
    {
        $sql = "SELECT CourtNumber, BookingTime FROM CourtBookings
        WHERE BookingType = :BookingType AND Fixtureid = :Fixtureid AND Userid = :Userid
        ORDER BY BookingTime;";
        $stmt = $this->pdo->runSQL($sql,['BookingType' => $type, 'Fixtureid' => $fixtureId, 'Userid' => $userId]); 
        $rows = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $rows;
    }

    public function getRequestedBookings(int $fixtureId) : array
    {
        // Get the current list of booking requests for this fixture
        // Returns an empty array if no requests yet
        $sql = "SELECT Users.Userid, FirstName, LastName, CourtNumber, LEFT(BookingTime, 5) AS BookingTime 
        FROM CourtBookings JOIN Users ON Users.Userid = CourtBookings.Userid
        WHERE BookingType = 'Request' AND Fixtureid = :Fixtureid 
        ORDER BY BookingTime, CourtNumber;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]); 
        $rows = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $rows;
    }

    public function countParticipantBookings(int $fixtureId, int $userId, $type) : int
    {
        $sql = "SELECT COUNT(CourtNumber) FROM CourtBookings
        WHERE BookingType = :BookingType AND Fixtureid = :Fixtureid AND Userid = :Userid;";
        $stmt = $this->pdo->runSQL($sql,['BookingType' => $type, 'Fixtureid' => $fixtureId, 'Userid' => $userId]); 
        return $stmt->fetchColumn();
    }

    public function getBookingFormData(int $fixtureId, int $userId, $type) : array
    {
        $fixture = $this->getFixture($fixtureId);
        $u = $this->getParticipantData($fixtureId, $userId);
        $brows = $this->getParticipantBookings($fixtureId, $userId, $type);
        $bookings=null;
        $n=0;
        foreach ($brows as $b) {
            $bookings[$n]['court'] = $b['CourtNumber'];
            $bookings[$n]['time'] = substr($b['BookingTime'],0,5);
            $n++;
        }

        foreach ($fixture['bookingtimes'] as $time) {
            $courts[$time] = $this->getAvailableCourts($fixtureId, $time, $type);
        }

        $usedBookingTime = "";
        if (is_null($bookings)===false and sizeof($bookings)==1) {
            $usedBookingTime = $bookings[0]['time'];
        }

        $isPlaying = $u['IsPlaying']?"Yes":"No";
        if (is_null($u['WantsToPlay'])) { $wantsToPlay = "Unknown"; }
        else { $wantsToPlay = $u['WantsToPlay']?"Yes":"No"; }
        return ['fixture' => $fixture, 'bookingType' => $type, 'participant' => $u,
        'isplaying' => $isPlaying, 'wantstoplay' => $wantsToPlay,
        'bookings' => $bookings, 'usedBookingTime' => $usedBookingTime, 
        'courts' => $courts];   
    }

    public function getInvitationData(int $fixtureId, int $userId) : array
    {
        // Get data for asking user if they want to play    13
        $sql = "SELECT Fixtureid, Users.Userid, FirstName, LastName, FixtureDate, LEFT(FixtureTime, 5) AS FixtureTime
        FROM Fixtures, Users WHERE Fixtureid = :Fixtureid AND Userid = :Userid;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId, 'Userid' => $userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $row['FixtureDate'] = date("l jS",strtotime($row['FixtureDate']));
        return $row;
    }

    public function getPlayInvitations($fixtureId) : array
    {
        // Get information for creating invitation to play emails
        $sql = "SELECT FirstName, LastName, EmailAddress, FixtureDate, LEFT(FixtureTime, 5) AS FixtureTime
        FROM Fixtures, Users WHERE Fixtureid = :Fixtureid AND Userid = FixtureOwner;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $fixture = $stmt->fetch(\PDO::FETCH_ASSOC);
        $owner['FirstName'] = $fixture['FirstName'];
        $owner['LastName'] = $fixture['LastName'];
        $owner['EmailAddress'] = $fixture['EmailAddress'];
        $shortDate = date("l jS",strtotime($fixture['FixtureDate']));
        $time = $fixture['FixtureTime'];

        $sql="SELECT Users.Userid, FirstName, LastName, EmailAddress
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE WantsToPlay IS NULL AND Fixtureid = :Fixtureid 
        ORDER BY FirstName, LastName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $toList = $stmt->fetchall(\PDO::FETCH_ASSOC);

        $email['subject'] = "Tennis $shortDate";
        $email['owner'] = $owner;
        $email['fixtureTime'] = $time;
        return ['email' => $email, 'recipients' => $toList];
    }

    public function setInvitationsSent($fixtureId)
    {
        $sql = "UPDATE Fixtures SET InvitationsSent = TRUE WHERE Fixtureid = :Fixtureid;";
        $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
    }
}