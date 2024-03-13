<?php
# Update Participant row to say no court was booked

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ParticipantSetBooking
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
        $userId = (int)$params['userid'];
        $value = $params['value'];
        $m = $this->container->get('Model');
        $f = $m->getFixture($fixtureId);
        $f->setCourtsBooked($userId, $value);
        $outPath = "/participant?fixtureid=$fixtureId&userid=$userId";
        if (strcmp($m->sessionRole(),'User') == 0) {
            $outPath = "/fixturenotice?fixtureid=$fixtureId";
        }
        return $response
            ->withHeader('Location', $outPath)
            ->withStatus(302);
    }
}
