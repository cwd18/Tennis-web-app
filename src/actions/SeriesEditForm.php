<?php
# Present form for editing basic series data

namespace TennisApp\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Views\Twig;

final class SeriesEditForm
{
    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $seriesId = $params['seriesid'];
        $pdo = $GLOBALS['pdo'];
        $u = new \TennisApp\Users($pdo);
        $users = $u->getAllUsers();
        $series = new \TennisApp\Series($pdo);
        $s = $series->getBasicSeriesData($seriesId);
        $view = Twig::fromRequest($request);
        return $view->render($response, 'serieseditform.html', 
        ['seriesid' => $seriesId, 'owner' => $s['SeriesOwner'],
        'day' => $s['SeriesWeekday'], 'time' => substr($s['SeriesTime'],0,5),
        'users' => $users
        ]);   
    }
}
