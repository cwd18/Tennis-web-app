<?php
# Delete the specified series

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class SeriesDelete
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
        $s = $model->getSeries();
        $s->deleteSeries($seriesId);
        return $response
          ->withHeader('Location', "/serieslist")
          ->withStatus(302);
    }
}
