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
        $row = $t->checkToken($token);
        $view = Twig::fromRequest($request);
        if ($row == FALSE) {
            return $view->render($response, 'error.html', ['error' => "Token not found: $token"]);}

        $_SESSION['User'] = $row['Userid'];
        $_SESSION['Role'] = $row['TokenClass'];
        $_SESSION['Otherid'] = $row['Otherid'];

        $response->getBody()->write("Session started");
        return $response;
    }
}