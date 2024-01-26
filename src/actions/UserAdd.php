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
        $u = new \TennisApp\Users($GLOBALS['pdo']);
        $userId = $u->addUser($params['fname'], $params['lname'], $params['email']);
        $row = $u->getUser($userId);
        $lines[] = $row['FirstName'] . ' ' . $row['LastName'];
        $lines[] = $row['EmailAddress'];
        $view = Twig::fromRequest($request);
        return $view->render($response, 'opcontinue.html', ['op' => "User $userId added", 
        'link' => "userlist", 'lines' => $lines]);
    }

}