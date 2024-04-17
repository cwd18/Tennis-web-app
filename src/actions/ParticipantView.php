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
        $f = $m->getFixture($fixtureId);
        $seriesId = $f->getSeriesid();
        $view = Twig::fromRequest($request);
        if (is_string($error = $m->checkOwnerAccess($seriesId))) {
            return $view->render($response, 'error.html', ['error' => $error]);}
        $fixture = $f->getFixtureData();
        $u = $f->getParticipantData($userId);
        $server = $m->getServer();
        $token = $m->getTokens()->getOrCreateToken($userId, 'User', $seriesId);
        $u['SeriesLink'] = "$server/start/$token";
        $bookings = $f->getParticipantBookings($userId, 'Booked');
        return $view->render($response, 'participant.html', 
        ['fixture' => $fixture, 'participant' => $u, 'bookings' => $bookings]);   
    }
}
