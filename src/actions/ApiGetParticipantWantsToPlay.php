<?php
# Return whether participant wants to play given fixtureid and userid

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiGetParticipantWantsToPlay
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
        $f = $m->getFixture($fixtureId);
        $r = $f->getWantsToPlay($userId);
        if (is_null($r)) {
            $wantsToPlay = 'Unknown';
        } else {
            $wantsToPlay = $r ? 'Yes' : 'No';
        }
        $response->getBody()->write($wantsToPlay);        
        return $response->withHeader('Content-Type', 'application/text');
    }
}
