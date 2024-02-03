<?php
# Update a user from edit user form parameters

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class UserUpdate
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getParsedBody();
        $userId = $params['Userid'];
        $fname = $params['fname'];
        $lname = $params['lname'];
        $email = $params['email'];
        $model = $this->container->get('Model');
        $u = $model->getUsers();
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
