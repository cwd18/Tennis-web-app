<?php
# Present form for users to be deleted from the specified fixture

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class FixtureDelUsersForm
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $fixtureId = $params['fixtureid'];
        $pdo = $GLOBALS['pdo'];
        $fixture = new \TennisApp\Fixtures($pdo);
        $users = $fixture->getFixtureUsers($fixtureId);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'usersselectform.html', 
        ['users' => $users, 
        'legend' => 'Select users to delete from fixture',
        'formlink' => 'fixturedelusers',
        'sfidvalue' => $fixtureId,
        'sfidname' => 'fixtureid',
        'cancellink' => "fixture?fixtureid=" . $fixtureId]);   
    }
}
