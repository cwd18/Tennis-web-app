<?php
# Return booking view grid given fixtureid 

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiGetBookingViewGrid
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
        $result = $f->getBookingViewGrid($type);
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
