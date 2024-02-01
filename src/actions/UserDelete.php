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
        $userId = $params['Userid'];
        $u = new \TennisApp\Users($GLOBALS['pdo']);
        $u->deleteUser($userId);
        return $response
          ->withHeader('Location', "/userlist")
          ->withStatus(302);
    }
}
