<?php
# Add series from form parameters

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

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
        $s->addSeries($owner, $day, $time);
        return $response
          ->withHeader('Location', "/serieslist")
          ->withStatus(302);
    }
}
