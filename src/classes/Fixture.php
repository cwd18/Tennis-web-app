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

    private function courtList($rangeList): array
    {
        // Return an array of court numbers from a comma separated list of ranges
        $rangeList = str_replace(' ', '', $rangeList);
        $ranges = explode(",", $rangeList);
        $courts = [];
        foreach ($ranges as $rangeStr) {
            $range = explode("-", $rangeStr);
            $n1 = (int)$range[0];
            $n2 = (int)$range[1];
            for ($n = $n1; $n <= $n2; $n++) {
                $courts[] = $n;
            }
        }
        return $courts;
    }

    private function setBase()
    {
        // Retrieve basic fixture data...
        $sql = "SELECT Fixtureid, Seriesid, FixtureOwner, 
        FirstName AS OwnerFirstName, LastName AS OwnerLastName, EmailAddress AS OwnerEmail,
        FixtureDate, LEFT(FixtureTime, 5) AS FixtureTime, 
        FixtureCourts, TargetCourts, InvitationsSent
        FROM Fixtures JOIN Users ON Fixtures.FixtureOwner = Users.Userid
        WHERE Fixtureid = :Fixtureid;";
        $stmt = $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
        $this->base = $stmt->fetch(\PDO::FETCH_ASSOC);
        $fixtureDt = strtotime($this->base['FixtureDate']);
        $bookingDt = $fixtureDt - 7 * 24 * 60 * 60; // 7 days earlier
        $this->base['bookingDate'] = date("l jS", $bookingDt);
        $this->base['bookingDateYmd'] = date("Y-m-d", $bookingDt);
        $this->base['description'] = date("l jS \of F", $fixtureDt);
        $this->base['shortDate'] = date("l jS", $fixtureDt);
        $this->base['inBookingWindow'] = $this->inBookingWindow();

        // Calculate booking time slots
        $bookingBase = $this->base['FixtureTime'];
        $bookingRange = 2;
        if ($bookingBase == '08:30') {
            $bookingBase = '07:30';
            $bookingRange = 3;
        }
        for ($n = 0; $n < $bookingRange; $n++) {
            $this->base['bookingTimes'][$n] = date("H:i", strtotime($bookingBase) + $n * 3600);
        }
    }

    public function getSeriesid(): int
    {
        return $this->base['Seriesid'];
    }

    public function getBasicFixtureData(): array
    {
        return $this->base;
    }

    public function inBookingWindow(): int
    {
        // Return 0 if the current time is inside the booking window for this fixture
        // Return -1 if the current time is earlier than the booking window
        // Return +1 if the current time is later than the booking window
        // The booking window is the week before 07:30 on the fixture data
        $fixtureDate = $this->base['FixtureDate'];
        $london = new \DateTimeZone('Europe/London');
        $bookingTimeEnd = new \DateTime("$fixtureDate 07:30", $london);
        $bookingDt2 = $bookingTimeEnd->getTimestamp();
        $bookingDt1 = $bookingDt2 - 7 * 24 * 60 * 60; // 7 days earlier
        $now = new \DateTime("now", $london);
        $nowDt = $now->getTimestamp();
        if ($nowDt < $bookingDt1) {
            $r = -1;
        } else if ($nowDt > $bookingDt2) {
            $r = 1;
        } else {
            $r = 0;
        }
        return $r;
    }

    public function updateBasicFixtureData(string $time, string $courts, string $targetCourts)
    {
        $sql = "UPDATE Fixtures SET FixtureTime = :FixtureTime, 
        FixtureCourts = :FixtureCourts, TargetCourts = :TargetCourts 
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
        $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
        $sql = "DELETE FROM FixtureParticipants WHERE Fixtureid = :Fixtureid;";
        $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
        $sql = "DELETE FROM Fixtures WHERE Fixtureid = :Fixtureid;";
        $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
    }

    public function getFixtureCandidates(): array
    {
        // Return list of possible candidate participants to add to the fixture, 
        // which excludes existing participants
        $sql = "SELECT Userid, FirstName, LastName FROM Users
        WHERE Users.Userid NOT IN (SELECT Userid FROM FixtureParticipants WHERE Fixtureid = :Fixtureid)
        ORDER BY FirstName;";
        $stmt = $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
        $users = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function getWantToPlay(): array
    {
        // Return list of users who want to play and are currently not playing
        $sql = "SELECT Users.Userid, FirstName, LastName 
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE Fixtureid = :Fixtureid AND WantsToPlay = TRUE AND IsPlaying = FALSE
        ORDER BY FirstName;";
        $stmt = $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
        $users = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function getWantToPlayCandidates(): array
    {
        // Return list of users who have not yet declared they want to play
        $sql = "SELECT Users.Userid, FirstName, LastName 
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE Fixtureid = :Fixtureid AND WantsToPlay IS NULL
        ORDER BY FirstName;";
        $stmt = $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
        $users = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function getParticipantData($userId): array|bool
    {
        $sql = "SELECT Users.Userid, FirstName, LastName, WantsToPlay, IsPlaying, CourtsBooked 
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE Fixtureid = :Fixtureid AND FixtureParticipants.Userid = :Userid;";
        $stmt = $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId, 'Userid' => $userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row;
    }

    public function getWantsToPlay($userId): ?int
    {
        $sql = "SELECT WantsToPlay FROM FixtureParticipants
        WHERE Fixtureid = :Fixtureid AND Userid = :Userid;";
        $stmt = $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId, 'Userid' => $userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row['WantsToPlay']; // null, zero, or one
    }

    private function getCourtsBooked($userId): ?int
    {
        // Return the value of the CourtsBooked column, which remembers the highest number of courts booked
        $sql = "SELECT CourtsBooked FROM FixtureParticipants
        WHERE Fixtureid = :Fixtureid AND Userid = :Userid;";
        $stmt = $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId, 'Userid' => $userId]);
        return $stmt->fetchColumn();
    }

    public function setCourtsBooked(int $userId)
    {
        // Ratchet up the value of the CourtsBooked column if the user 
        // has booked more courts than the current value
        $count = $this->countParticipantBookings($userId, 'Booked');
        $courtsBooked = $this->getCourtsBooked($userId);
        if ($courtsBooked < $count) {
            $sql = "UPDATE FixtureParticipants SET CourtsBooked = :val 
            WHERE Fixtureid = :Fixtureid AND Userid = :Userid;";
            $this->pdo->runSQL($sql, ['val' => $count, 'Fixtureid' => $this->fixtureId, 'Userid' => $userId]);
        }
    }

    private function countWantsToPlay(): int
    {
        // Return the number of participants who have declared they want to play
        $sql = "SELECT COUNT(Userid) FROM FixtureParticipants
        WHERE Fixtureid = :Fixtureid AND WantsToPlay = TRUE;";
        $stmt = $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
        return (int)$stmt->fetchColumn();
    }

    public function setAutoPlaying()
    {
        // Automatically set who is playing based on the number of courts available
        // at the current start time of the fixture

        $this->resetPlaying(); // set nobody playing

        // Calculate number of courts available
        $capacity = $this->getCapacity();
        if (count($capacity) == 0) {
            return;
        } // no courts available
        $numCourts = $capacity[$this->base['FixtureTime']];

        // Calculate how many people can play, which must be even
        $numWantsToPlay = $this->countWantsToPlay();
        $numPlayers = min(4 * $numCourts, $numWantsToPlay - $numWantsToPlay % 2);

        // Set playing for the first $numPlayers who want to play, in priortiy order
        $sql = "UPDATE FixtureParticipants
        SET IsPlaying = TRUE
        WHERE FixtureParticipants.Fixtureid = :Fixtureid        
        AND WantsToPlay = TRUE
        ORDER BY CourtsBooked DESC, AcceptTime LIMIT $numPlayers;";
        $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
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
        $now = new \DateTime("now", new \DateTimeZone('Europe/London'));
        $dateTimeNow = $now->format("Y-m-d H:i:s");
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
        $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId, 'Userid' => $userId]);
    }

    public function resetPlaying()
    {
        $sql = "UPDATE FixtureParticipants SET IsPlaying = FALSE
        WHERE Fixtureid = :Fixtureid;";
        $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
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

    public function getFixtureUsers(): array
    {
        // Return list of existing participants
        $sql = "SELECT Users.Userid, FirstName, LastName, ShortName 
        FROM Users JOIN FixtureParticipants ON Users.Userid=FixtureParticipants.Userid
        WHERE Fixtureid = :Fixtureid
        ORDER BY FirstName;";
        $stmt = $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
        $users = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function getFixtureNonBookers($type): array
    {
        // Return list of existing participants that have not booked or requested
        $sql = "SELECT Users.Userid, FirstName, LastName 
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid 
        WHERE Fixtureid = :Fixtureid1
        AND Users.Userid NOT IN 
        (SELECT Userid FROM CourtBookings WHERE BookingType = :BookingType AND Fixtureid = :Fixtureid2)
        ORDER BY FirstName;";
        $stmt = $this->pdo->runSQL($sql, ['Fixtureid1' => $this->fixtureId, 'BookingType' => $type, 'Fixtureid2' => $this->fixtureId]);
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

    public function setParticipantBookings(int $userId, array $bookings, $type = 'Booked')
    {
        // Set court bookings for a participant
        $this->pdo->beginTransaction();
        // Delete existing bookings for this user
        $sql = "DELETE FROM CourtBookings WHERE BookingType = :BookingType 
        AND Fixtureid = :Fixtureid AND Userid = :Userid;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('Fixtureid', $this->fixtureId, \PDO::PARAM_INT);
        $stmt->bindParam('Userid', $userId, \PDO::PARAM_INT);
        $stmt->bindParam('BookingType', $type, \PDO::PARAM_STR);
        $stmt->execute();

        // Add the new user bookings
        $sql = "INSERT INTO CourtBookings (Fixtureid, Userid, BookingTime, CourtNumber, BookingType)
        VALUES (:Fixtureid, :Bookerid, :BookingTime, :CourtNumber, :BookingType);";
        $stmt = $this->pdo->prepare($sql);
        foreach ($bookings as $booking) {
            if ($booking['court'] == 0) {
                continue;
            }
            $stmt->bindParam('Fixtureid', $this->fixtureId, \PDO::PARAM_INT);
            $stmt->bindParam('Bookerid', $userId, \PDO::PARAM_INT);
            $stmt->bindParam('BookingTime', $booking['time'], \PDO::PARAM_STR);
            $stmt->bindParam('CourtNumber', $booking['court'], \PDO::PARAM_INT);
            $stmt->bindParam('BookingType', $type, \PDO::PARAM_STR);
            $stmt->execute();
        }
        $this->pdo->commit();
    }

    private function addCourtBooking($bookerId, $time, $court, $type)
    {
        $sql = "INSERT INTO CourtBookings (Fixtureid, Userid, BookingTime, CourtNumber, BookingType)
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

    private function getAvailableCourts($userId, $time, $type): array
    {
        // Return a list of available courts for this user at the passed time
        $sql = "SELECT CourtNumber FROM CourtBookings 
        WHERE BookingType = :BookingType AND Fixtureid = :Fixtureid AND BookingTime = :BookingTime
        AND Userid != :Userid
        ORDER BY CourtNumber;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam('BookingType', $type, \PDO::PARAM_STR);
        $stmt->bindParam('Fixtureid', $this->fixtureId, \PDO::PARAM_INT);
        $stmt->bindParam('BookingTime', $time, \PDO::PARAM_STR);
        $stmt->bindParam('Userid', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        $excludedCourts[] = [];
        while ($courtNumber = $stmt->fetchColumn()) {
            $excludedCourts[] = $courtNumber;
        }
        $courts[0] = 0; // prepend a zero to the list of available courts
        $fixtureCourts = $this->courtList($this->base['FixtureCourts']);
        foreach ($fixtureCourts as $court) {
            $ok = TRUE;
            foreach ($excludedCourts as $excludedCourt) {
                if ($court == $excludedCourt) {
                    $ok = FALSE;
                    break;
                }
            }
            if ($ok) {
                $courts[] = $court;
            }
        }
        return $courts;
    }

    public function getPlayerLists(): array
    {
        // Get players...
        $sql = "SELECT DISTINCT Users.Userid, ShortName, AcceptTime, CourtsBooked
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE FixtureParticipants.Fixtureid = :Fixtureid 
        AND IsPlaying = TRUE
        ORDER BY CourtsBooked DESC, AcceptTime, ShortName;";
        $stmt = $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
        $playerLists['players'] = $stmt->fetchall(\PDO::FETCH_ASSOC);

        // Get people who have accepted but not marked to play...
        $sql = "SELECT DISTINCT Users.Userid, ShortName, AcceptTime, CourtsBooked
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE FixtureParticipants.Fixtureid = :Fixtureid 
        AND IsPlaying = FALSE AND WantsToPlay = TRUE
        ORDER BY CourtsBooked DESC, AcceptTime, ShortName;";
        $stmt = $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
        $playerLists['reserves'] = $stmt->fetchall(\PDO::FETCH_ASSOC);

        // Get decliners...
        $sql = "SELECT Users.Userid, ShortName
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE Fixtureid = :Fixtureid 
        AND WantsToPlay = FALSE
        ORDER BY ShortName;";
        $stmt = $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
        $playerLists['decliners'] = $stmt->fetchall(\PDO::FETCH_ASSOC);

        // Get abstainers (people who haven't responded to invitation)...
        $sql = "SELECT Users.Userid,ShortName
        FROM Users, FixtureParticipants
        WHERE Fixtureid = :Fixtureid AND Users.Userid = FixtureParticipants.Userid
        AND WantsToPlay IS NULL
        ORDER BY ShortName;";
        $stmt = $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
        $playerLists['abstainers'] = $stmt->fetchall(\PDO::FETCH_ASSOC);

        return $playerLists;
    }

    public function getAbsentBookers(): array
    {
        // Return list of bookers who do not want to play
        $sql = "SELECT Users.Userid, ShortName FROM Users 
        JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid 
        AND FixtureParticipants.Fixtureid = :F1 AND WantsToPlay = FALSE
        JOIN CourtBookings ON Users.Userid = CourtBookings.Userid
        AND BookingType = 'Booked' AND CourtBookings.Fixtureid = :F2
        ORDER BY ShortName;";
        $stmt = $this->pdo->runSQL($sql, ['F1' => $this->fixtureId, 'F2' => $this->fixtureId]);
        $absentBookers = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $absentBookers;
    }

    public function getBookingViewGrid(): array
    {
        // Get court bookings into grid with 2 or 3 booking time columns and a bookers bookers column)

        $bookingGrid[0][0] = "Court"; // first column is court number

        // Get bookings
        $sql = "SELECT ShortName, CourtNumber, LEFT(BookingTime,5) AS BookingTime FROM Users
        JOIN CourtBookings ON Users.Userid = CourtBookings.Userid
        WHERE BookingType = 'Booked' AND Fixtureid = :Fixtureid 
        ORDER BY CourtNumber, BookingTime;";
        $stmt = $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
        $rows = $stmt->fetchall(\PDO::FETCH_ASSOC);
        if (count($rows) == 0) { // no bookings
            $bookingViewGrid[0][0] = "None";
            return $bookingViewGrid;
        }

        // Create first row of column headings
        foreach ($rows as $row) {
            $bookedTimes[] = (string)$row['BookingTime'];
        }
        $uniqueTimes = array_unique($bookedTimes);
        sort($uniqueTimes);
        $c = 1;
        foreach ($uniqueTimes as $time) {
            $bookingGrid[0][$c++] = (string)$time;
        }
        $bookersColumn = $c;
        $bookingGrid[0][$bookersColumn] = 'Bookers';

        // Get court numbers and populate first column
        foreach ($rows as $row) {
            $bookedCourts[] = (string)$row['CourtNumber'];
        }
        $uniqueCourts = array_unique($bookedCourts);
        sort($uniqueCourts);
        $r = 1;
        foreach ($uniqueCourts as $court) {
            $bookingGrid[$r][0] = (string)$court;
            for ($c = 1; $c <= $bookersColumn; $c++) {
                $bookingGrid[$r][$c] = '-';
            } // initialise cells to '-'
            $r++;
        }
        $numRows = $r;

        // populate grid with bookings and add bookers to bookers column
        foreach ($rows as $row) {
            $name = $row['ShortName'];
            for ($r = 1; strcmp($bookingGrid[$r][0], (string)$row['CourtNumber']) != 0; $r++) { // match grid row
                if ($r == $numRows) {
                    return $bookingGrid;
                } // something wrong
            }
            for ($c = 1; strcmp($bookingGrid[0][$c], $row['BookingTime']) != 0; $c++) {  // match grid column
                if ($c == $bookersColumn) {
                    return $bookingGrid;
                } // something wrong
            }
            $bookingGrid[$r][$c] = (string)$row['CourtNumber'];
            if (strcmp($bookingGrid[$r][$bookersColumn], '-') == 0) {
                $bookingGrid[$r][$bookersColumn] = $name;
            } else {
                $names = explode(", ", $bookingGrid[$r][$bookersColumn]);
                if (strcmp($names[count($names) - 1], $name) != 0) {
                    $bookingGrid[$r][$bookersColumn] = $bookingGrid[$r][$bookersColumn] . ", " . $name;
                } else {
                    $bookingGrid[$r][$bookersColumn] = $bookingGrid[$r][$bookersColumn] . " (2)";
                }
            }
        }
        // remove the first column from the grid as it isn't required for display
        for ($r = 0; $r < count($bookingGrid); $r++) {
            for ($c = 1; $c <= $bookersColumn; $c++) {
                $bookingViewGrid[$r][$c - 1] = $bookingGrid[$r][$c];
            }
        }
        return $bookingViewGrid;
    }

    public function getFixtureData(): array
    {
        // Get fixture data for display

        // Get adjacent fixture
        $sql = "SELECT Fixtureid FROM Fixtures WHERE Seriesid = :Seriesid
        ORDER BY FixtureDate DESC LIMIT 2;";
        $stmt = $this->pdo->runSQL($sql, ['Seriesid' => $this->base['Seriesid']]);
        $latestFixtures = $stmt->fetchall(\PDO::FETCH_ASSOC);
        if ($latestFixtures[1]['Fixtureid'] == $this->fixtureId) {
            $adjacentFixtureId = $latestFixtures[0]['Fixtureid'];
            $adjacentLabel = "Next";
        } else {
            $adjacentFixtureId = $latestFixtures[1]['Fixtureid'];
            $adjacentLabel = "Previous";
        }

        $playerLists = $this->getPlayerLists();

        // Are we in the booking window for this fixture
        $inBookingWindow = $this->inBookingWindow();

        // get requested bookings
        $requestedBookings = $this->getRequestedBookings();

        $capacity = $this->getCapacity();

        $bookingViewGrid = $this->getBookingViewGrid();

        // return all fixture data
        $fixture = [
            'base' => $this->base,
            'adjacentFixtureid' => $adjacentFixtureId, 'adjacentLabel' => $adjacentLabel,
            'players' => $playerLists['players'], 'reserves' => $playerLists['reserves'],
            'decliners' => $playerLists['decliners'],  'abstainers' => $playerLists['abstainers'],
            'capacity' => $capacity,
            'inBookingWindow' => $inBookingWindow, 'requestedBookings' => $requestedBookings,
            'bookings' => $bookingViewGrid
        ];
        return $fixture;
    }

    public function getParticipantBookingsTable(int $userId): array
    {
        $bookings = $this->getParticipantBookings($userId, 'Booked');
        $table = [];
        foreach ($this->base['bookingTimes'] as $i => $time) {
            $table[$i]['time'] = $time;
            $table[$i]['court'] = 0;
            foreach ($bookings as $b) {
                if ($b['BookingTime'] == $time) {
                    $table[$i]['court'] = $b['CourtNumber'];
                }
            }
            $table[$i]['availableCourts'] = $this->getAvailableCourts($userId, $time, 'Booked');
        }
        return $table;
    }

    public function getParticipantBookings(int $userId, $type): array
    {
        // Get the current list of bookings of this type (Booked or Request) for this participant
        $sql = "SELECT CourtNumber, LEFT(BookingTime, 5) AS BookingTime FROM CourtBookings
        WHERE BookingType = :BookingType AND Fixtureid = :Fixtureid AND Userid = :Userid
        ORDER BY BookingTime;";
        $stmt = $this->pdo->runSQL($sql, ['BookingType' => $type, 'Fixtureid' => $this->fixtureId, 'Userid' => $userId]);
        $bookings = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $bookings;
    }

    public function getBookingRequestsTable(): array
    {
        // Get a table of booking requests for this fixture
        // The table has columns for time, court, and userid
        // A userid of 0 means no request

        // Get booking requests
        $sql = "SELECT LEFT(BookingTime, 5) AS BookingTime, CourtNumber, Userid
        FROM CourtBookings WHERE BookingType = 'Request' AND Fixtureid = :Fixtureid 
        ORDER BY BookingTime, CourtNumber;";
        $stmt = $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
        $bookingRequests = $stmt->fetchall(\PDO::FETCH_ASSOC);

        // Create table
        $time[0] = $this->base['FixtureTime'];
        $time[1] = date('H:i', strtotime($time[0]) + 60 * 60);
        $i = 0;
        $targetCourts = $this->courtList($this->base['TargetCourts']);
        foreach ($time as $t) {
            foreach ($targetCourts as $c) {
                $table[$i]['time'] = $t;
                $table[$i]['court'] = $c;
                $table[$i]['userid'] = 0;
                foreach ($bookingRequests as $b) {
                    if ($b['BookingTime'] == $t and $b['CourtNumber'] == $c) {
                        $table[$i]['userid'] = $b['Userid'];
                    }
                }
                $i++;
            }
        }
        return $table;
    }

    public function getRequestedBookings(): array
    {
        // Get the current list of booking requests for this fixture
        // Returns an empty array if no requests for this fixture
        $sql = "SELECT Users.Userid, ShortName, CourtNumber, LEFT(BookingTime, 5) AS BookingTime 
        FROM CourtBookings JOIN Users ON Users.Userid = CourtBookings.Userid
        WHERE BookingType = 'Request' AND Fixtureid = :Fixtureid 
        ORDER BY BookingTime, CourtNumber;";
        $stmt = $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
        $bookingRequests = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $bookingRequests;
    }

    public function getBookers(): array
    {
        // Return list of fixture participants who are bookers and who are not requested to book
        $sql = "SELECT Users.Userid, FirstName, LastName, ShortName
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE Booker = TRUE AND FixtureParticipants.Fixtureid = :F1 
        ORDER BY ShortName;";
        $stmt = $this->pdo->runSQL($sql, ['F1' => $this->fixtureId]);
        $users = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function setBookingRequests($bookingRequests)
    {
        // Delete any existing requests
        $sql = "DELETE FROM CourtBookings WHERE Fixtureid = :Fixtureid AND BookingType = 'Request';";
        $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
        // Add the new requests
        foreach ($bookingRequests as $request) {
            if ($request['userid'] != 0) {
                $this->addCourtBooking($request['userid'], $request['time'], $request['court'], 'Request');
            }
        }
    }

    public function countParticipantBookings(int $userId, $type): int
    {
        $sql = "SELECT COUNT(CourtNumber) FROM CourtBookings
        WHERE BookingType = :BookingType AND Fixtureid = :Fixtureid AND Userid = :Userid;";
        $stmt = $this->pdo->runSQL($sql, ['BookingType' => $type, 'Fixtureid' => $this->fixtureId, 'Userid' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    public function getInvitationData(int $userId): array
    {
        // Get data for asking user if they want to play om participant web page
        $r['Fixtureid'] = $this->base['Fixtureid'];
        $r['Userid'] = $userId;
        $r['FixtureDate'] = $this->base['FixtureDate'];
        $r['FixtureTime'] = $this->base['FixtureTime'];
        $sql = "SELECT FirstName, LastName FROM Users WHERE Userid = :Userid;";
        $stmt = $this->pdo->runSQL($sql, ['Userid' => $userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $r['FirstName'] = $row['FirstName'];
        $r['LastName'] = $row['LastName'];
        return $r;
    }

    public function getWannaPlayRecipients(): array
    {
        // Get recipient list for creating invitation to play emails
        $sql = "SELECT Users.Userid, FirstName, LastName, EmailAddress
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE WantsToPlay IS NULL AND Fixtureid = :Fixtureid
        ORDER BY FirstName, LastName;";
        $stmt = $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
        return $stmt->fetchall(\PDO::FETCH_ASSOC);
    }

    public function getBookingRequestRecipients(): array
    {
        // Get recipients for creating booking request emails
        // Includes booking users who want to play, have not responded, or who are subject to a booking request
        $sql = "SELECT DISTINCT Users.Userid, FirstName, LastName, EmailAddress, Booker
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        LEFT JOIN CourtBookings ON Users.Userid = CourtBookings.Userid AND BookingType = 'Request' 
        WHERE (BookingType IS NOT NULL OR WantsToPlay = TRUE OR WantsToPlay IS NULL) 
        AND Booker = TRUE
        AND FixtureParticipants.Fixtureid = :Fixtureid
        ORDER BY FirstName, LastName;";
        $stmt = $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
        return $stmt->fetchall(\PDO::FETCH_ASSOC);
    }

    public function getUpdateRecipients(): array
    {
        // Get recipient list for creating update emails
        $sql = "SELECT Users.Userid, FirstName, LastName, EmailAddress
        FROM Users JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE WantsToPlay = TRUE AND Fixtureid = :Fixtureid
        ORDER BY FirstName, LastName;";
        $stmt = $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
        return $stmt->fetchall(\PDO::FETCH_ASSOC);
    }

    public function setInvitationsSent()
    {
        $sql = "UPDATE Fixtures SET InvitationsSent = TRUE WHERE Fixtureid = :Fixtureid;";
        $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
        $this->base['InvitationsSent'] = TRUE;
    }

    public function getInvitationsSent(): bool
    {
        return (bool)$this->base['InvitationsSent'];
    }

    public function createBookingRequests()
    {
        // Automatically create booking requests
        // Delete any existing requests
        $sql = "DELETE FROM CourtBookings WHERE Fixtureid = :Fixtureid AND BookingType = 'Request';";
        $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
        // Get booking participants ordered by number of bookings across previous fixtures
        $sql = "SELECT Users.Userid FROM Users 
        JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        AND FixtureParticipants.Fixtureid = :Fixtureid
        LEFT JOIN CourtBookings ON Users.Userid = CourtBookings.Userid AND BookingType = 'Booked' 
        WHERE Users.Booker = TRUE 
        GROUP BY Users.Userid ORDER BY COUNT(*) DESC;";
        $userIds = $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId])->fetchall(\PDO::FETCH_ASSOC);
        // Create requests, allocating users to courts and times
        $numUsers = count($userIds);
        $time[0] = $this->base['FixtureTime'];
        $time[1] = date('H:i', strtotime($time[0]) + 60 * 60);
        if (strcmp($time[0], "07:30") == 0) {
            $time[0] = $time[1]; // swap order of times if 07:30 is the first time
            $time[1] = $this->base['FixtureTime'];
        }
        $u = 0;
        $targetCourts = $this->courtList($this->base['TargetCourts']);
        foreach ($targetCourts as $c) {
            foreach ($time as $t) {
                if ($u >= $numUsers) {
                    return;
                } // run out of users
                $userId = $userIds[$u++]['Userid'];
                $this->addCourtBooking($userId, $t, $c, 'Request');
            }
        }
    }

    public function getCapacity(): array
    {
        // Return the number of courts available for a two-hour fixture
        // Returns an associative array with the start time as key and number of courts as value
        // If two start times are possible, the array has two entries, otherwise one
        $sql = "SELECT LEFT(BookingTime, 5) AS BookingTime, COUNT(CourtNumber) AS NumCourts
    FROM  CourtBookings WHERE FixtureId = :Fixtureid AND BookingType = 'Booked'
    GROUP BY BookingTime
    ORDER BY BookingTime;";
        $stmt = $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
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

    public function getEmailList(): string
    {
        // Return a comma separated list of email addresses for all participants
        $sql = "SELECT FirstName, LastName, EmailAddress FROM Users 
        JOIN FixtureParticipants ON Users.Userid = FixtureParticipants.Userid
        WHERE Fixtureid = :Fixtureid;";
        $stmt = $this->pdo->runSQL($sql, ['Fixtureid' => $this->fixtureId]);
        $rows = $stmt->fetchall(\PDO::FETCH_ASSOC);
        $emails = "";
        foreach ($rows as $row) {
            $emails .= $row['FirstName'] . ' ' . $row['LastName'] . ' <' . $row['EmailAddress'] . '>, ';
        }
        $emails = substr($emails, 0, -2); // remove trailing comma and space 
        return $emails;
    }
}
