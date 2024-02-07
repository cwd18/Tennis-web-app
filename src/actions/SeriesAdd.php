<?php
# Add series from form parameters

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class SeriesAdd
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getParsedBody();
        $owner = $params['owner'];
        $day = $params['day'];
        $time = $params['time'];
        $courts = $params['courts'];
        $s = $this->container->get('Model')->getSeries();
        $s->addSeries($owner, $day, $time, $courts);
        return $response
          ->withHeader('Location', "/serieslist")
          ->withStatus(302);
    }
}
