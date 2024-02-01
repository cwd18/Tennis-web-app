<?php
# Delete the specified series

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class SeriesDelete
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $seriesId = $params['seriesid'];
        $pdo = $GLOBALS['pdo'];
        $s = new \TennisApp\Series($pdo);
        $s->deleteSeries($seriesId);
        return $response
          ->withHeader('Location', "/serieslist")
          ->withStatus(302);
    }
}
