<?php
# Set whether participant wants to play

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ParticipantWantsToPlay
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $fixtureId = $params['fixtureid'];
        $userId = $params['userid'];
        $wantsToPlay = $params['WantsToPlay'];
        $m = $this->container->get('Model');
        if (is_string($error = $m->checkUser($fixtureId))) {
            $response->getBody()->write($error);
            return $response;
        }
        $f = $m->getFixtures();
        if ($wantsToPlay) {
            $f->setWantsToPlay($fixtureId, $userId);
        } else {
            $f->setWantsNotToPlay($fixtureId, $userId);
        }
        $outPath = strcmp($m->sessionRole(),'User') == 0 ? "/fixturenotice?fixtureid=$fixtureId" :
         "/participant?fixtureid=$fixtureId&userid=$userId";
        return $response
          ->withHeader('Location', $outPath)
          ->withStatus(302);
    }
}
