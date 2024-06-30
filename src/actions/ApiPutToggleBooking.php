<?php
# Toggle selected boooking between 'Booked' and 'Cancel'

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiPutToggleBooking
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $fixtureId = (int)$args['fixtureid'];
        $time = $args['time'];
        $court = (int)$args['court'];
        $m = $this->container->get('Model');
        if (is_string($error = $m->checkOwnerAccessFixture($fixtureId))) {
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(401);
        }
        $f = $m->getFixture($fixtureId);
        $f->toggleBooking($time, $court);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
