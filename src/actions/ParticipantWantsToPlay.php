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
        $fromFixture = str_contains($request->getUri()->getPath(), 'FromFixture');
        $params = $request->getQueryParams();
        $fixtureId = $params['fixtureid'];
        $userId = $params['userid'];
        $wantsToPlay = $params['WantsToPlay'];
        $f = $this->container->get('Model')->getFixtures();
        if ($wantsToPlay) {
            $f->setWantsToPlay($fixtureId, $userId);
        } else {
            $f->setWantsNotToPlay($fixtureId, $userId);
        }
        $outPath = $fromFixture ? '/participantFromFixture' : '/participant';
        return $response
          ->withHeader('Location', "$outPath?fixtureid=$fixtureId&userid=$userId")
          ->withStatus(302);
    }
}
