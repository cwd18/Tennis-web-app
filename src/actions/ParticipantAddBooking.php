<?php
# Add booking from form parameters

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ParticipantAddBooking
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
        $court = $params['court'];
        $time = $params['time'];
        $model = $this->container->get('Model');
        $f = $model->getFixtures();
        $f->addCourtBooking($fixtureId, $userId, $time, $court);
        $outPath = $fromFixture ? '/participantFromFixture' : '/participant';
        return $response
            ->withHeader('Location', "$outPath?fixtureid=$fixtureId&userid=$userId")
            ->withStatus(302);
    }
}
