<?php
# Add next fixture to specified series

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class FixtureAdd
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $seriesId = $params['seriesid'];
        $pdo = $GLOBALS['pdo'];
        $f = new \TennisApp\Fixtures($pdo);
        $fixtureId = $f->addNextFixtureToSeries($seriesId,3);
        $row = $f->getBasicFixtureData($fixtureId);
        $lines[] = $f->fixtureDescription($row['FixtureDate']);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'opcontinue.html', ['op' => 'Added fixture', 
        'link' => "series?seriesid=$seriesId", 'lines' => $lines]);
    }
}
