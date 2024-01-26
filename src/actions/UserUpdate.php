<?php
# Update a user from edit user form parameters

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class UserUpdate
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getParsedBody();
        $userId = $params['Userid'];
        $fname = $params['fname'];
        $lname = $params['lname'];
        $email = $params['email'];
        $u = new \TennisApp\Users($GLOBALS['pdo']);
        $row = $u->updateUser($userId, $fname, $lname, $email);
        if ($row['FirstName'] != $fname) {
            $lines[] = $row['FirstName'] . " => $fname";
        }
        if ($row['LastName'] != $lname) {
            $lines[] = $row['LastName'] . " => $lname";
        }
        if ($row['EmailAddress'] != $email) {
            $lines[] = $row['EmailAddress'] . " => $email";
        }
        $view = Twig::fromRequest($request);
        return $view->render($response, 'opcontinue.html', ['op' => "User $userId updated", 
        'link' => "userlist", 'lines' => $lines]);

      }
}
