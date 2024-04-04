<?php
# Participant page for a series, which starts on the next fixture

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class ParticipantSeries
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $seriesId = (int)$params['seriesid'];
        $index = (int)$params['index'];
        $m = $this->container->get('Model');
        $s = $m->getSeries($seriesId);
        $fixtureId = $index == 0 ? $s->nextfixture() : $s->latestFixture();
        $view = Twig::fromRequest($request);
        if (is_string($error = $m->checkUserAccessSeries($seriesId))) {
            return $view->render($response, 'error.html', ['error' => $error]);}
        $f = $m->getFixture($fixtureId);
        $userId = $m->sessionUser();
        $fixture = $f->getFixtureData();
        $participant = $f->getParticipantData($userId);
        return $view->render($response, 'participantSeries.html', 
            ['fixture' => $fixture, 'participant' => $participant]);   
    }
}
