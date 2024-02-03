<?php
# Delete the specified fixture

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class FixtureDelete
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
        $model = $this->container->get('Model');
        $f = $model->getFixtures();
        $f->deleteFixture($fixtureId);
        return $response
          ->withHeader('Location', "/series?seriesid=$seriesId")
          ->withStatus(302);
    }
}