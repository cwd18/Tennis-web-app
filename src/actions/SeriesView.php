<?php
# View specified series

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class SeriesView
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $seriesId = $params['seriesid'];
        $s = new \TennisApp\Series($GLOBALS['pdo']);
        $series = $s->getSeries($seriesId);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'series.html', $series);
    }
}
