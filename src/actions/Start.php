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
        switch ($row['TokenClass']) {
            case 'User':
                $fixtureId = $row['Otherid'];
                $wantsToPlay = $m->getFixtures()->getWantsToPlay($fixtureId, $userId);
                $route = $wantsToPlay == NULL ? 
                "/participantInvite?fixtureid=$fixtureId&userid=$userId" :
                "/fixturenotice?fixtureid=$fixtureId";
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