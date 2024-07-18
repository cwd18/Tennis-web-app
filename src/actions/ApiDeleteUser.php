<?php
# Delete a user record given userid

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiDeleteUser
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $userId = (int)$args["userid"];
        $m = $this->container->get('Model');
        $u = $m->getUsers();
        $u->deleteUser($userId);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
