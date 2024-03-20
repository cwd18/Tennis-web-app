<?php
# Present form to add a new series 

namespace TennisApp\Action;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class SeriesAddForm
{
  private $container;

  public function __construct(ContainerInterface $container)
  {
      $this->container = $container;
  }

public function __invoke(Request $request, Response $response): Response
    {
      $m = $this->container->get('Model');
      $view = Twig::fromRequest($request);
      if (is_string($error = $m->checkAdmin())) {
        return $view->render($response, 'error.html', ['error' => $error]);}
      $u = $m->getUsers();
      $users = $u->getAllUsers();
      return $view->render($response, 'seriesaddform.html', ['users' => $users]);
    }
}
