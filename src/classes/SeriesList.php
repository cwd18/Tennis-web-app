<?php
declare(strict_types=1);

namespace TennisApp;

class SeriesList
{
    public Database $pdo;

    public function __construct(Database $pdo)
    {
        $this->pdo = $pdo;
    }

    public function seriesDescription($weekday, $time)
    {
        $dayname = date('l', strtotime("Monday +$weekday days"));
        $description = $dayname . ' at ' . $time;
        return $description;
    }

    private function createSeries(int $seriesId) : Series
    {
        $series = new Series($this->pdo, $seriesId);
        return $series;
    }
  
    public function getAllSeries() : array
    {
        $stmt = $this->pdo->runSQL("SELECT seriesId FROM FixtureSeries ORDER BY SeriesWeekday;");
        while ($seriesId = $stmt->fetchColumn()) {
            $s = $this->createSeries($seriesId);
            $base = $s->getBasicSeriesData();
            $base['futureFixtures'] = $s->countFutureFixtures();
            $seriesList[] = $base;
        }
        return $seriesList;
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
        $seriesId = (int)$this->pdo->lastInsertId();
        $s = $this->createSeries($seriesId);
        $s->addUsers(array((int)$owner));
        return $seriesId;
    }
}
