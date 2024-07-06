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
        $booker = array_key_exists('booker', $userData); // checkbox
        $m = $this->container->get('Model');
        $u = $m->getUsers();
        if ($userId === 0) {
            $u->addUser(
                $userData['fname'],
                $userData['lname'],
                $userData['email'],
                $booker
            );
        } else {
            $u->updateUser(
                $userId,
                $userData['fname'],
                $userData['lname'],
                $userData['email'],
                $booker
            );
        }
        return $response->withHeader('Content-Type', 'application/json');
    }
}
