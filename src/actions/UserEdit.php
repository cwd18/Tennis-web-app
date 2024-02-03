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
        $model = $this->container->get('Model');
        $u = $model->getUsers();
        $row = $u->getUser($params['Userid']);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'useredit.html', $row);
      }
}
