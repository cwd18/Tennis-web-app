<?php
# Return booking records given booking type and fixtureid 

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiGetBookings
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $type = $args['type'];
        $fixtureId = (int)$args['fixtureid'];
        $m = $this->container->get('Model');
        if (is_string($error = $m->checkUserAccessFixture($fixtureId))) {
            $response->getBody()->write($error);
            return $response;
        }
        $f = $m->getFixture($fixtureId);
        $result = $f->getBookings($type);
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
