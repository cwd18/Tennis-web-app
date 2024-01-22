<?php
# Present form to add series 

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class SeriesAddForm
{
    public function __invoke(Request $request, Response $response): Response
    {
      $pdo = $GLOBALS['pdo'];
      $u = new \TennisApp\Users($pdo);
      $users = $u->getAllUsers();
      $view = Twig::fromRequest($request);
      return $view->render($response, 'seriesaddform.html', ['users' => $users]);
    }
}
