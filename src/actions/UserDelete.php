<?php
# Delete the specified user and then list all users

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class UserDelete
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $userId = $params['Userid'];
        $model = $this->container->get('Model');
        $u = $model->getUsers();
        $u->deleteUser($userId);
        return $response
          ->withHeader('Location', "/userlist")
          ->withStatus(302);
    }
}
