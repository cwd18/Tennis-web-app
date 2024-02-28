<?php
# Delete booking request from link parameters

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class FixtureDelRequest
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
        $court = $params['court'];
        $time = $params['time'];
        $m = $this->container->get('Model');
        $f = $m->getFixtures();
        $seriesId = $f->getSeriesid($fixtureId);
        if (is_string($error = $m->checkOwner($seriesId))) {
            $response->getBody()->write($error);
            return $response;
        }
        $f->deleteCourtBooking($fixtureId, $userId, $time, $court, 'Request');
        return $response
          ->withHeader('Location', "/fixture?fixtureid=$fixtureId&userid=$userId")
          ->withStatus(302);
      }
}
