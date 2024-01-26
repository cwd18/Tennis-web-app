<?php
# Add users from form parameters and then display the fixture

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class FixtureDelUsers
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
        $f = new \TennisApp\Fixtures($pdo);
        $users = $f->deleteFixtureUsers($fixtureId, $userIds);
        foreach ($users as $user) {
            $lines[] = $user['FirstName'] . ' ' . $user['LastName'];
        }
        $view = Twig::fromRequest($request);
        return $view->render($response, 'opcontinue.html', ['op' => 'Users deleted from fixture',
        'link' => "fixture?fixtureid=$fixtureId", 'lines' => $lines]);
      }
}
