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

    public function inBookingWindow($fixtureId) : bool
    {
        // Return true if the current time is inside the booking window for this fixture
        $sql = "SELECT FixtureDate FROM Fixtures WHERE Fixtureid = :Fixtureid;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $fixtureDate = $stmt->fetchColumn();
        $bookingTime = "07:30";
        $bookingDt = strtotime($fixtureDate . " " . $bookingTime);
        $nowDt = time();
        return $nowDt < $bookingDt and $nowDt > ($bookingDt - 7 * 86400);
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

    public function getWantToPlay($fixtureId) : array
    {
        // Return list of users who want to play and are currently not playing
        $sql = "SELECT Users.Userid, FirstName, LastName FROM Users, FixtureParticipants
        WHERE Users.Userid = FixtureParticipants.Userid AND Fixtureid = :Fixtureid
        AND WantsToPlay = TRUE AND IsPlaying = FALSE
        ORDER BY FirstName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $users = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function getWantToPlayCandidates($fixtureId) : array
    {
        // Return list of users who have not yet declared they want to play
        $sql = "SELECT Users.Userid, FirstName, LastName FROM Users, FixtureParticipants
        WHERE Users.Userid=FixtureParticipants.Userid AND Fixtureid = :Fixtureid
        AND WantsToPlay IS NULL
        ORDER BY FirstName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $users = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function setBookersPlaying($fixtureId)
    {
        // Set bookers to playing unless they have declared they don't want to play
        $sql = "UPDATE FixtureParticipants, CourtBookings
        SET WantsToPlay =  TRUE, IsPlaying = TRUE
        WHERE FixtureParticipants.Fixtureid = :Fixtureid        
        AND FixtureParticipants.Fixtureid = CourtBookings.Fixtureid
        AND FixtureParticipants.Userid = CourtBookings.UserId
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
        $sql = "SELECT Users.Userid, FirstName, LastName FROM Users, FixtureParticipants
        WHERE Users.Userid=FixtureParticipants.Userid AND Fixtureid = :Fixtureid
        ORDER BY FirstName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $users = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function getFixtureNonBookers($fixtureId) : array
    {
        // Return list of existing participants that have not booked
        $sql = "SELECT Users.Userid, FirstName, LastName FROM Users, FixtureParticipants
        WHERE Users.Userid=FixtureParticipants.Userid AND Fixtureid = :Fixtureid1
        AND Users.Userid NOT IN (SELECT Userid FROM CourtBookings WHERE Fixtureid = :Fixtureid2)
        ORDER BY FirstName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid1' => $fixtureId, 'Fixtureid2' => $fixtureId]);
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

    public function addCourtBooking($fixtureId, $bookerId, $time, $court)
    {
        $sql="INSERT INTO CourtBookings (Fixtureid, Userid, BookingTime, CourtNumber)
        VALUES (:Fixtureid, :Bookerid, :BookingTime, :CourtNumber);";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('Fixtureid', $fixtureId, \PDO::PARAM_INT);
        $stmt->bindParam('Bookerid', $bookerId, \PDO::PARAM_INT);
        $stmt->bindParam('BookingTime', $time, \PDO::PARAM_STR); 
        $stmt->bindParam('CourtNumber', $court, \PDO::PARAM_INT);
        $stmt->execute();
    }

    public function deleteCourtBooking($fixtureId, $userId, $time, $court)
    {
        $sql = "DELETE FROM CourtBookings WHERE Fixtureid = :Fixtureid AND Userid = :Userid
        AND BookingTime = :BookingTime AND CourtNumber = :CourtNumber;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('Fixtureid', $fixtureId, \PDO::PARAM_INT);
        $stmt->bindParam('Userid', $userId, \PDO::PARAM_INT);
        $stmt->bindParam('BookingTime', $time, \PDO::PARAM_STR); 
        $stmt->bindParam('CourtNumber', $court, \PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getAvailableCourts($fixtureId, $time) : array
    {
        // Return a list of available courts for the passed time
        $sql = "SELECT FixtureCourts FROM Fixtures WHERE Fixtureid = :Fixtureid;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $fixtureCourts = explode(",", str_replace(' ','',$row['FixtureCourts']));
        $sql = "SELECT CourtNumber FROM CourtBookings 
        WHERE Fixtureid = :Fixtureid AND BookingTime = :BookingTime
        ORDER BY CourtNumber;";
        $stmt = $this->pdo->prepare($sql);
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

    public function getFixture($fixtureId) : array
    {
        // Get Fixture data
        $sql="SELECT Fixtures.Seriesid, FirstName, LastName, 
        FixtureDate, FixtureTime, FixtureCourts, InvitationsSent
        FROM Fixtures, Users, FixtureSeries
        WHERE Fixtureid = :Fixtureid 
        AND Fixtures.FixtureOwner = Users.Userid 
        AND Fixtures.Seriesid = FixtureSeries.Seriesid;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $seriesId = $row['Seriesid'];
        $owner['FirstName'] = $row['FirstName'];
        $owner['LastName'] = $row['LastName'];
        $description = date("l jS \of F Y", strtotime($row['FixtureDate']));
        $fixtureTime = substr($row['FixtureTime'],0,5);
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
        $sql="SELECT Users.Userid, FirstName, LastName, AcceptTime
        FROM Users, FixtureParticipants
        WHERE Fixtureid = :Fixtureid AND Users.Userid=FixtureParticipants.Userid
        AND IsPlaying = TRUE
        ORDER BY AcceptTime, FirstName, LastName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $playerList = $stmt->fetchall(\PDO::FETCH_ASSOC);

        // Get reserves...
        $sql="SELECT Users.Userid, FirstName, LastName, AcceptTime
        FROM Users, FixtureParticipants
        WHERE Fixtureid = :Fixtureid AND Users.Userid = FixtureParticipants.Userid
        AND IsPlaying = FALSE AND WantsToPlay = TRUE
        ORDER BY AcceptTime, FirstName, LastName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $reserveList = $stmt->fetchall(\PDO::FETCH_ASSOC);
        
        // Get decliners...
        $sql="SELECT Users.Userid, FirstName, LastName
        FROM Users, FixtureParticipants
        WHERE Fixtureid = :Fixtureid AND Users.Userid = FixtureParticipants.Userid
        AND WantsToPlay = FALSE
        ORDER BY FirstName, LastName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $declineList = $stmt->fetchall(\PDO::FETCH_ASSOC);
        
        // Get abstainers...
        $sql="SELECT Users.Userid, FirstName, LastName
        FROM Users, FixtureParticipants
        WHERE Fixtureid = :Fixtureid AND Users.Userid = FixtureParticipants.Userid
        AND WantsToPlay IS NULL
        ORDER BY FirstName, LastName;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $abstainList = $stmt->fetchall(\PDO::FETCH_ASSOC);
        
        // Get court bookings into grid with columns (court, booking time, bookers)
        $bookingGrid[0][0] = "Court";
        $sql = "SELECT DISTINCT BookingTime FROM CourtBookings WHERE Fixtureid = :Fixtureid 
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
        WHERE Fixtureid = :Fixtureid ORDER BY CourtNumber;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $rows = $stmt->fetchall(\PDO::FETCH_ASSOC);
        $r=1;
        foreach ($rows as $row) {
            $bookingGrid[$r][0] = $row['CourtNumber'];
            for ($c=1;$c<=$bookersColumn;$c++) {$bookingGrid[$r][$c] = "-";}
            $r++;
        }

        $sql = "SELECT FirstName, LastName, CourtNumber, BookingTime FROM Users, CourtBookings
        WHERE Fixtureid = :Fixtureid AND Users.Userid = CourtBookings.Userid
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
        'players' => $playerList, 'reserves' => $reserveList, 'decliners' => $declineList,  'abstainers' => $abstainList,
        'bookingtimes' => $bookingTimes, 'time1' => $bookingTime1, 'time2' => $bookingTime2,
        'bookings' => $bookingViewGrid, 'courts' => $fixtureCourts];
        return $fixture;
    }

    public function getParticipantData($fixtureId, $userId) : array
    {
        $sql = "SELECT Users.Userid, FirstName, LastName, WantsToPlay, IsPlaying 
        FROM Users, FixtureParticipants
        WHERE Fixtureid = :Fixtureid AND FixtureParticipants.Userid = :Userid 
        AND Users.Userid = FixtureParticipants.Userid;";
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
        $sql = "SELECT CourtsBooked FROM FixtureParticipants
        WHERE Fixtureid = :Fixtureid AND Userid = :Userid;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId, 'Userid' => $userId]);
        return $stmt->fetchColumn();
    }
    
    public function getInvitationData(int $fixtureId, int $userId) : array
    {
        $sql = "SELECT Fixtureid, Users.Userid, FirstName, LastName, FixtureDate, FixtureTime
        FROM Fixtures, Users WHERE Fixtureid = :Fixtureid AND Userid = :Userid;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId, 'Userid' => $userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $row['FixtureDate'] = date("l jS",strtotime($row['FixtureDate']));
        $row['FixtureTime'] = substr($row['FixtureTime'],0,5);
        return $row;
    }

    public function getParticipantBookings(int $fixtureId, int $userId) : array
    {
        $sql = "SELECT CourtNumber, BookingTime FROM CourtBookings
        WHERE Fixtureid = :Fixtureid AND Userid = :Userid
        ORDER BY BookingTime;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId, 'Userid' => $userId]); 
        $rows = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $rows;
    }

    public function countParticipantBookings(int $fixtureId, int $userId) : int
    {
        $sql = "SELECT COUNT(CourtNumber) FROM CourtBookings
        WHERE Fixtureid = :Fixtureid AND Userid = :Userid;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId, 'Userid' => $userId]); 
        return $stmt->fetchColumn();
    }

    public function getBookingFormData(int $fixtureId, int $userId) : array
    {
        $fixture = $this->getFixture($fixtureId);
        $u = $this->getParticipantData($fixtureId, $userId);
        $brows = $this->getParticipantBookings($fixtureId, $userId);
        $bookings=null;
        $n=0;
        foreach ($brows as $b) {
            $bookings[$n]['court'] = $b['CourtNumber'];
            $bookings[$n]['time'] = substr($b['BookingTime'],0,5);
            $n++;
        }

        foreach ($fixture['bookingtimes'] as $time) {
            $courts[$time] = $this->getAvailableCourts($fixtureId, $time);
        }

        $usedBookingTime = "";
        if (is_null($bookings)===false and sizeof($bookings)==1) {
            $usedBookingTime = $bookings[0]['time'];
        }

        $isPlaying = $u['IsPlaying']?"Yes":"No";
        if (is_null($u['WantsToPlay'])) { $wantsToPlay = "Unknown"; }
        else { $wantsToPlay = $u['WantsToPlay']?"Yes":"No"; }
        return ['fixture' => $fixture, 'participant' => $u,
        'isplaying' => $isPlaying, 'wantstoplay' => $wantsToPlay,
        'bookings' => $bookings, 'usedBookingTime' => $usedBookingTime, 
        'courts' => $courts];   
    }

    public function getPlayInvitations($fixtureId) : array
    {
        $sql = "SELECT FirstName, LastName, EmailAddress, FixtureDate, FixtureTime
        FROM Fixtures, Users WHERE Fixtureid = :Fixtureid AND Userid = FixtureOwner;";
        $stmt = $this->pdo->runSQL($sql,['Fixtureid' => $fixtureId]);
        $fixture = $stmt->fetch(\PDO::FETCH_ASSOC);
        $owner['FirstName'] = $fixture['FirstName'];
        $owner['LastName'] = $fixture['LastName'];
        $owner['EmailAddress'] = $fixture['EmailAddress'];
        $shortDate = date("l jS",strtotime($fixture['FixtureDate']));
        $time = substr($fixture['FixtureTime'],0,5);

        $sql="SELECT Users.Userid, FirstName, LastName, EmailAddress
        FROM Users, FixtureParticipants
        WHERE WantsToPlay IS NULL 
        AND Fixtureid = :Fixtureid AND Users.Userid = FixtureParticipants.Userid
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