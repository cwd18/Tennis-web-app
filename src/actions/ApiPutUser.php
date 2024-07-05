<?php
# Add or update user record
# If userid is 0, add a new user record 

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ApiPutUser
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $userId = (int)$args["userid"];
        $userData = $request->getParsedBody();
        $m = $this->container->get('Model');
        $u = $m->getUsers();
        if ($userId === 0) {
            $u->addUser(
                $userData['FirstName'],
                $userData['LastName'],
                $userData['EmailAddress'],
                $userData['Booker']
            );
        } else {
            $u->updateUser(
                $userId,
                $userData['FirstName'],
                $userData['LastName'],
                $userData['EmailAddress'],
                $userData['Booker']
            );
        }
        return $response->withHeader('Content-Type', 'application/json');
    }
}
