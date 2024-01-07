<?php
# View the specified fixture

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class FixtureView
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $fixtureId = $params['fixtureid'];
        $pdo = $GLOBALS['pdo'];
        $fixtures = new \TennisApp\Fixtures($pdo);
        $f = $fixtures->getFixture($fixtureId);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'fixture.html', $f);   
    }
}
