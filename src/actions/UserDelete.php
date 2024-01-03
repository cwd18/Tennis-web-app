<?php
# Delete the specified user and then list all users

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class UserDelete
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $users = new \TennisApp\Users($GLOBALS['pdo']);
        $row = $users->deleteUser($params['Userid']);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'useropcontinue.html', ['op' => 'User deleted', 'user' => $row]);
    }
}
