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
        if ($row == FALSE) {
            $response->getBody()->write("Token not found: $token");
            return $response;
        }
        $userId = $row['Userid'];

        $_SESSION['User'] = $userId;
        $_SESSION['Role'] = $row['TokenClass'];
        $_SESSION['Otherid'] = $row['Otherid'];

        $role = $row['TokenClass'];
        if (strcmp($role, 'User') == 0) {
            $fixtureId = $row['Otherid'];
            $route = "/participantPage?fixtureid=$fixtureId&userid=$userId";
        } elseif (strcmp($role, 'Owner') == 0) {
            $seriesId = $row['Otherid'];
            $route = "/series?seriesid=$seriesId";
        } elseif (strcmp($role, 'Admin') == 0) {
            $route = "/serieslist";
        }
        return $response
          ->withHeader('Location', $route)
          ->withStatus(302);
    }
}