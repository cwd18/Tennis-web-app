<?php
# Present participant page

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class ParticipantPage
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
        $userId = (int)$params['userid'];
        $m = $this->container->get('Model');
        $view = Twig::fromRequest($request);
        if (is_string($error = $m->checkUser($fixtureId))) {
            return $view->render($response, 'error.html', ['error' => $error]);}
        $f = $m->getFixture($fixtureId);
        if ($f->getWantsToPlay($userId) == NULL) {
            $participant = $f->getParticipantData($userId);
            $fixture = $f->getBasicFixtureData();
            return $view->render($response, 'participantInvite.html', 
            ['fixture' => $fixture, 'participant' => $participant]);
        }
        if ($f->getCourtsBooked($userId) == NULL and $f->inBookingWindow() == 0) {
            $fixture = $f->getFixtureData();
            $participant = $f->getParticipantData($userId);
            $bookings = $f->getParticipantBookings($userId, 'Booked');
            return $view->render($response, 'participant.html', 
            ['fixture' => $fixture, 'participant' => $participant, 'bookings' => $bookings]);   
        }
        $fixture = $f->getFixtureData();
        return $view->render($response, 'fixtureNotice.html', $fixture);   
    }
}
