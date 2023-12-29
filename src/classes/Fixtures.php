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
    return $fixtureId;
    }
}
