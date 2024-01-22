<?php
# Add series from form parameters

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class SeriesAdd
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getParsedBody();
        $owner = $params['owner'];
        $day = $params['day'];
        $time = $params['time'];
        $pdo = $GLOBALS['pdo'];
        $s = new \TennisApp\Series($pdo);
        $seriesId = $s->addSeries($owner, $day, $time);
        $changes[] = "Owner: $owner";
        $changes[] = "Day: $day";
        $changes[] = "Time: $time";
        $view = Twig::fromRequest($request);
        return $view->render($response, 'opcontinue.html', ['op' => "Series added: $seriesId", 
        'link' => "serieslist", 'lines' => $changes]);
    }
}
