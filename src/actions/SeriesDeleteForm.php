<?php
# Present are you sure before deleting series
namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class SeriesDeleteForm
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $seriesId = $params['seriesid'];
        $pdo = $GLOBALS['pdo'];
        $s = new \TennisApp\Series($pdo);
        $series = $s->getBasicSeriesData($seriesId);
        $lines[] = "Are you sure you want to delete the below series?";
        $lines[] = $s->seriesDescription($series['SeriesWeekday'], $series['SeriesTime']);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'opconfirm.html', ['op' => "Delete series", 
        'continuelink' => "seriesdelete?seriesid=$seriesId", 
        'cancellink' => "series?seriesid=$seriesId", 
        'lines' => $lines]);
    }
}