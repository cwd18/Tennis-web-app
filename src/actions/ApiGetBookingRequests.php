<?php
# Return booking requests table given fixtureid 

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiGetBookingRequests
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

public function __invoke(Request $request, Response $response, array $args): Response
    {
        $fixtureId = (int)$args['fixtureid'];
        $m = $this->container->get('Model');
        if (is_string($error = $m->checkUser($fixtureId))) {
            $response->getBody()->write($error);        
            return $response;
        }
        $f = $m->getFixture($fixtureId);
        $table = $f->getBookingRequestsTable();
        $response->getBody()->write(json_encode($table));        
        return $response->withHeader('Content-Type', 'application/json');
    }
}
