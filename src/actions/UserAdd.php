<?php
# Add a user from form parameters

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class UserAdd
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getParsedBody();
        $model = $this->container->get('Model');
        $u = $model->getUsers();
        $u->addUser($params['fname'], $params['lname'], $params['email']);
        return $response
          ->withHeader('Location', "/userlist")
          ->withStatus(302);
    }

}