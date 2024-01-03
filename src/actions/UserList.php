<?php
# List all users

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class UserList
{
    public function __invoke(Request $request, Response $response): Response
    {
        $users = new \TennisApp\Users($GLOBALS['pdo']);
        $allUsers = $users->getAllUsers();
        $view = Twig::fromRequest($request);
        return $view->render($response, 'userlist.html', ['userlist' => $allUsers]);
    }
}
