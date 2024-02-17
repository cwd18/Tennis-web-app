<?php
# Present are you sure before deleting fixture
namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class FixtureDeleteForm
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $fixtureId = $params['fixtureid'];
        $seriesId = $params['seriesid'];
        $m = $this->container->get('Model');
        if (is_string($error = $m->checkAdmin())) {
            $response->getBody()->write($error);
            return $response;
        }
        $lines[] = "Are you sure you want to delete fixture $fixtureId?";
        $view = Twig::fromRequest($request);
        return $view->render($response, 'opconfirm.html', ['op' => "Delete fixture", 
        'continuelink' => "fixturedelete?fixtureid=$fixtureId&seriesid=$seriesId", 
        'cancellink' => "fixture?fixtureid=$fixtureId", 
        'lines' => $lines]);
    }
}
