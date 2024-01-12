<?php
# Add users from form parameters and then display the fixture

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class FixtureAddUsers
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getParsedBody();
        $fixtureId = $params['fixtureid'];
        foreach ($params as $pk => $p) {
            if (substr($pk, 0, 5) == "user_") {
                $userIds[] = $p;
            }
        }
        $pdo = $GLOBALS['pdo'];
        $fixture = new \TennisApp\Fixtures($pdo);
        $fixture->addUsers($fixtureId, $userIds);
        $f = $fixture->getFixture($fixtureId);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'fixture.html', $f);
      }
}
