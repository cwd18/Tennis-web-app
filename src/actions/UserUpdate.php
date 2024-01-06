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
        $users = new \TennisApp\Users($GLOBALS['pdo']);
        $fname = $params['fname'];
        $lname = $params['lname'];
        $email = $params['email'];
        $row = $users->updateUser($params['Userid'], $fname, $lname, $email);
        if ($row['FirstName'] != $fname) {
            $row['FirstName'] .= " => $fname";
        }
        if ($row['LastName'] != $lname) {
            $row['LastName'] .= " => $lname";
        }
        if ($row['EmailAddress'] != $email) {
            $row['EmailAddress'] .= " => $email";
        }
        $view = Twig::fromRequest($request);
        return $view->render($response, 'useropcontinue.html', ['op' => 'User updated', 'user' => $row]);
      }
}
