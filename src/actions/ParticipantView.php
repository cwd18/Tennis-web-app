<?php
# Present form for editing participant data

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class ParticipantView
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $fixtureId = (int)$params['fixtureid'];
        $userId = $params['userid'];
        $m = $this->container->get('Model');
        $f = $m->getFixtures();
        $seriesId = $f->getSeriesid($fixtureId);
        if (is_string($error = $m->checkOwner($seriesId))) {
            $response->getBody()->write($error);
            return $response;
        }
        $fixture = $f->getFixture($fixtureId);
        $u = $f->getParticipantData($fixtureId, $userId);
        $brows = $f->getParticipantBookings($fixtureId, $userId, 'Booked');
        $bookings=null;
        $n=0;
        foreach ($brows as $b) {
            $bookings[$n]['court'] = $b['CourtNumber'];
            $bookings[$n]['time'] = substr($b['BookingTime'],0,5);
            $n++;
        }
        $isPlaying = $u['IsPlaying']?"Yes":"No";
        if (is_null($u['WantsToPlay'])) { 
            $wantsToPlay = "Unknown"; 
        } else { 
            $wantsToPlay = $u['WantsToPlay'] ? "Yes" : "No"; 
        }
        $twigFile = $request->getUri()->getPath() . '.html';
        $view = Twig::fromRequest($request);
        return $view->render($response, $twigFile, 
        ['fixture' => $fixture, 'participant' => $u,
        'isplaying' => $isPlaying, 'wantstoplay' => $wantsToPlay,
        'bookings' => $bookings]);   
    }
}
