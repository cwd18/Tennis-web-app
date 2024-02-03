<?php
# Add next fixture to specified series

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class FixtureAdd
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $seriesId = $params['seriesid'];
        $model = $this->container->get('Model');
        $f = $model->getFixtures();
        $f->addNextFixtureToSeries($seriesId);
        return $response
          ->withHeader('Location', "/series?seriesid=$seriesId")
          ->withStatus(302);
    }
}
