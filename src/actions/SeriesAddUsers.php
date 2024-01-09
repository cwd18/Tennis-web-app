<?php
# Add users from form parameters and then display the series

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class SeriesAddUsers
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getParsedBody();
        $seriesId = $params['seriesid'];
        foreach ($params as $pk => $p) {
            if (substr($pk, 0, 5) == "user_") {
                $userIds[] = $p;
            }
        }
        $pdo = $GLOBALS['pdo'];
        $series = new \TennisApp\Series($pdo);
        $series->addUsers($seriesId, $userIds);
        $s = $series->getSeries($seriesId);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'series.html', $s);
      }
}
