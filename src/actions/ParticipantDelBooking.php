<?php
# Delete booking from link parameters and then present continuation view

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class ParticipantDelBooking
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
        $f->deleteCourtBooking($fixtureId, $userId, $time, $court);
        $lines[] = sprintf("Deleted court %u at %s", $court, $time);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'opcontinue.html', ['op' => 'Deleted booking from fixture',
        'link' => sprintf("participant?fixtureid=%s&userid=%s", $fixtureId, $userId),
        'lines' => $lines]);
      }
}
