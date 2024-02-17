<?php
# Add a user from form parameters

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class UserAddForm
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $m = $this->container->get('Model');
        if (is_string($error = $m->checkAdmin())) {
            $response->getBody()->write($error);
            return $response;
        }
        $view = Twig::fromRequest($request);
        return $view->render($response, 'useraddform.html', ['input' => 'none']);
    }
}
