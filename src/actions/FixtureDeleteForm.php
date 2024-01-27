<?php
# Present are you sure before deleting fixture
namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class FixtureDeleteForm
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $fixtureId = $params['fixtureid'];
        $seriesId = $params['seriesid'];
        $lines[] = "Are you sure you want to delete fixture $fixtureId?";
        $view = Twig::fromRequest($request);
        return $view->render($response, 'opconfirm.html', ['op' => "Delete fixture", 
        'continuelink' => "fixturedelete?fixtureid=$fixtureId&seriesid=$seriesId", 
        'cancellink' => "fixture?fixtureid=$fixtureId", 
        'lines' => $lines]);
    }
}
