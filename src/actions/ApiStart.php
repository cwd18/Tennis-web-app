<?php
# External token-based entry point for React to start a session

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class ApiStart
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $token = $args['token'];
        $m = $this->container->get('Model');
        $t = $m->getTokens();
        $tokenData = $t->checkToken($token);
        $view = Twig::fromRequest($request);
        if ($tokenData == FALSE) {
            return $view->render($response, 'error.html', ['error' => "Token not found: $token"]);}

        $_SESSION['User'] = $tokenData['Userid'];
        $_SESSION['Role'] = $tokenData['TokenClass'];
        $_SESSION['Otherid'] = $tokenData['Otherid'];

        $u = $m->getUsers();
        $userData = $u->getUserData($tokenData['Userid']);
        $tokenData['FirstName'] = $userData['FirstName'];
        $tokenData['LastName'] = $userData['LastName'];
        $response->getBody()->write(json_encode($tokenData));        
        return $response->withHeader('Content-Type', 'application/json');
    }
}