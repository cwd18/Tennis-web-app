<?php
declare(strict_types=1);

namespace TennisApp;

class Fixture
{
    protected Database $pdo;
    protected int $fixtureId;
    protected $base;

    public function __construct(Database $pdo, int $fixtureId)
    {
        $this->pdo = $pdo;
        $this->fixtureId = $fixtureId;
        $this->setBase();
    }

    private function setBase()
    {
        // Retrieve basic fixture data...
        $sql = "SELECT Fixtureid, Seriesid, 
        FixtureOwner, FirstName AS OwnerFirstName, LastName AS OwnerLastName, EmailAddress AS OwnerEmail,
        FixtureDate, LEFT(FixtureTime, 5) AS FixtureTime, 
        FixtureCourts, TargetCourts, InvitationsSent
        FROM Fixtures JOIN Users ON Fixtures.FixtureOwner = Users.Userid
        WHERE Fixtureid = :Fixtureid;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId]);
        $this->base = $stmt->fetch(\PDO::FETCH_ASSOC);
        $fixtureDt = strtotime($this->base['FixtureDate']);
        $this->base['description'] = date("l jS \of F Y", $fixtureDt);
        $this->base['shortDate'] = date("l jS", $fixtureDt);
    }
    
    public function getSeriesid() : int
    {
        return $this->base['Seriesid'];
    }

    public function getBasicFixtureData() : array
    {
        return $this->base;
    }

    public function inBookingWindow() : int
    {
        // Return 0 if the current time is inside the booking window for this fixture
        // Return -1 if the current time is earlier than the booking window
        // Return +1 if the current time is later than the booking window
        // The booking window is the week before 07:30 on the fixture data
        $fixtureDate = $this->base['FixtureDate'];
        $bookingTime = "07:30";
        $bookingDt2 = strtotime($fixtureDate . " " . $bookingTime);
        $bookingDt1 = $bookingDt2 - 7 * 24 * 60 * 60; // 7 days earlier
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

    public function updateBasicFixtureData(string $time, string $courts, string $targetCourts)
    {
        $sql = "UPDATE Fixtures SET FixtureTime = :FixtureTime, 
        FixtureCourts = :FixtureCourts, TargetCourts = :TargetCourts, 
        WHERE Fixtureid = :Fixtureid;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('Fixtureid', $this->fixtureId, \PDO::PARAM_INT);
        $stmt->bindParam('FixtureTime', $time, \PDO::PARAM_STR); 
        $stmt->bindParam('FixtureCourts', $courts, \PDO::PARAM_STR);
        $stmt->bindParam('TargetCourts', $targetCourts, \PDO::PARAM_STR);
        $stmt->execute();
        $this->setBase();
    }

    public function deleteFixture()
    {
        $sql = "DELETE FROM CourtBookings WHERE Fixtureid = :Fixtureid;";
        $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId]);
        $sql = "DELETE FROM FixtureParticipants WHERE Fixtureid = :Fixtureid;";
        $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId]);
        $sql = "DELETE FROM Fixtures WHERE Fixtureid = :Fixtureid;";
        $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId]);
    }

    public function getFixtureCandidates() : array
    {
        // Return list of possible candidate participants to add to the fixture, 
        // which excludes existing participants
        $sql = "SELECT Userid, FirstName, LastName FROM Users
        WHERE Users.Userid NOT IN (SELECT Userid FROM FixtureParticipants WHERE Fixtureid = :Fixtureid)
        ORDER BY FirstName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId]);
        $users = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function getFixtureParticipants() : array
    {
        // Return list of fixture participants
        $sql = "SELECT Users.Userid, FirstName, LastName 
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE  Fixtureid = :Fixtureid
        ORDER BY FirstName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId]);
        $users = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function getWantToPlay() : array
    {
        // Return list of users who want to play and are currently not playing
        $sql = "SELECT Users.Userid, FirstName, LastName 
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE Fixtureid = :Fixtureid AND WantsToPlay = TRUE AND IsPlaying = FALSE
        ORDER BY FirstName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId]);
        $users = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function getWantToPlayCandidates() : array
    {
        // Return list of users who have not yet declared they want to play
        $sql = "SELECT Users.Userid, FirstName, LastName 
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE Fixtureid = :Fixtureid AND WantsToPlay IS NULL
        ORDER BY FirstName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId]);
        $users = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function getParticipantData($userId) : array|bool
    {
        $sql = "SELECT Users.Userid, FirstName, LastName, WantsToPlay, IsPlaying, CourtsBooked 
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE Fixtureid = :Fixtureid AND FixtureParticipants.Userid = :Userid;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId, 'Userid' => $userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row;
    }

    public function getWantsToPlay($userId) : ?int
    {
        $sql = "SELECT WantsToPlay FROM FixtureParticipants
        WHERE Fixtureid = :Fixtureid AND Userid = :Userid;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId, 'Userid' => $userId]);
        return $stmt->fetchColumn();
    }
    
    public function getCourtsBooked($userId) : ?int
    {
        // Return the value of the CourtsBooked column, which is initially NULL but sent to FALSE
        // when user has indicated they did not book any court
        $sql = "SELECT CourtsBooked FROM FixtureParticipants
        WHERE Fixtureid = :Fixtureid AND Userid = :Userid;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId, 'Userid' => $userId]);
        return $stmt->fetchColumn();
    }
    
    public function setCourtsBooked(int $userId, bool $val)
    {
        $sql = "UPDATE FixtureParticipants SET CourtsBooked = :val 
        WHERE Fixtureid = :Fixtureid AND Userid = :Userid;";
        $this->pdo->runSQL($sql, ['val' => $val, 'Fixtureid' => $this->fixtureId, 'Userid' => $userId]);
    }
    
    public function setAutoPlaying()
    {
        // Automatically set playing
        $this->resetPlaying();
        $capacity = $this->getCapacity();
        if (count($capacity) == 0) {
            return;
        }
        $numPlayers = 1000; // initial high enough value
        foreach ($capacity as $count) {
            if ($count < $numPlayers) {
                $numPlayers = $count;
            }
        }
        $numPlayers *= 4;
        $sql = "UPDATE FixtureParticipants
        SET IsPlaying = TRUE
        WHERE FixtureParticipants.Fixtureid = :Fixtureid        
        AND WantsToPlay = TRUE AND IsPlaying = FALSE
        ORDER BY CourtsBooked DESC, AcceptTime LIMIT $numPlayers;";
        $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId]);
    }

    public function setPlaying($userIds)
    {
        $sql = "UPDATE FixtureParticipants SET IsPlaying = TRUE
        WHERE Fixtureid = :Fixtureid AND Userid = :Userid;";
        $stmt = $this->pdo->prepare($sql);
        foreach ($userIds as $userId) {
            $stmt->execute(['Fixtureid' => $this->fixtureId, 'Userid' => $userId]);        
        }
    }

    public function setWantsToPlay($userIds)
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
                $stmt->execute(['Fixtureid' => $this->fixtureId, 'Userid' => $userId, 'DTnow' => $dateTimeNow]);        
            } 
        } else {
            $stmt->execute(['Fixtureid' => $this->fixtureId, 'Userid' => $userIds, 'DTnow' => $dateTimeNow]);        
        }
    }

    public function setWantsNotToPlay($userId)
    {
        $sql = "UPDATE FixtureParticipants SET WantsToPlay = FALSE, IsPlaying = FALSE 
        WHERE Fixtureid = :Fixtureid AND Userid = :Userid;";
        $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId, 'Userid' => $userId]);
    }

    public function resetPlaying()
    {
        $sql = "UPDATE FixtureParticipants SET IsPlaying = FALSE
        WHERE Fixtureid = :Fixtureid;";
        $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId]);
    }

    public function addUsers($userIds)
    {
        // Add users to the fixture
        $sql = "INSERT INTO FixtureParticipants (Fixtureid, Userid) 
        VALUES (:Fixtureid, :Userid);";
        $stmt = $this->pdo->prepare($sql);
        foreach ($userIds as $userId) {
            $stmt->execute(['Fixtureid' => $this->fixtureId, 'Userid' => $userId]);
        }
    }

    public function getFixtureUsers() : array
    {
        // Return list of existing participants
        $sql = "SELECT Users.Userid, FirstName, LastName 
        FROM Users JOIN FixtureParticipants ON Users.Userid=FixtureParticipants.Userid
        WHERE Fixtureid = :Fixtureid
        ORDER BY FirstName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId]);
        $users = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function getFixtureNonBookers($type) : array
    {
        // Return list of existing participants that have not booked or requested
        $sql = "SELECT Users.Userid, FirstName, LastName 
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid 
        WHERE Fixtureid = :Fixtureid1
        AND Users.Userid NOT IN 
        (SELECT Userid FROM CourtBookings WHERE BookingType = :BookingType AND Fixtureid = :Fixtureid2)
        ORDER BY FirstName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid1' => $this->fixtureId, 'BookingType' => $type, 'Fixtureid2' => $this->fixtureId]);
        $users = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function deleteFixtureUsers($userIds)
    {
        // Delete specified users from this fixture 
        $sql = "DELETE FROM FixtureParticipants WHERE Fixtureid = :Fixtureid AND Userid = :Userid;";
        $stmt = $this->pdo->prepare($sql);
        foreach ($userIds as $userId) {
            $stmt->execute(['Fixtureid' => $this->fixtureId, 'Userid' => $userId]);
            }
    }

    public function addCourtBooking($bookerId, $time, $court, $type)
    {
        $sql="INSERT INTO CourtBookings (Fixtureid, Userid, BookingTime, CourtNumber, BookingType)
        VALUES (:Fixtureid, :Bookerid, :BookingTime, :CourtNumber, :BookingType);";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('Fixtureid', $this->fixtureId, \PDO::PARAM_INT);
        $stmt->bindParam('Bookerid', $bookerId, \PDO::PARAM_INT);
        $stmt->bindParam('BookingTime', $time, \PDO::PARAM_STR); 
        $stmt->bindParam('CourtNumber', $court, \PDO::PARAM_INT);
        $stmt->bindParam('BookingType', $type, \PDO::PARAM_STR); 
        $stmt->execute();
    }

    public function deleteCourtBooking($userId, $time, $court, $type)
    {
        $sql = "DELETE FROM CourtBookings WHERE BookingType = :BookingType 
        AND Fixtureid = :Fixtureid AND Userid = :Userid
        AND BookingTime = :BookingTime AND CourtNumber = :CourtNumber;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('Fixtureid', $this->fixtureId, \PDO::PARAM_INT);
        $stmt->bindParam('Userid', $userId, \PDO::PARAM_INT);
        $stmt->bindParam('BookingTime', $time, \PDO::PARAM_STR); 
        $stmt->bindParam('CourtNumber', $court, \PDO::PARAM_INT);
        $stmt->bindParam('BookingType', $type, \PDO::PARAM_STR); 
        $stmt->execute();
    }

    private function getAvailableCourts($time, $type) : array
    {
        // Return a list of available courts for the passed time
        $fixtureCourts = explode(",", str_replace(' ','',$this->base['FixtureCourts']));
        $sql = "SELECT CourtNumber FROM CourtBookings 
        WHERE BookingType = :BookingType AND Fixtureid = :Fixtureid AND BookingTime = :BookingTime
        ORDER BY CourtNumber;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('BookingType', $type, \PDO::PARAM_STR); 
        $stmt->bindParam('Fixtureid', $this->fixtureId, \PDO::PARAM_INT);
        $stmt->bindParam('BookingTime', $time, \PDO::PARAM_STR); 
        $stmt->execute();
        $excludedCourts[] = NULL;
        while ($courtNumber = $stmt->fetchColumn()) {
            $excludedCourts[] = $courtNumber;
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

    public function getFixtureData() : array
    {
        // Augment Fixture data
        $stmt = $this->pdo->runSQL(
            "SELECT FirstName, LastName FROM Users WHERE Userid = :Userid",
            ['Userid' => $this->base['FixtureOwner']]);
        $ownerName = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Calculate booking time slots
        $bookingBase = $this->base['FixtureTime'];
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
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId]);
        $playerList = $stmt->fetchall(\PDO::FETCH_ASSOC);

        // Get people who have accepted but not marked to play...
        $sql="SELECT DISTINCT Users.Userid, FirstName, LastName, AcceptTime, CourtsBooked
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE FixtureParticipants.Fixtureid = :Fixtureid 
        AND IsPlaying = FALSE AND WantsToPlay = TRUE
        ORDER BY CourtsBooked DESC, AcceptTime, FirstName, LastName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId]);
        $reserveList = $stmt->fetchall(\PDO::FETCH_ASSOC);

        // Get decliners...
        $sql="SELECT Users.Userid, FirstName, LastName
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE Fixtureid = :Fixtureid 
        AND WantsToPlay = FALSE
        ORDER BY FirstName, LastName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId]);
        $declineList = $stmt->fetchall(\PDO::FETCH_ASSOC);
        
        // Get abstainers (people who haven't responded to invitation)...
        $sql="SELECT Users.Userid, FirstName, LastName
        FROM Users, FixtureParticipants
        WHERE Fixtureid = :Fixtureid AND Users.Userid = FixtureParticipants.Userid
        AND WantsToPlay IS NULL
        ORDER BY FirstName, LastName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId]);
        $abstainList = $stmt->fetchall(\PDO::FETCH_ASSOC);

        // Are we in the booking window for this fixture
        $inBookingWindow = $this->inBookingWindow();

        // get requested bookings
        $requestedBookings = $this->getRequestedBookings();

        $capacity = $this->getCapacity();
        
        // Get court bookings into grid with columns (court, booking time, bookers)
        $bookingGrid[0][0] = "Court";
        $sql = "SELECT DISTINCT BookingTime FROM CourtBookings 
        WHERE BookingType = 'Booked' AND Fixtureid = :Fixtureid 
        ORDER BY BookingTime;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId]);
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
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId]);
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
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId]);
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
        $fixture = ['base' => $this->base, 'ownerName' => $ownerName,
        'players' => $playerList, 'reserves' => $reserveList, 
        'decliners' => $declineList,  'abstainers' => $abstainList, 'capacity' => $capacity,
        'inBookingWindow' => $inBookingWindow, 'requestedBookings' =>$requestedBookings,
        'bookingtimes' => $bookingTimes, 'time1' => $bookingTime1, 'time2' => $bookingTime2,
        'bookings' => $bookingViewGrid];
        return $fixture;
    }

    public function getParticipantBookings(int $userId, $type) : array
    {
        $sql = "SELECT CourtNumber, BookingTime FROM CourtBookings
        WHERE BookingType = :BookingType AND Fixtureid = :Fixtureid AND Userid = :Userid
        ORDER BY BookingTime;";
        $stmt = $this->pdo->runSQL($sql,['BookingType' => $type, 'Fixtureid' => $this->fixtureId, 'Userid' => $userId]); 
        $bookings = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $bookings;
    }

    public function getRequestedBookings() : array
    {
        // Get the current list of booking requests for this fixture
        // Returns an empty array if no requests yet
        $sql = "SELECT Users.Userid, FirstName, LastName, CourtNumber, LEFT(BookingTime, 5) AS BookingTime 
        FROM CourtBookings JOIN Users ON Users.Userid = CourtBookings.Userid
        WHERE BookingType = 'Request' AND Fixtureid = :Fixtureid 
        ORDER BY BookingTime, CourtNumber;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId]); 
        $bookingRequests = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $bookingRequests;
    }

    public function countParticipantBookings(int $userId, $type) : int
    {
        $sql = "SELECT COUNT(CourtNumber) FROM CourtBookings
        WHERE BookingType = :BookingType AND Fixtureid = :Fixtureid AND Userid = :Userid;";
        $stmt = $this->pdo->runSQL($sql,['BookingType' => $type, 'Fixtureid' => $this->fixtureId, 'Userid' => $userId]); 
        return (int)$stmt->fetchColumn();
    }

    public function getBookingFormData(int $userId, $type) : array
    {
        $fixture = $this->getFixtureData();
        $u = $this->getParticipantData($userId);
        $brows = $this->getParticipantBookings($userId, $type);
        $bookings=null;
        $n=0;
        foreach ($brows as $b) {
            $bookings[$n]['court'] = $b['CourtNumber'];
            $bookings[$n]['time'] = substr($b['BookingTime'],0,5);
            $n++;
        }

        foreach ($fixture['bookingtimes'] as $time) {
            $courts[$time] = $this->getAvailableCourts($time, $type);
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

    public function getInvitationData(int $userId) : array
    {
        // Get data for asking user if they want to play om participant web page
        $r['Fixtureid'] = $this->base['Fixtureid'];
        $r['Userid'] = $userId;
        $r['FixtureDate'] = $this->base['FixtureDate'];
        $r['FixtureTime'] = $this->base['FixtureTime'];
        $sql = "SELECT FirstName, LastName FROM Users WHERE Userid = :Userid;";
        $stmt = $this->pdo->runSQL($sql,['Userid' => $userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $r['FirstName'] = $row['FirstName'];
        $r['LastName'] = $row['LastName'];
        return $r;
    }

    public function getWannaPlayRecipients() : array
    {
        // Get recipient list for creating invitation to play emails
        $sql="SELECT Users.Userid, FirstName, LastName, EmailAddress
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE WantsToPlay IS NULL AND Fixtureid = :Fixtureid
        ORDER BY FirstName, LastName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId]);
        return $stmt->fetchall(\PDO::FETCH_ASSOC);
    }

    public function getBookingRequestRecipients() : array
    {
        // Get recipients for creating booking request emails
        $sql="SELECT Users.Userid, FirstName, LastName, EmailAddress
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE WantsToPlay = TRUE AND Fixtureid = :Fixtureid
        ORDER BY FirstName, LastName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId]);
        return $stmt->fetchall(\PDO::FETCH_ASSOC);
    }
    
    public function setInvitationsSent()
    {
        $sql = "UPDATE Fixtures SET InvitationsSent = TRUE WHERE Fixtureid = :Fixtureid;";
        $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId]);
        $this->base['InvitationsSent'] = TRUE;
    }

    public function getInvitationsSent() : bool
    {
        return (bool)$this->base['InvitationsSent'];
    }

    public function createBookingRequests() 
    {
        // Automatically create booking requests
        // Delete any existing requests
        $sql = "DELETE FROM CourtBookings WHERE Fixtureid = :Fixtureid AND BookingType = 'Request';";
        $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
        // Get users
        $sql = "SELECT Userid FROM FixtureParticipants WHERE Fixtureid = :Fixtureid";
        $userIds = $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId])->fetchall(\PDO::FETCH_ASSOC);
        // Get times/courts to book
        $sql = "SELECT LEFT(FixtureTime, 5) AS FixtureTime, TargetCourts 
        FROM Fixtures WHERE FixtureId = :Fixtureid;";
        $row = $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId])->fetchall(\PDO::FETCH_ASSOC);
        // Create requests
        $range = explode("-", $row['TargetCourts']);
        $time[0] = $row['FixtureTime']; 
        $time[1] = date('H:i', strtotime($time[0]) + 60 * 60);
        $u = 0;
        for ($c = $range[0]; $c<= $range[1]; $c++) {
            foreach ($time as $t) {
                if ($u >= count($userIds)) {
                    return; // run out of users
                }
                $userId = $userIds[$u++];
                $this->addCourtBooking($userId, $t, $c, 'Request');
            }
        }

    }

    public function getCapacity() : array
    {
    // return the number of players that can play for 2 hours given booked courts
    $sql ="SELECT LEFT(BookingTime, 5) AS BookingTime, COUNT(CourtNumber) AS NumCourts
    FROM  CourtBookings WHERE FixtureId = :Fixtureid AND BookingType = 'Booked'
    GROUP BY BookingTime
    ORDER BY BookingTime;";
    $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $this->fixtureId]);
    $rows = $stmt->fetchall(\PDO::FETCH_ASSOC);
    $numTimeSlots = count($rows);
    if ($numTimeSlots == 2) {
        $capacity[$rows[0]['BookingTime']] = min($rows[0]['NumCourts'], $rows[1]['NumCourts']);
    } else if ($numTimeSlots > 2) {
        $capacity[$rows[0]['BookingTime']] = min($rows[0]['NumCourts'], $rows[1]['NumCourts']);
        $capacity[$rows[1]['BookingTime']] = min($rows[1]['NumCourts'], $rows[2]['NumCourts']);
    } else {
        $capacity = [];
    }
    return $capacity;
    }

}