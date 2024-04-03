<?php
# Return participant data given fixtureid and userid

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiGetParticipantData
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
        if (is_string($error = $m->checkUserAccessFixture($fixtureId))) {
            $response->getBody()->write($error);        
            return $response;
        }
        $u = $m->getUsers();
        $participantData = $u->getUserData($userId);
        $f = $m->getFixture($fixtureId);
        $inBookingWindow = $f->inBookingWindow();
        $r = $f->getWantsToPlay($userId);
        if (is_null($r)) {
            $wantsToPlay = 'Unknown';
        } else {
            $wantsToPlay = $r ? 'Yes' : 'No';
        }
        $participantData['wantsToPlay'] = $wantsToPlay;
        $participantData['inBookingWindow'] = $inBookingWindow;
        $response->getBody()->write(json_encode($participantData));        
        return $response->withHeader('Content-Type', 'HTML/json');
    }
}
