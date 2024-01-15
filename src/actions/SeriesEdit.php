<?php
# Edit basic series data from form parameters and then display the series

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class SeriesEdit
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getParsedBody();
        $seriesId = $params['seriesid'];
        $owner = $params['owner'];
        $day = $params['day'];
        $time = $params['time'];
        $pdo = $GLOBALS['pdo'];
        $s = new \TennisApp\Series($pdo);
        $row = $s->updateBasicSeriesData($seriesId, $owner, $day, $time);
        $owner0 = $row['SeriesOwner'];
        $day0 = $row['SeriesWeekday'];
        $time0 = substr($row['SeriesTime'],0,5);
        if ($owner0 != $owner) { $changes[] = "Owner: $owner0 -> $owner"; }
        if ($day0 != $day) { $changes[] = "Day: $day0 -> $day"; }
        if ($time0 != $time) { $changes[] = "Time: $time0 -> $time"; }
        if (empty($changes)) { $changes[] = "Nothing changed"; }
        $view = Twig::fromRequest($request);
        return $view->render($response, 'sfeditcontinue.html', ['op' => 'Series data edits', 
        'link' => "series?seriesid=$seriesId", 'changes' => $changes]);
      }
}
