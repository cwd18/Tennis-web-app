<?php
# View the specified fixture by fixtureid or seriesid (to view next fixture)

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;
use \TennisApp\Fixture;

final class FixtureView
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $fixtureId = (int)$params['fixtureid'];
        $m = $this->container->get('Model');
        $f = new Fixture($m->db, $fixtureId);
        $seriesId = $f->getSeriesid();
        if (is_string($error = $m->checkOwner($seriesId))) {
            $response->getBody()->write($error);
            return $response;
        }
        $fixture = $f->getFixtureData();
        $view = Twig::fromRequest($request);
        return $view->render($response, 'fixture.html', $fixture);   
    }
}
