<?php
# External token-based entry point

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class Start
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $token = $args['token'];
        $m = $this->container->get('Model');
        $t = $m->getTokens();
        $row = $t->checkToken($token);
        $userId = $row['Userid'];
        switch ($row['TokenClass']) {
            case 'User':
                $fixtureId = $row['Otherid'];
                $route = "/participant?fixtureid=$fixtureId&userid=$userId";
                break;
            case 'Owner':
                $seriesId = $row['Otherid'];
                $route = "/series?seriesid=$seriesId";
                break;
            case 'Admin':
                $route = "/serieslist";
                break;
        }
        return $response
          ->withHeader('Location', $route)
          ->withStatus(302);
    }
}