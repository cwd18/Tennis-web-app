<?php
# Edit a user

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class UserEdit
{
  private $container;

  public function __construct(ContainerInterface $container)
  {
      $this->container = $container;
  }

  public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $m = $this->container->get('Model');
        $view = Twig::fromRequest($request);
        if (is_string($error = $m->checkAdmin())) {
            return $view->render($response, 'error.html', ['error' => $error]);}
        $u = $m->getUsers();
        $row = $u->getUserData($params['Userid']);
        return $view->render($response, 'useredit.html', $row);
      }
}
