<?php
# Update booking requests from parameters 

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiPutBookingRequests
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

public function __invoke(Request $request, Response $response, array $args): Response
    {
        $fixtureId = (int)$args['fixtureid'];
        $bookingRequests = $request->getParsedBody();
        $m = $this->container->get('Model');
        if (is_string($error = $m->checkUserAccessFixture($fixtureId))) {
            $response->getBody()->write($error);        
            return $response;
        }
        $f = $m->getFixture($fixtureId);
        $f->setBookingRequests($bookingRequests);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
