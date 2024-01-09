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
        $series = new \TennisApp\Series($pdo);
        $series->updateBasicSeriesData($seriesId, $owner, $day, $time);
        $s = $series->getSeries($seriesId);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'series.html', $s);
      }
}
