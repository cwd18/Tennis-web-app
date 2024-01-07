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
        $pdo = $GLOBALS['pdo'];
        $fixtures = new \TennisApp\Fixtures($pdo);
        $f = $fixtures->deleteFixture($fixtureId);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'fixturedeleted.html', $f);   
    }
}
