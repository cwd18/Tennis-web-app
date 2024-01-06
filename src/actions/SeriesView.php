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
        $series = new \TennisApp\Series($GLOBALS['pdo']);
        $view = Twig::fromRequest($request);
        $s = $series->getSeries($seriesId);
        return $view->render($response, 'series.html', [
            'seriesid' => $seriesId,
            'description' => $s['description'],
            'owner' => $s['owner'],
            'participants' => $s['participants'],
            'fixtures' => $s['fixtures']
            ]);
    }
}
