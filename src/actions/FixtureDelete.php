<?php
# Delete the specified fixture

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class FixtureDelete
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $fixtureId = $params['fixtureid'];
        $seriesId = $params['seriesid'];
        $pdo = $GLOBALS['pdo'];
        $f = new \TennisApp\Fixtures($pdo);
        $f->deleteFixture($fixtureId);
        $lines[] = "Fixture id: $fixtureId";
        $view = Twig::fromRequest($request);
        return $view->render($response, 'opcontinue.html', ['op' => "Fixture deleted", 
        'link' => "series?seriesid=$seriesId", 'lines' => $lines]);
    }
}