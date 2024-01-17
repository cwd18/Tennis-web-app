<?php
# Add bookings from form parameters and then present continuation view

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class FixtureAddBooking
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getParsedBody();
        $fixtureId = $params['fixtureid'];
        $bookerId = $params['booker'];
        $court1 = $params['court1'];
        $time1 = $params['time1'];
        $court2 = $params['court2'];
        $time2 = $params['time2'];
        $pdo = $GLOBALS['pdo'];
        $f = new \TennisApp\Fixtures($pdo);
        if (strlen($court1) > 0) {
            $f->addCourtBooking($fixtureId, $bookerId, $court1, $time1);
            $lines[] = "Court $court1 at $time1";
        }
        if (strlen($court2) > 0 ) {
            $f->addCourtBooking($fixtureId, $bookerId, $court2, $time2);
            $lines[] = "Court $court2 at $time2";
        }
        $view = Twig::fromRequest($request);
        return $view->render($response, 'opcontinue.html', ['op' => 'Added bookings to fixture',
        'link' => 'fixture?fixtureid=' . $fixtureId, 'lines' => $lines]);
      }
}
