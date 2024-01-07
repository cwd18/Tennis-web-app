<?php
declare(strict_types=1);

namespace TennisApp;

class Fixtures
{
    public function __construct(protected $pdo)
    {
    }

    public function fixtureDescription($datestr)
    {
        $date = strtotime($datestr);
        return date("l jS \of F Y",$date);
    }
    
    public function getRecentFixtures($seriesId)
    {
        $sql = "SELECT Fixtureid, FixtureDate, FixtureTime FROM Fixtures 
        WHERE Seriesid=$seriesId ORDER BY FixtureDate DESC LIMIT 5;";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $result = $statement->fetchall(\PDO::FETCH_ASSOC);
        if (empty($result)) {
            return $result;
        }
        foreach ($result as $row) {
            $description = $this->fixtureDescription($row['FixtureDate']);
            $time = substr($row['FixtureTime'],0,5);
            $series[] = ['fixtureid' => $row['Fixtureid'], 'description' => $description, 'time' => $time];
        }
        return $series;
    }

    public function addNextFixtureToSeries($seriesId)
    {
        // Get basic series data
        $series = new Series($this->pdo);
        $seriesRow = $series->getBasicSeriesData($seriesId);
        $fixtureOwner = $seriesRow['SeriesOwner'];
        $fixtureTime = $seriesRow['SeriesTime'];
        $fixtureWeekDay = $seriesRow['SeriesWeekday'];
        // Calculate the date of the next fixture
        $dayname = date('l', strtotime("Monday +$fixtureWeekDay days"));
        $fixtureDateInt=strtotime("next ".$dayname,strtotime("+6 Days"));
        $fixtureDate=date("y-m-d",$fixtureDateInt);
        // Add fixture
        $sql = "INSERT INTO Fixtures (Seriesid, FixtureOwner, FixtureDate, FixtureTime)
        VALUES ('$seriesId', '$fixtureOwner', '$fixtureDate', '$fixtureTime');";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $fixtureId = $this->pdo->lastInsertId();
        // Initialise participants from series candidates
        $sql = "INSERT INTO FixtureParticipants (Fixtureid, Userid)
        SELECT '$fixtureId', Userid FROM SeriesCandidates WHERE Seriesid=$seriesId;";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        return $fixtureId;
    }

    public function deleteFixture($fixtureId)
    {
        // only works if no participants or bookings
        $fixture = $this->getFixture($fixtureId);
        $sql = "DELETE FROM Fixtures WHERE Fixtureid=$fixtureId;";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        return $fixture;
    }

    public function getFixture($fixtureId)
    {
        // Get Fixture data
        $sql="SELECT Fixtures.Seriesid, FirstName, LastName, FixtureDate, FixtureTime
        FROM Fixtures, Users, FixtureSeries
        WHERE Fixtureid=$fixtureId 
        AND Fixtures.FixtureOwner=Users.Userid AND Fixtures.Seriesid=FixtureSeries.Seriesid;";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        $seriesId = $row['Seriesid'];
        $ownerName = $row['FirstName']." ".$row['LastName'];
        $description = $this->fixtureDescription($row['FixtureDate']);
        $fixtureTime=substr($row['FixtureTime'],0,5);

        // Get participants...
        $sql="SELECT Users.Userid, FirstName, LastName FROM Users, FixtureParticipants
        WHERE Fixtureid=$fixtureId AND Users.Userid=FixtureParticipants.Userid
        ORDER BY FirstName, LastName;";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $rows = $statement->fetchall(\PDO::FETCH_ASSOC);
        $participantList=Null;
        foreach ($rows as $row) {
            $participantList[] = $row['FirstName']." ".$row['LastName'];
            }

        // Get court bookings into grid with columns (court, booking time, bookers)
        $bookingGrid[0][0] = "Court";
        $sql = "SELECT DISTINCT BookingTime FROM CourtBookings WHERE Fixtureid=$fixtureId;";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $rows = $statement->fetchall(\PDO::FETCH_ASSOC);

        $c=1;
        foreach ($rows as $row) {
            $bookingGrid[0][$c] = substr($row['BookingTime'],0,5);
            $c++;
        }
        $bookersColumn=$c;
        $bookingGrid[0][$bookersColumn]="Bookers";

        $sql="SELECT DISTINCT CourtNumber FROM CourtBookings 
        WHERE Fixtureid=$fixtureId ORDER BY CourtNumber;";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $rows = $statement->fetchall(\PDO::FETCH_ASSOC);
        $r=1;
        foreach ($rows as $row) {
            $bookingGrid[$r][0] = $row['CourtNumber'];
            for ($c=1;$c<=$bookersColumn;$c++) {$bookingGrid[$r][$c] = "-";}
            $r++;
        }

        $sql = "SELECT FirstName, LastName, CourtNumber, BookingTime FROM Users, CourtBookings
        WHERE Fixtureid=$fixtureId and Users.Userid=CourtBookings.Userid
        ORDER BY CourtNumber, BookingTime;";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        $rows = $statement->fetchall(\PDO::FETCH_ASSOC);
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $Name = $row['FirstName']." ".$row['LastName'];
                for ($r=1;$bookingGrid[$r][0]!=$row['CourtNumber'];$r++) {} // match grid row
                for ($c=1;$bookingGrid[0][$c]!=substr($row['BookingTime'],0,5);$c++) {} // match grid column
                $bookingGrid[$r][$c] = $row['CourtNumber'];
                if ($bookingGrid[$r][$bookersColumn]=="-") {
                    $bookingGrid[$r][$bookersColumn] = $Name;
                } else if ($bookingGrid[$r][$bookersColumn]!=$Name) {
                    $bookingGrid[$r][$bookersColumn] = $bookingGrid[$r][$bookersColumn].", ".$Name;
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
        'owner' => $ownerName, 'participants' => $participantList, 'bookings' => $bookingViewGrid];
        return $fixture;
    }
}
