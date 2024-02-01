<?php
# Set whether participant wants to play

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ParticipantWantsToPlay
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $fixtureId = $params['fixtureid'];
        $userId = $params['userid'];
        $wantsToPlay = $params['WantsToPlay'];
        $pdo = $GLOBALS['pdo'];
        $f = new \TennisApp\Fixtures($pdo);
        $f->setParticipantWantsToPlay($fixtureId, $userId, $wantsToPlay);
        return $response
          ->withHeader('Location', "/participant?fixtureid=$fixtureId&userid=$userId")
          ->withStatus(302);
    }
}
