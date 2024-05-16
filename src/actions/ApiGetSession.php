<?php
# Return session data

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiGetSession
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

public function __invoke(Request $request, Response $response, array $args): Response
    {
        $m = $this->container->get('Model');
        $userId = $m->sessionUser();
        $userData = $m->getUsers()->getUserData($userId);
        $sessionData['sessionUser'] = $userData['FirstName'] . ' ' . $userData['LastName'];
        $sessionData['sessionRole'] = $m->sessionRole();
        $response->getBody()->write(json_encode($sessionData));        
        return $response->withHeader('Content-Type', 'application/json');
    }
}
