<?php
# Edit basic series data from form parameters and then display the series

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class SeriesEdit
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getParsedBody();
        $seriesId = (int)$params['seriesid'];
        $day = $params['day'];
        $time = $params['time'];
        $courts = $params['courts'];
        $targetCourts = $params['targetcourts'];
        $autoEmail = array_key_exists('autoEmail', $params);

        $m = $this->container->get('Model');
        if (is_string($error = $m->checkOwner($seriesId))) {
            $response->getBody()->write($error);
            return $response;
        }
        $s = $m->getSeries();
        $owner = array_key_exists('owner', $params) ? $params['owner'] : $s->getOwner($seriesId);
        $s->updateBasicSeriesData($seriesId, $owner, $day, $time, $courts, $targetCourts, $autoEmail);
        return $response
          ->withHeader('Location', "/series?seriesid=$seriesId")
          ->withStatus(302);
    }
}
