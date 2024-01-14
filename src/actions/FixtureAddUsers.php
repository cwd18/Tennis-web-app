<?php
# Add users from form parameters and then present continuation view

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
        $f = new \TennisApp\Fixtures($pdo);
        $users = $f->addUsers($fixtureId, $userIds);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'useraddremcontinue.html', ['op' => 'Users added to fixture',
        'link' => 'fixture?fixtureid=' . $fixtureId, 'users' => $users]);
      }
}
