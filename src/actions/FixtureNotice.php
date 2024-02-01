<?php
# View a notice of the specified fixture

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class FixtureNotice
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $pdo = $GLOBALS['pdo'];
        $f = new \TennisApp\Fixtures($pdo);
        if (array_key_exists('fixtureid', $params)) {
            $fixtureId = $params['fixtureid'];
        } else {
            $fixtureId = $f->latestFixture($params['seriesid']);
        }
        $url = $request->getUri();
        var_dump($url);
        $fixture = $f->getFixture($fixtureId);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'fixtureNotice.html', $fixture);   
    }
}
