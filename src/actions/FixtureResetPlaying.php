<?php
# Set all fixture participants to not playing

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class FixtureResetPlaying
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $fixtureId = $params['fixtureid'];
        $pdo = $GLOBALS['pdo'];
        $f = new \TennisApp\Fixtures($pdo);
        $f->resetPlaying($fixtureId);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'opcontinue.html', ['op' => 'All set to not playing', 
        'link' => "fixture?fixtureid=$fixtureId", 'lines' => ""]);
    }
}
