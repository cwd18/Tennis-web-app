<?php
# List all series

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class SeriesList
{
    public function __invoke(Request $request, Response $response): Response
    {
        $series = new \TennisApp\Series($GLOBALS['pdo']);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'serieslist.html', ['serieslist' => $series->getAllSeries()]);
    }
}
