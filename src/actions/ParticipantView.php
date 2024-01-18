<?php
# Present form for editing participant data

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class ParticipantView
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $fixtureId = $params['fixtureid'];
        $userId = $params['userid'];
        $pdo = $GLOBALS['pdo'];
        $f = new \TennisApp\Fixtures($pdo);
        $fixture = $f->getFixture($fixtureId);
        $u = $f->getParticipantData($fixtureId, $userId);
        $brows = $f->getParticipantBookings($fixtureId, $userId);
        $bookings=null;
        $n=0;
        foreach ($brows as $b) {
            $bookings[$n]['court'] = $b['CourtNumber'];
            $bookings[$n]['time'] = substr($b['BookingTime'],0,5);
            $n++;
        }
        $isPlaying = $u['IsPlaying']?"Yes":"No";
        if (is_null($u['WantsToPlay'])) { $wantsToPlay = "Unknown"; }
        else { $wantsToPlay = $u['WantsToPlay']?"Yes":"No"; }
        $view = Twig::fromRequest($request);
        return $view->render($response, 'participant.html', 
        ['fixture' => $fixture, 'participant' => $u,
        'isplaying' => $isPlaying, 'wantstoplay' => $wantsToPlay,
        'bookings' => $bookings]);   
    }
}
