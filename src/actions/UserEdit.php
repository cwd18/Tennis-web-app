<?php
# Edit a user

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class UserEdit
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $users = new \TennisApp\Users($GLOBALS['pdo']);
        $row = $users->getUser($params['Userid']);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'useredit.html', $row);
      }
}
