<?php
# Add booking from form parameters

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ParticipantAddBooking
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $fixtureId = $params['fixtureid'];
        $userId = $params['userid'];
        $court = $params['court'];
        $time = $params['time'];
        $pdo = $GLOBALS['pdo'];
        $f = new \TennisApp\Fixtures($pdo);
        $f->addCourtBooking($fixtureId, $userId, $time, $court);
        return $response
            ->withHeader('Location', "/participant?fixtureid=$fixtureId&userid=$userId")
            ->withStatus(302);
    }
}
