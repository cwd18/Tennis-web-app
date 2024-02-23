<?php
# View the specified fixture by fixtureid or seriesid (to view next fixture)

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

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
        $m = $this->container->get('Model');
        $f = $m->getFixtures();
        $s = $m->getSeries();
        $f = $m->getFixtures();
        if (array_key_exists('fixtureid', $params)) {
            $fixtureId = $params['fixtureid'];
            $seriesId = $f->getSeriesid($fixtureId);
        } else {
            $seriesId = $params['seriesid'];
            $fixtureId = $s->nextFixture($seriesId);
        }
        if (is_string($error = $m->checkOwner($seriesId))) {
            $response->getBody()->write($error);
            return $response;
        }
        $fixture = $f->getFixture($fixtureId);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'fixture.html', $fixture);   
    }
}
