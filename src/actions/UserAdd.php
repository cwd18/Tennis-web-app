<?php
# Add a user from form parameters

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class UserAdd
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getParsedBody();
        $users = new \TennisApp\Users($GLOBALS['pdo']);
        $row = $users->addUser($params['fname'], $params['lname'], $params['email']);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'useropcontinue.html', ['op' => 'User added', 'user' => $row]);
      }
}
