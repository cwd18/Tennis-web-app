<?php
# Return participant bookings table given fixtureid and userid

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiGetParticipantBookings
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

public function __invoke(Request $request, Response $response, array $args): Response
    {
        $fixtureId = (int)$args['fixtureid'];
        $userId = (int)$args['userid'];
        $m = $this->container->get('Model');
        if (is_string($error = $m->checkUser($fixtureId))) {
            return $view->render($response, 'error.html', ['error' => $error]);}
        $f = $m->getFixture($fixtureId);
        $bookings = $f->getParticipantBookingsTable($userId);
        $response->getBody()->write(json_encode($bookings));        
        return $response->withHeader('Content-Type', 'application/json');
    }
}
