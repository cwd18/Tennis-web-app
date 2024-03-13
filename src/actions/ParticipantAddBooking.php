<?php
# Add booking from form parameters

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ParticipantAddBooking
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $fixtureId = (int)$params['fixtureid'];
        $userId = $params['userid'];
        $court = $params['court'];
        $time = $params['time'];
        $type = $params['type'];
        $m = $this->container->get('Model');
        $f = $m->getFixture($fixtureId);
        $f->addCourtBooking($userId, $time, $court, $type);
        if (strcmp($type, 'Booked') == 0) {
            $countBookings = $f->countParticipantBookings($userId, $type);
            if ($countBookings == 1) {
                $f->setCourtsBooked($userId, FALSE); // first court booked
            } else {
                $f->setCourtsBooked($userId, TRUE); // second court booked
            }
        }
        $outPath = strcmp($type, 'Booked') == 0 ? "/participant?fixtureid=$fixtureId&userid=$userId":
            "/fixtureAddRequests?fixtureid=$fixtureId";
        if (strcmp($m->sessionRole(),'User') == 0) {
            $outPath = $countBookings == 2 ?
            "/fixturenotice?fixtureid=$fixtureId" : 
            "/participantBook?fixtureid=$fixtureId&userid=$userId&type=$type";
        }
        return $response
            ->withHeader('Location', $outPath)
            ->withStatus(302);
    }
}
