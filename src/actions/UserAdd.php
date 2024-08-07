<?php
# Add a user from form parameters

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class UserAdd
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getParsedBody();
        $booker = array_key_exists('booker', $params); // checkbox
        $m = $this->container->get('Model');
        $view = Twig::fromRequest($request);
        if (is_string($error = $m->checkAdmin())) {
            return $view->render($response, 'error.html', ['error' => $error]);
        }
        $u = $m->getUsers();
        $u->addUser($params['fname'], $params['lname'], $params['email'], $booker);
        return $response
            ->withHeader('Location', "/userlist")
            ->withStatus(302);
    }
}
