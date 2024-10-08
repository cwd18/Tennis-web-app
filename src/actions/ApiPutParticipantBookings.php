<?php
# Post participant bookings given fixtureid and userid

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiPutParticipantBookings
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
        $bookings = $request->getParsedBody();
        $m = $this->container->get('Model');
        if (is_string($error = $m->checkUserAccessFixture($fixtureId))) {
            $response->getBody()->write($error);
            return $response;
        }
        $f = $m->getFixture($fixtureId);
        $f->setParticipantBookings($userId, $bookings);
        $f->setCourtsBooked($userId);
        $f->checkCourtsToCancel(); // delete any cancel courts that have since been booked
        return $response->withHeader('Content-Type', 'application/json');
    }
}
