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
        $model = $this->container->get('Model');
        $f = $model->getFixtures();
        $f->setParticipantWantsToPlay($fixtureId, $userId, $wantsToPlay);
        return $response
          ->withHeader('Location', "/participant?fixtureid=$fixtureId&userid=$userId")
          ->withStatus(302);
    }
}
