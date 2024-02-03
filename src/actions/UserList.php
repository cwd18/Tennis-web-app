<?php
# List all users

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class UserList
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $model = $this->container->get('Model');
        $u = $model->getUsers();
        $allUsers = $u->getAllUsers();
        $view = Twig::fromRequest($request);
        return $view->render($response, 'userlist.html', ['userlist' => $allUsers]);
    }
}