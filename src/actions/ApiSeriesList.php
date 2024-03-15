<?php
# Return series list

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiSeriesList
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

public function __invoke(Request $request, Response $response): Response
    {
        $m = $this->container->get('Model');
        if (is_string($error = $m->checkAdmin())) {
            $response->getBody()->write($error);
            return $response;
        }
        $s = $m->getSeriesList();
        $list = $s->getAllSeries();
        $response->getBody()->write(json_encode($list));        
        return $response->withHeader('Content-Type', 'application/json');
    }
}
