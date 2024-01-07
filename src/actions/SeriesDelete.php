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
        $fixtures = new \TennisApp\Series($pdo);
        $s = $fixtures->deleteSeries($seriesId);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'seriesdeleted.html', $s);   
    }
}
